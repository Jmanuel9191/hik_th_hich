<?php

namespace app\components;

use app\models\Ecosistema;
use app\models\Moneda;
use app\models\Proyecto;
use app\models\ProyectoDescarga;
use app\models\ProyectoDocumento;
use app\models\ProyectoHistorial;
use app\models\ProyectoImagen;
use app\models\ProyectoSearch;
use app\models\ProyectoVideo;
use app\models\Usuarios;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * Banco de proyectos (admin). Reglas:
 *  - Gerente de área: ve, crea y edita solo en sus ecosistemas (cualquier gerente del área edita los proyectos del área).
 *  - Distribuidor: solo mira. Superadmin: todo.
 *  - Eliminar: el gerente solo en borrador; publicado o con movimiento se archiva. El Superadmin puede siempre.
 *  - Archivos en privado/proyectos/<id>/ (imagenes, galeria, videos, documentos).
 */
class ProyectoService implements InterfaceProyecto
{
    private $permisos;

    public function __construct(InterfacePermisosService $permisos)
    {
        $this->permisos = $permisos;
    }

    /* ---------------- Alcance por área ---------------- */

    private function rol()
    {
        return (int) (Yii::$app->user->identity->idrol ?? 0);
    }

    /** null = todos; si es gerente, los ids de sus ecosistemas. */
    private function alcance()
    {
        if ($this->rol() !== Rol::GERENTE) {
            return null;
        }
        return array_map('intval', (new \yii\db\Query())->select('idecosistema')->from('usuario_ecosistema')
            ->where(['idusuario' => Yii::$app->user->id])->column());
    }

    private function enAlcance($idecosistema)
    {
        $ids = $this->alcance();
        return $ids === null || in_array((int) $idecosistema, $ids, true);
    }

    /* ---------------- Listado y búsqueda ---------------- */

    public function listar($params)
    {
        $this->permisos->verificarPermiso('proyectos_listar');
        $search = new ProyectoSearch();
        return [
            'searchModel' => $search,
            'dataProvider' => $search->search($params, $this->alcance()),
            'ecosistemas' => $this->ecosistemasPermitidos(),
        ];
    }

    public function buscar($id, $permiso = 'proyectos_ver'): Proyecto
    {
        $this->permisos->verificarPermiso($permiso);
        $p = Proyecto::find()->with(['ecosistema', 'moneda', 'gerente'])->where(['idproyecto' => (int) $id])->one();
        if ($p === null) {
            throw new NotFoundHttpException('El proyecto no existe.');
        }
        if (!$this->enAlcance($p->idecosistema)) {
            throw new ForbiddenHttpException('Este proyecto es de un área que no tenés asignada.');
        }
        return $p;
    }

    public function nuevo(): Proyecto
    {
        $p = new Proyecto(['tipo' => 'financiamiento', 'estado' => 'borrador']);
        $p->idmoneda = Moneda::find()->select('idmoneda')->where(['estado' => '1'])->orderBy(['orden' => SORT_ASC])->scalar() ?: null;
        $permitidos = $this->ecosistemasPermitidos();
        if (count($permitidos) === 1) {
            $p->idecosistema = array_key_first($permitidos);
        }
        if ($this->rol() === Rol::GERENTE) {
            $p->idgerente = Yii::$app->user->id;
        }
        return $p;
    }

    public function ecosistemasPermitidos(): array
    {
        $lista = Ecosistema::lista(false);
        $ids = $this->alcance();
        return $ids === null ? $lista : array_intersect_key($lista, array_flip($ids));
    }

    public function gerentes(): array
    {
        $areas = [];
        foreach ((new \yii\db\Query())->select(['ue.idusuario', 'e.nombre'])->from('usuario_ecosistema ue')
            ->innerJoin('ecosistema e', 'e.idecosistema = ue.idecosistema')->orderBy('e.numero')->all() as $f) {
            $areas[$f['idusuario']][] = $f['nombre'];
        }
        $lista = [];
        foreach (Usuarios::find()->where(['idrol' => Rol::GERENTE, 'estado' => '1'])->orderBy('nombrecompleto')->all() as $u) {
            $lista[$u->idusuario] = trim($u->nombrecompleto ?: $u->usuario) . (isset($areas[$u->idusuario]) ? ' (' . implode(', ', $areas[$u->idusuario]) . ')' : '');
        }
        return $lista;
    }

    private function gerenteDelArea($idusuario, $idecosistema)
    {
        return (new \yii\db\Query())->from('usuario_ecosistema ue')->innerJoin('usuarios u', 'u.idusuario = ue.idusuario')
            ->where(['ue.idusuario' => (int) $idusuario, 'ue.idecosistema' => (int) $idecosistema, 'u.idrol' => Rol::GERENTE, 'u.estado' => '1'])->exists();
    }

    public function puedeEditar(Proyecto $p): bool
    {
        return $this->permisos->tienePermiso('proyectos_editar') && $this->enAlcance($p->idecosistema);
    }

    public function puedeEliminar(Proyecto $p): bool
    {
        if (!$this->permisos->tienePermiso('proyectos_eliminar') || !$this->enAlcance($p->idecosistema)) {
            return false;
        }
        return $this->rol() === Rol::SUPERADMIN || $p->estado === 'borrador';
    }

    /* ---------------- Estados ---------------- */

    /** desde => [hacia => permiso] */
    const FLUJO = [
        'borrador' => ['revision' => 'proyectos_editar', 'publicado' => 'proyectos_publicar'],
        'revision' => ['publicado' => 'proyectos_publicar', 'borrador' => 'proyectos_editar'],
        'publicado' => ['cerrado' => 'proyectos_publicar', 'borrador' => 'proyectos_publicar', 'archivado' => 'proyectos_publicar'],
        'cerrado' => ['publicado' => 'proyectos_publicar', 'archivado' => 'proyectos_publicar'],
        'archivado' => ['borrador' => 'proyectos_publicar'],
    ];
    /** hacia => [texto del botón, ícono, clase, aviso] */
    const BOTONES = [
        'revision' => ['Enviar a revisión', 'paper-plane', 'btn-warning', 'Queda listo para que lo revisen y lo publiquen. Todavía no lo ven los inversores.'],
        'publicado' => ['Publicar', 'globe', 'btn-success', 'Lo empiezan a ver los inversores aprobados de esta área.'],
        'cerrado' => ['Cerrar inversiones', 'lock', 'btn-info', 'Queda como "Inversión completa": los inversores lo siguen viendo con sus avances, pero ya no recibe más inversiones.'],
        'archivado' => ['Archivar', 'archive', 'btn-default', 'Deja de mostrarse a los inversores. No se borra nada.'],
        'borrador' => ['Volver a borrador', 'undo', 'btn-default', 'Deja de mostrarse a los inversores y se puede seguir editando.'],
    ];

    public function transiciones(Proyecto $p): array
    {
        if (!$this->enAlcance($p->idecosistema)) {
            return [];
        }
        $t = [];
        foreach (self::FLUJO[$p->estado] ?? [] as $hacia => $permiso) {
            if ($this->permisos->tienePermiso($permiso)) {
                $t[$hacia] = self::BOTONES[$hacia];
            }
        }
        return $t;
    }

    /** Lo que falta para poder publicar. */
    private function faltantes(Proyecto $p)
    {
        $f = [];
        if (trim(strip_tags((string) $p->descripcion)) === '') {
            $f[] = 'la descripción';
        }
        if (!$p->foto_principal) {
            $f[] = 'la foto principal';
        }
        if (!$p->idmoneda) {
            $f[] = 'la moneda';
        }
        if ($p->monto_objetivo === null) {
            $f[] = mb_strtolower($p->getAttributeLabel('monto_objetivo'));
        }
        if ($p->tipo === 'financiamiento' && $p->inversion_minima === null) {
            $f[] = 'la inversión mínima';
        }
        return $f;
    }

    public function cambiarEstado($id, $estado)
    {
        $p = $this->buscar($id);
        $permiso = self::FLUJO[$p->estado][$estado] ?? null;
        if ($permiso === null) {
            return ['exito' => false, 'mensaje' => 'Ese cambio de estado no es posible.'];
        }
        $this->permisos->verificarPermiso($permiso);
        if ($estado === 'publicado' && ($f = $this->faltantes($p))) {
            return ['exito' => false, 'mensaje' => 'Para publicar falta completar: ' . implode(', ', $f) . '.'];
        }
        $antes = $p->estadoNombre();
        $cambios = ['estado' => $estado, 'fecha_actualizacion' => date('Y-m-d H:i:s')];
        if ($estado === 'publicado' && !$p->fecha_publicacion) {
            $cambios['fecha_publicacion'] = date('Y-m-d H:i:s');
        }
        $p->updateAttributes($cambios);
        ProyectoHistorial::registrar($p->idproyecto, 'Estado', 'Estado: ' . $antes . ' → ' . $p->estadoNombre());
        return ['exito' => true, 'mensaje' => 'Ahora está: ' . $p->estadoNombre() . '.'];
    }

    /* ---------------- Crear / editar ---------------- */

    public function guardar(Proyecto $p, array $post)
    {
        $nuevo = $p->isNewRecord;
        $antes = $p->getAttributes();
        $antesEco = $p->ecosistema->nombre ?? '';
        $datos = $post['Proyecto'] ?? [];

        $p->setAttributes(array_intersect_key($datos, array_flip(array_diff(Proyecto::CAMPOS, ['descripcion', 'riesgos', 'etapas_interes']))), false);
        foreach (['descripcion', 'riesgos'] as $campo) {
            if (array_key_exists($campo, $datos)) {
                $p->$campo = $this->html($datos[$campo]);
            }
        }
        $p->etapasInteres = array_values(array_filter((array) ($datos['etapasInteres'] ?? []), 'strlen'));
        $p->etapas_interes = $p->etapasInteres ? implode(',', array_intersect(array_keys(Proyecto::ETAPAS), $p->etapasInteres)) : null;

        $subidas = [];
        foreach (array_keys(Proyecto::IMAGENES) as $campo) {
            $f = UploadedFile::getInstance($p, $campo);
            if ($f !== null && $f->error !== UPLOAD_ERR_NO_FILE) {
                $subidas[$campo] = $f;
            }
        }

        if (!$p->validate()) {
            return ['exito' => false, 'mensaje' => 'Revisá los campos marcados.'];
        }
        foreach (Proyecto::SOLO_TIPO as $tipo => $campos) {
            if ($tipo !== $p->tipo) {
                foreach ($campos as $campo) {
                    $p->$campo = null; // no aplica a este tipo
                }
            }
        }
        if ($p->tipo !== 'inversion') {
            $p->etapasInteres = [];
        }
        if (!$this->enAlcance($p->idecosistema)) {
            $p->addError('idecosistema', 'Solo podés usar ecosistemas que tenés asignados.');
            return ['exito' => false, 'mensaje' => 'Revisá los campos marcados.'];
        }
        if ($p->idgerente && !$this->gerenteDelArea($p->idgerente, $p->idecosistema)) {
            $p->addError('idgerente', 'Ese gerente no tiene asignado este ecosistema (o está bloqueado).');
            return ['exito' => false, 'mensaje' => 'Revisá los campos marcados.'];
        }

        $tx = Yii::$app->db->beginTransaction();
        if ($nuevo) {
            $p->estado = 'borrador';
            $p->codigo = $this->codigoNuevo($p);
            $p->slug = $this->slugUnico($p->titulo);
            $p->idusuario_registro = Yii::$app->user->id;
            $p->fecha_registro = date('Y-m-d H:i:s');
            $p->fecha_actualizacion = $p->fecha_registro;
            $p->save(false); // primero, para tener el id (carpeta)
        } else {
            $p->fecha_actualizacion = date('Y-m-d H:i:s');
        }

        $borrar = [];
        $cambiosImg = [];
        $carpeta = Proyecto::carpetaDe($p->idproyecto, 'imagenes');
        foreach ($subidas as $campo => $f) {
            $r = SubidaImagen::guardar($f, $carpeta, Proyecto::MAX_IMAGEN_MB);
            if (!$r['ok']) {
                $tx->rollBack();
                if ($nuevo) {
                    $p->setIsNewRecord(true);
                    $p->idproyecto = null;
                }
                $p->addError($campo, $r['error']);
                return ['exito' => false, 'mensaje' => 'Revisá los campos marcados.'];
            }
            if ($p->$campo) {
                $borrar[] = $p->$campo;
            }
            $cambiosImg[] = Proyecto::IMAGENES[$campo] . ': ' . ($p->$campo ? 'reemplazada' : 'subida');
            $p->$campo = $r['archivo'];
        }
        foreach (array_keys(Proyecto::IMAGENES) as $campo) {
            if (!isset($subidas[$campo]) && !empty($datos['quitar'][$campo]) && $p->$campo) {
                $borrar[] = $p->$campo;
                $cambiosImg[] = Proyecto::IMAGENES[$campo] . ': quitada';
                $p->$campo = null;
            }
        }
        $p->porcentaje_avance = $this->porcentajeSegunFicha($p);
        $p->save(false);
        $tx->commit();
        foreach ($borrar as $archivo) {
            SubidaImagen::borrar($carpeta, $archivo);
        }
        $p->populateRelation('ecosistema', Ecosistema::findOne($p->idecosistema));

        if ($nuevo) {
            ProyectoHistorial::registrar($p->idproyecto, 'Creación',
                'Proyecto creado: ' . $p->titulo . "\nTipo: " . Proyecto::TIPOS[$p->tipo] . "\nEcosistema: " . $p->ecosistema->nombre
                . ($cambiosImg ? "\n" . implode("\n", $cambiosImg) : ''));
        } else {
            $cambios = $this->diferencias($p, $antes, $antesEco);
            if ($cambios || $cambiosImg) {
                ProyectoHistorial::registrar($p->idproyecto, 'Edición', implode("\n", array_merge($cambios, $cambiosImg)));
            }
        }
        return ['exito' => true, 'mensaje' => $nuevo ? 'Proyecto creado (en borrador). Ahora podés sumar fotos, videos y documentos.' : 'Cambios guardados.'];
    }

    private function diferencias(Proyecto $p, array $antes, $antesEco)
    {
        $nombres = [
            'tipo' => Proyecto::TIPOS, 'etapa' => Proyecto::ETAPAS, 'frecuencia_retorno' => Proyecto::FRECUENCIAS,
            'tipo_participacion' => Proyecto::PARTICIPACIONES,
        ];
        $cambios = [];
        foreach (Proyecto::CAMPOS as $campo) {
            $a = (string) $antes[$campo];
            $d = (string) $p->$campo;
            if (in_array($campo, ['monto_total', 'monto_objetivo', 'inversion_minima', 'inversion_maxima', 'roi'], true)) {
                $a = $a === '' ? '' : rtrim(rtrim(number_format((float) $a, 2, '.', ''), '0'), '.');
                $d = $d === '' ? '' : rtrim(rtrim(number_format((float) $d, 2, '.', ''), '0'), '.');
            }
            if ($a === $d) {
                continue;
            }
            $etiqueta = $p->getAttributeLabel($campo);
            if (in_array($campo, ['descripcion', 'riesgos', 'garantias', 'impacto', 'resumen'], true)) {
                $cambios[] = $etiqueta . ': ' . ($d === '' ? 'quitado' : ($a === '' ? 'agregado' : 'modificado'));
                continue;
            }
            if ($campo === 'idecosistema') {
                [$a, $d] = [$antesEco, $p->ecosistema->nombre ?? $d];
            } elseif ($campo === 'idgerente') {
                $a = $a === '' ? '' : (Usuarios::findOne((int) $a)->nombrecompleto ?? '#' . $a);
                $d = $d === '' ? '' : (Usuarios::findOne((int) $d)->nombrecompleto ?? '#' . $d);
            } elseif ($campo === 'idmoneda') {
                $a = $a === '' ? '' : (Moneda::findOne((int) $a)->codigo ?? $a);
                $d = $d === '' ? '' : (Moneda::findOne((int) $d)->codigo ?? $d);
            } elseif ($campo === 'etapas_interes') {
                $n = fn($v) => implode(', ', array_intersect_key(Proyecto::ETAPAS, array_flip(explode(',', $v))));
                [$a, $d] = [$n($a), $n($d)];
            } elseif (isset($nombres[$campo])) {
                [$a, $d] = [$nombres[$campo][$a] ?? $a, $nombres[$campo][$d] ?? $d];
            }
            $cambios[] = $etiqueta . ': ' . ($a === '' ? '(vacío)' : $a) . ' → ' . ($d === '' ? '(vacío)' : $d);
        }
        return $cambios;
    }

    /**
     * % de avance cuando cambia la ficha: si algún avance visible informó un %, manda ese;
     * si no, el de la etapa. "Busca inversión" no tiene %.
     */
    private function porcentajeSegunFicha(Proyecto $p)
    {
        if ($p->tipo !== 'financiamiento') {
            return null;
        }
        if (!$p->isNewRecord) {
            $informado = \app\models\ProyectoAvance::find()->select('porcentaje')
                ->where(['idproyecto' => $p->idproyecto, 'estado' => '1'])->andWhere(['not', ['porcentaje' => null]])
                ->orderBy(['fecha' => SORT_DESC, 'idavance' => SORT_DESC])->scalar();
            if ($informado !== false && $informado !== null) {
                return (int) $informado;
            }
        }
        return Proyecto::PORCENTAJE_ETAPA[$p->etapa] ?? null;
    }

    /** HUK-ENE-2026-001: 3 letras del ecosistema, año y número correlativo del año. */
    private function codigoNuevo(Proyecto $p)
    {
        $eco = Ecosistema::findOne($p->idecosistema);
        $letras = strtoupper(substr(preg_replace('/[^a-z]/', '', $eco->slug ?? 'gen'), 0, 3)) ?: 'GEN';
        $anio = date('Y');
        $ultimos = Proyecto::find()->select('codigo')->where(['like', 'codigo', '-' . $anio . '-'])->column();
        $max = 0;
        foreach ($ultimos as $c) {
            $max = max($max, (int) substr($c, strrpos($c, '-') + 1));
        }
        return sprintf('HUK-%s-%s-%03d', $letras, $anio, $max + 1);
    }

    private function slugUnico($titulo)
    {
        $base = Inflector::slug($titulo) ?: 'proyecto';
        $slug = $base;
        for ($i = 2; Proyecto::find()->where(['slug' => $slug])->exists(); $i++) {
            $slug = $base . '-' . $i;
        }
        return $slug;
    }

    /** HTML del editor limpio; un editor "vacío" deja <p>&nbsp;</p> → null. */
    private function html($valor)
    {
        $html = HtmlEditor::limpiar($valor);
        $plano = preg_replace('/[\s\x{00A0}]+/u', '', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        return $plano === '' ? null : $html;
    }

    /* ---------------- Eliminar ---------------- */

    public function eliminar($id)
    {
        $p = $this->buscar($id, 'proyectos_eliminar');
        if (!$this->puedeEliminar($p)) {
            return ['exito' => false, 'mensaje' => 'Solo se pueden eliminar proyectos en borrador. Si ya se publicó, archivalo.'];
        }
        if (\app\models\InversionPago::find()->where(['idproyecto' => $p->idproyecto])->exists()) {
            return ['exito' => false, 'mensaje' => 'Tiene pagos registrados en Inversiones: no se puede eliminar. Archivalo.'];
        }
        $titulo = $p->codigo . ' · ' . $p->titulo;
        $p->delete(); // galería, videos y documentos se borran en cascada
        FileHelper::removeDirectory(Proyecto::carpetaDe($id));
        ProyectoHistorial::registrar((int) $id, 'Eliminación', 'Proyecto eliminado: ' . $titulo);
        return ['exito' => true, 'mensaje' => 'Proyecto eliminado.'];
    }

    /* ---------------- Galería ---------------- */

    private function editable($id)
    {
        $p = $this->buscar($id, 'proyectos_editar');
        if (!$this->puedeEditar($p)) {
            throw new ForbiddenHttpException('No podés editar este proyecto.');
        }
        return $p;
    }

    public function subirGaleria($id)
    {
        $p = $this->editable($id);
        $f = UploadedFile::getInstanceByName('imagenes') ?? (UploadedFile::getInstancesByName('imagenes')[0] ?? null);
        if ($f === null) {
            return ['error' => 'No llegó ninguna imagen.'];
        }
        $r = SubidaImagen::guardar($f, Proyecto::carpetaDe($p->idproyecto, 'galeria'), Proyecto::MAX_IMAGEN_MB);
        if (!$r['ok']) {
            return ['error' => $r['error']];
        }
        $img = new ProyectoImagen([
            'idproyecto' => $p->idproyecto,
            'archivo' => $r['archivo'],
            'nombre_original' => mb_substr($f->name, 0, 255),
            'orden' => (int) ProyectoImagen::find()->where(['idproyecto' => $p->idproyecto])->max('orden') + 1,
            'idusuario' => Yii::$app->user->id,
            'fecha_registro' => date('Y-m-d H:i:s'),
        ]);
        $img->save(false);
        $p->updateAttributes(['fecha_actualizacion' => date('Y-m-d H:i:s')]);
        ProyectoHistorial::registrar($p->idproyecto, 'Galería', 'Foto agregada: ' . $img->nombre_original);
        return [];
    }

    public function borrarImagenGaleria($idimagen)
    {
        $img = ProyectoImagen::findOne((int) $idimagen);
        if ($img === null) {
            return ['error' => 'La foto ya no existe.'];
        }
        $p = $this->editable($img->idproyecto);
        SubidaImagen::borrar(Proyecto::carpetaDe($p->idproyecto, 'galeria'), $img->archivo);
        $img->delete();
        ProyectoHistorial::registrar($p->idproyecto, 'Galería', 'Foto eliminada: ' . $img->nombre_original);
        return [];
    }

    /* ---------------- Videos ---------------- */

    public function agregarVideo($id, array $post)
    {
        $p = $this->editable($id);
        $v = new ProyectoVideo(['idproyecto' => $p->idproyecto]);
        $datos = $post['ProyectoVideo'] ?? [];
        $v->titulo = $datos['titulo'] ?? '';
        $v->url = $datos['url'] ?? '';
        $f = UploadedFile::getInstance($v, 'archivo');
        if ($f !== null && $f->error === UPLOAD_ERR_NO_FILE) {
            $f = null;
        }
        if (!$v->validate()) {
            return ['exito' => false, 'mensaje' => implode(' ', $v->getFirstErrors())];
        }
        if ($f === null && $v->url === '') {
            return ['exito' => false, 'mensaje' => 'Subí un archivo de video o pegá un enlace de YouTube o Vimeo.'];
        }
        if ($f !== null) {
            $n = ProyectoVideo::find()->where(['idproyecto' => $p->idproyecto, 'tipo' => 'archivo'])->count();
            if ($n >= Proyecto::MAX_VIDEOS_ARCHIVO) {
                return ['exito' => false, 'mensaje' => 'Este proyecto ya tiene ' . Proyecto::MAX_VIDEOS_ARCHIVO . ' videos subidos (el máximo). Usá un enlace de YouTube o Vimeo, o borrá uno.'];
            }
            $r = SubidaArchivo::guardar($f, Proyecto::carpetaDe($p->idproyecto, 'videos'), ProyectoVideo::FORMATOS, Proyecto::MAX_VIDEO_MB);
            if (!$r['ok']) {
                return ['exito' => false, 'mensaje' => $r['error']];
            }
            $v->setAttributes(['tipo' => 'archivo', 'url' => null, 'archivo' => $r['archivo'], 'mime' => $r['mime'], 'tamano' => $r['tamano'],
                'nombre_original' => mb_substr($f->name, 0, 255)], false);
        } else {
            if (ProyectoVideo::embed($v->url) === null) {
                return ['exito' => false, 'mensaje' => 'El enlace tiene que ser de YouTube o Vimeo. Ej.: https://vimeo.com/123456789'];
            }
            $v->tipo = 'enlace';
        }
        $v->orden = (int) ProyectoVideo::find()->where(['idproyecto' => $p->idproyecto])->max('orden') + 1;
        $v->idusuario = Yii::$app->user->id;
        $v->fecha_registro = date('Y-m-d H:i:s');
        $v->save(false);
        $p->updateAttributes(['fecha_actualizacion' => date('Y-m-d H:i:s')]);
        ProyectoHistorial::registrar($p->idproyecto, 'Videos', 'Video agregado: ' . $v->titulo . ($v->tipo === 'archivo'
            ? ' (archivo ' . $v->nombre_original . ', ' . SubidaArchivo::peso($v->tamano) . ')' : ' (enlace)'));
        return ['exito' => true, 'mensaje' => 'Video agregado.'];
    }

    private function videoEditable($idvideo)
    {
        $v = ProyectoVideo::findOne((int) $idvideo);
        if ($v === null) {
            throw new NotFoundHttpException('El video ya no existe.');
        }
        return [$v, $this->editable($v->idproyecto)];
    }

    public function moverVideo($idvideo, $direccion)
    {
        [$v, $p] = $this->videoEditable($idvideo);
        $subir = (int) $direccion < 0;
        $vecino = ProyectoVideo::find()->where(['idproyecto' => $p->idproyecto])
            ->andWhere([$subir ? '<' : '>', 'orden', $v->orden])->orderBy(['orden' => $subir ? SORT_DESC : SORT_ASC])->one();
        if ($vecino === null) {
            return ['exito' => false, 'mensaje' => $subir ? 'Ya es el primero.' : 'Ya es el último.'];
        }
        $tx = Yii::$app->db->beginTransaction();
        $orden = $v->orden;
        $v->updateAttributes(['orden' => $vecino->orden]);
        $vecino->updateAttributes(['orden' => $orden]);
        $tx->commit();
        ProyectoHistorial::registrar($p->idproyecto, 'Videos', 'Video "' . $v->titulo . '": ' . ($subir ? 'subió' : 'bajó') . ' un lugar.');
        return ['exito' => true, 'mensaje' => ''];
    }

    public function eliminarVideo($idvideo)
    {
        [$v, $p] = $this->videoEditable($idvideo);
        if ($v->archivo) {
            SubidaArchivo::borrar(Proyecto::carpetaDe($p->idproyecto, 'videos'), $v->archivo);
        }
        $v->delete();
        ProyectoHistorial::registrar($p->idproyecto, 'Videos', 'Video eliminado: ' . $v->titulo);
        return ['exito' => true, 'mensaje' => 'Video eliminado.'];
    }

    /* ---------------- Documentos ---------------- */

    public function agregarDocumento($id, array $post)
    {
        $p = $this->editable($id);
        $d = new ProyectoDocumento(['idproyecto' => $p->idproyecto]);
        $datos = $post['ProyectoDocumento'] ?? [];
        $d->nombre = $datos['nombre'] ?? '';
        $d->tipo = $datos['tipo'] ?? '';
        $d->visible_inversor = !empty($datos['visible_inversor']) ? '1' : '0';
        $f = UploadedFile::getInstance($d, 'archivo');
        if (!$d->validate()) {
            return ['exito' => false, 'mensaje' => implode(' ', $d->getFirstErrors())];
        }
        if ($f === null || $f->error === UPLOAD_ERR_NO_FILE) {
            return ['exito' => false, 'mensaje' => 'Elegí el archivo.'];
        }
        $r = SubidaArchivo::guardar($f, Proyecto::carpetaDe($p->idproyecto, 'documentos'), ProyectoDocumento::FORMATOS, Proyecto::MAX_DOCUMENTO_MB);
        if (!$r['ok']) {
            return ['exito' => false, 'mensaje' => $r['error']];
        }
        $d->setAttributes(['archivo' => $r['archivo'], 'mime' => $r['mime'], 'tamano' => $r['tamano'], 'nombre_original' => mb_substr($f->name, 0, 255),
            'idusuario' => Yii::$app->user->id, 'fecha_registro' => date('Y-m-d H:i:s')], false);
        $d->save(false);
        $p->updateAttributes(['fecha_actualizacion' => date('Y-m-d H:i:s')]);
        ProyectoHistorial::registrar($p->idproyecto, 'Documentos', 'Documento agregado: ' . $d->nombre . ' (' . $d->tipoNombre() . ', '
            . ($d->visible_inversor === '1' ? 'lo ven los inversores' : 'solo HUK') . ')');
        return ['exito' => true, 'mensaje' => 'Documento agregado.'];
    }

    private function documentoEditable($iddocumento)
    {
        $d = ProyectoDocumento::findOne((int) $iddocumento);
        if ($d === null) {
            throw new NotFoundHttpException('El documento ya no existe.');
        }
        return [$d, $this->editable($d->idproyecto)];
    }

    public function cambiarVisibilidadDocumento($iddocumento)
    {
        [$d, $p] = $this->documentoEditable($iddocumento);
        $visible = $d->visible_inversor !== '1';
        $d->updateAttributes(['visible_inversor' => $visible ? '1' : '0']);
        ProyectoHistorial::registrar($p->idproyecto, 'Documentos', 'Documento "' . $d->nombre . '": ' . ($visible ? 'ahora lo ven los inversores' : 'ahora solo lo ve HUK'));
        return ['exito' => true, 'mensaje' => $visible ? 'Ahora lo ven los inversores.' : 'Ahora solo lo ve HUK.'];
    }

    public function eliminarDocumento($iddocumento)
    {
        [$d, $p] = $this->documentoEditable($iddocumento);
        SubidaArchivo::borrar(Proyecto::carpetaDe($p->idproyecto, 'documentos'), $d->archivo);
        $d->delete();
        ProyectoHistorial::registrar($p->idproyecto, 'Documentos', 'Documento eliminado: ' . $d->nombre);
        return ['exito' => true, 'mensaje' => 'Documento eliminado.'];
    }

    public function descargasPorDocumento($id): array
    {
        return array_map('intval', ProyectoDescarga::find()->select(['COUNT(*)', 'iddocumento'])->where(['idproyecto' => (int) $id])
            ->andWhere(['not', ['iddocumento' => null]])
            ->groupBy('iddocumento')->indexBy('iddocumento')->column());
    }

    /* ---------------- Archivos protegidos ---------------- */

    private function existe($ruta)
    {
        if (!is_file($ruta)) {
            throw new NotFoundHttpException('El archivo ya no está en el servidor.');
        }
        return $ruta;
    }

    public function archivoImagen($id, $campo): array
    {
        $p = $this->buscar($id);
        if (!isset(Proyecto::IMAGENES[$campo]) || !$p->$campo) {
            throw new NotFoundHttpException('Sin imagen.');
        }
        return ['ruta' => $this->existe(Proyecto::carpetaDe($p->idproyecto, 'imagenes') . '/' . basename($p->$campo)), 'nombre' => $p->$campo, 'mime' => null];
    }

    public function archivoGaleria($idimagen): array
    {
        $img = ProyectoImagen::findOne((int) $idimagen) ?? throw new NotFoundHttpException('La foto ya no existe.');
        $p = $this->buscar($img->idproyecto);
        return ['ruta' => $this->existe(Proyecto::carpetaDe($p->idproyecto, 'galeria') . '/' . basename($img->archivo)), 'nombre' => $img->nombre_original, 'mime' => null];
    }

    public function archivoVideo($idvideo): array
    {
        $v = ProyectoVideo::findOne((int) $idvideo);
        if ($v === null || $v->tipo !== 'archivo') {
            throw new NotFoundHttpException('El video ya no existe.');
        }
        $p = $this->buscar($v->idproyecto);
        return ['ruta' => $this->existe(Proyecto::carpetaDe($p->idproyecto, 'videos') . '/' . basename($v->archivo)), 'nombre' => $v->nombre_original,
            'mime' => $v->mime === 'video/quicktime' ? 'video/mp4' : $v->mime]; // MOV con H.264 se reproduce como MP4
    }

    public function archivoDocumento($iddocumento): array
    {
        $d = ProyectoDocumento::findOne((int) $iddocumento) ?? throw new NotFoundHttpException('El documento ya no existe.');
        $p = $this->buscar($d->idproyecto);
        $ruta = $this->existe(Proyecto::carpetaDe($p->idproyecto, 'documentos') . '/' . basename($d->archivo));
        ProyectoDescarga::registrar($d);
        return ['ruta' => $ruta, 'nombre' => $d->nombre_original, 'mime' => $d->mime];
    }

    /* ---------------- Historial ---------------- */

    public function historial($id)
    {
        return new ActiveDataProvider([
            'query' => ProyectoHistorial::find()->with('usuarioActor')->where(['idproyecto' => (int) $id])->orderBy(['idhistorial' => SORT_DESC]),
            'pagination' => ['pageSize' => 15, 'pageParam' => 'hpage'],
        ]);
    }

    /* ---------------- Exportar ---------------- */

    public function exportar($params)
    {
        $this->permisos->verificarPermiso('proyectos_exportar');
        $search = new ProyectoSearch();
        $modelos = $search->search($params, $this->alcance())->query->all();
        $dinero = fn($v, Proyecto $m) => $v === null || $v === '' ? '' : number_format((float) $v, 2, '.', '');
        $fecha = fn($v) => $v ? date('d/m/Y', strtotime($v)) : '';
        $columnas = [
            'Código' => fn(Proyecto $m) => $m->codigo,
            'Proyecto' => fn($m) => $m->titulo,
            'Tipo' => fn($m) => Proyecto::TIPOS[$m->tipo] ?? $m->tipo,
            'Ecosistema' => fn($m) => $m->ecosistema->nombre ?? '',
            'Gerente' => fn($m) => $m->gerente->nombrecompleto ?? '',
            'Estado' => fn($m) => $m->estadoNombre(),
            'Etapa' => fn($m) => $m->etapaNombre(),
            '% de avance' => fn($m) => $m->porcentaje_avance === null ? '' : $m->porcentaje_avance . ' %',
            'País' => fn($m) => $m->pais,
            'Ciudad' => fn($m) => $m->ciudad,
            'Moneda' => fn($m) => $m->moneda->codigo ?? '',
            'Monto total' => fn($m) => $dinero($m->monto_total, $m),
            'Monto a levantar / capital' => fn($m) => $dinero($m->monto_objetivo, $m),
            'Juntado (pagos vigentes)' => fn($m) => number_format($m->recaudado(), 2, '.', ''),
            '% de la ronda' => fn($m) => ($p = $m->porcentajeRonda()) === null ? '' : $p . ' %',
            'Inversores' => fn($m) => (int) \app\models\InversionPago::find()->where(['idproyecto' => $m->idproyecto, 'estado' => 'vigente'])->select('idusuario')->distinct()->count(),
            'Inversión mínima' => fn($m) => $dinero($m->inversion_minima, $m),
            'Inversión máxima' => fn($m) => $dinero($m->inversion_maxima, $m),
            'ROI (%)' => fn($m) => $m->roi,
            'Plazo (meses)' => fn($m) => $m->plazo_meses,
            'Apertura' => fn($m) => $fecha($m->fecha_apertura),
            'Cierre' => fn($m) => $fecha($m->fecha_cierre),
            'Resumen' => fn($m) => $m->resumen,
            'Fecha de registro' => fn($m) => $fecha($m->fecha_registro),
            'Publicado el' => fn($m) => $fecha($m->fecha_publicacion),
        ];
        $filtros = [];
        if ((string) $search->titulo !== '') {
            $filtros[] = 'Búsqueda: ' . $search->titulo;
        }
        if ($search->idecosistema && ($e = Ecosistema::findOne((int) $search->idecosistema))) {
            $filtros[] = 'Ecosistema: ' . $e->nombre;
        }
        if ($search->tipo) {
            $filtros[] = 'Tipo: ' . Proyecto::TIPOS[$search->tipo];
        }
        if ($search->estado) {
            $filtros[] = 'Estado: ' . Proyecto::ESTADOS[$search->estado][0];
        }
        if ($search->etapa) {
            $filtros[] = 'Etapa: ' . (Proyecto::ETAPAS[$search->etapa] ?? $search->etapa);
        }
        if ($this->alcance() !== null) {
            $filtros[] = 'Solo tus áreas';
        }
        return ['titulo' => 'Proyectos', 'columnas' => $columnas, 'modelos' => $modelos, 'filtros' => $filtros];
    }

}
