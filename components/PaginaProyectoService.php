<?php

namespace app\components;

use app\models\BannerProyectoForm;
use app\models\bloques\Catalogo;
use app\models\Proyecto;
use app\models\ProyectoBloque;
use app\models\ProyectoDocumento;
use app\models\ProyectoImagen;
use app\models\ProyectoVideo;
use app\models\ProyectoHistorial;
use app\models\ProyectoPlantilla;
use app\models\ProyectoSeccion;
use Yii;
use yii\helpers\Inflector;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * Editor de la página web de los proyectos y de las plantillas (ver InterfacePaginaProyecto).
 * Fotos de los bloques: privado/proyectos/<id>/bloques y privado/plantillas/<id>/bloques.
 * Al copiar (duplicar, aplicar o guardar plantilla) las fotos se copian con nombre nuevo (CopiaPagina):
 * cada bloque es dueño de sus archivos y se pueden borrar sin miedo.
 */
class PaginaProyectoService implements InterfacePaginaProyecto
{
    private $permisos;
    private $proyectos;

    public function __construct(InterfacePermisosService $permisos, InterfaceProyecto $proyectos)
    {
        $this->permisos = $permisos;
        $this->proyectos = $proyectos;
    }

    /* ================= Contenedores y permisos ================= */

    public function proyecto($id, bool $soloVer = false): Proyecto
    {
        $editar = $this->permisos->tienePermiso('proyectos_pagina');
        if (!$editar && !($soloVer && $this->permisos->tienePermiso('proyectos_ver'))) {
            throw new ForbiddenHttpException('No tiene permiso para la página web de los proyectos.');
        }
        $p = Proyecto::find()->with('ecosistema')->where(['idproyecto' => (int) $id])->one();
        if ($p === null) {
            throw new NotFoundHttpException('El proyecto no existe.');
        }
        if (!array_key_exists($p->idecosistema, $this->proyectos->ecosistemasPermitidos())) {
            throw new ForbiddenHttpException('Este proyecto es de un área que no tiene asignada.');
        }
        return $p;
    }

    public function puedeEditar(Proyecto $p): bool
    {
        return $this->permisos->tienePermiso('proyectos_pagina')
            && array_key_exists($p->idecosistema, $this->proyectos->ecosistemasPermitidos());
    }

    public function plantilla($id): ProyectoPlantilla
    {
        $this->permisos->verificarPermiso('plantillas_editar');
        return ProyectoPlantilla::findOne((int) $id) ?? throw new NotFoundHttpException('La plantilla no existe.');
    }

    /** Proyecto o plantilla, ya con el permiso revisado. */
    private function contenedor(string $tipo, int $id)
    {
        if (!in_array($tipo, self::TIPOS, true)) {
            throw new NotFoundHttpException();
        }
        return $tipo === 'proyecto' ? $this->proyecto($id) : $this->plantilla($id);
    }

    private static function tipoDe($c): string
    {
        return $c instanceof Proyecto ? 'proyecto' : 'plantilla';
    }

    private static function idDe($c): int
    {
        return $c instanceof Proyecto ? (int) $c->idproyecto : (int) $c->idplantilla;
    }

    private static function carpeta($c): string
    {
        return $c instanceof Proyecto ? Proyecto::carpetaDe($c->idproyecto, 'bloques') : ProyectoPlantilla::carpetaDe($c->idplantilla, 'bloques');
    }

    /** Condición SQL de las secciones del contenedor. */
    private static function donde($c): array
    {
        return $c instanceof Proyecto ? ['idproyecto' => $c->idproyecto] : ['idplantilla' => $c->idplantilla];
    }

    /** Sección + su contenedor (con permiso de edición). */
    private function seccion($idseccion): array
    {
        $s = ProyectoSeccion::findOne((int) $idseccion) ?? throw new NotFoundHttpException('La sección ya no existe.');
        $c = $s->idproyecto ? $this->proyecto($s->idproyecto) : $this->plantilla($s->idplantilla);
        return [$s, $c];
    }

    /** Bloque + sección + contenedor (con permiso de edición). */
    private function bloque($idbloque): array
    {
        $b = ProyectoBloque::findOne((int) $idbloque) ?? throw new NotFoundHttpException('El bloque ya no existe.');
        [$s, $c] = $this->seccion($b->idseccion);
        return [$b, $s, $c];
    }

    /** Historial del proyecto y fecha de modificación (plantilla: solo la fecha). */
    private function registrar($c, string $texto): void
    {
        $c->updateAttributes(['fecha_actualizacion' => date('Y-m-d H:i:s')]);
        if ($c instanceof Proyecto) {
            ProyectoHistorial::registrar($c->idproyecto, 'Página web', $texto);
        }
    }

    /* ================= Editor ================= */

    public function editor(string $tipo, int $id, bool $soloVer = false): array
    {
        $c = $tipo === 'proyecto' ? $this->proyecto($id, $soloVer) : $this->contenedor($tipo, $id);
        $esProyecto = $c instanceof Proyecto;
        return [
            'tipo' => $tipo,
            'contenedor' => $c,
            'secciones' => ProyectoSeccion::find()->with('bloques')->where(self::donde($c))->orderBy(['orden' => SORT_ASC, 'idseccion' => SORT_ASC])->all(),
            'banner' => $esProyecto ? BannerProyectoForm::desdeProyecto($c) : null,
            'plantillas' => $esProyecto ? ProyectoPlantilla::lista() : [],
            'sugerida' => $esProyecto ? ProyectoPlantilla::sugerida($c->idecosistema) : null,
            'puedeEditar' => $esProyecto ? $this->puedeEditar($c) : true,
            'puedeGuardarPlantilla' => $this->permisos->tienePermiso('plantillas_editar'),
            'medios' => MediosProyecto::panel($tipo, $id),
        ];
    }

    /* ================= Banner ================= */

    public function guardarBanner($idproyecto, array $post): array
    {
        $p = $this->proyecto($idproyecto);
        $f = new BannerProyectoForm();
        $f->load($post);
        if (!$f->validate()) {
            return ['exito' => false, 'mensaje' => implode(' ', $f->getFirstErrors()), 'form' => $f];
        }
        $cambios = [];
        foreach (Proyecto::IMAGENES as $campo => $nombre) {
            $archivo = UploadedFile::getInstanceByName('imagen[' . $campo . ']');
            if ($archivo !== null && $archivo->error !== UPLOAD_ERR_NO_FILE) {
                $r = SubidaImagen::guardar($archivo, Proyecto::carpetaDe($p->idproyecto, 'imagenes'), Proyecto::MAX_IMAGEN_MB);
                if (!$r['ok']) {
                    return ['exito' => false, 'mensaje' => $r['error'], 'form' => $f];
                }
                $this->borrarFoto(Proyecto::carpetaDe($p->idproyecto, 'imagenes'), $p->$campo);
                $cambios[] = $nombre . ': ' . ($p->$campo ? 'reemplazada' : 'subida');
                $p->$campo = $r['archivo'];
            }
        }
        $antes = [$p->rubro, $p->bajada, $p->sello, $p->elementos, $p->cifras, $p->destacado, $p->mostrar_ficha];
        $p->rubro = $f->rubro ?: null;
        $p->bajada = $f->bajada ?: null;
        $p->sello = $f->sello ?: null;
        $p->elementos = $f->elementos ? json_encode($f->elementos, JSON_UNESCAPED_UNICODE) : null;
        $p->cifras = $f->cifras ? json_encode($f->cifras, JSON_UNESCAPED_UNICODE) : null;
        $p->destacado = $f->destacado ? '1' : '0';
        $p->mostrar_ficha = $f->mostrar_ficha ? '1' : '0';
        if ($antes !== [$p->rubro, $p->bajada, $p->sello, $p->elementos, $p->cifras, $p->destacado, $p->mostrar_ficha]) {
            $cambios[] = 'Banner: datos actualizados' . ($antes[5] !== $p->destacado ? ($p->destacado === '1' ? ' (ahora es destacado)' : ' (ya no es destacado)') : '');
        }
        if (!$cambios) {
            return ['exito' => true, 'mensaje' => 'No había cambios.'];
        }
        $p->save(false, ['rubro', 'bajada', 'sello', 'elementos', 'cifras', 'destacado', 'mostrar_ficha', 'imagen_banner', 'foto_principal']);
        $this->registrar($p, implode("\n", $cambios));
        return ['exito' => true, 'mensaje' => 'Banner guardado.'];
    }

    /* ================= Secciones ================= */

    public function formularioSeccion(string $tipo, int $id, $idseccion = null): array
    {
        if ($idseccion) {
            [$s] = $this->seccion($idseccion);
        } else {
            $this->contenedor($tipo, $id);
            $s = new ProyectoSeccion(['fondo' => 'blanco', 'diseno' => 'arriba', 'en_menu' => '1', 'numerada' => '1', 'visible' => '1']);
        }
        return ['seccion' => $s, 'tipo' => $tipo, 'id' => $id];
    }

    public function guardarSeccion(string $tipo, int $id, $idseccion, array $post): array
    {
        if ($idseccion) {
            [$s, $c] = $this->seccion($idseccion);
        } else {
            $c = $this->contenedor($tipo, $id);
            $s = new ProyectoSeccion(self::donde($c));
        }
        $nueva = $s->isNewRecord;
        $datos = $post['ProyectoSeccion'] ?? [];
        $s->setAttributes(array_intersect_key($datos, array_flip(['antetitulo', 'titulo', 'menu', 'fondo', 'diseno'])), false);
        foreach (['en_menu', 'numerada', 'visible'] as $casilla) {
            $s->$casilla = !empty($datos[$casilla]) ? '1' : '0';
        }
        if (!$s->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.', 'errores' => $s->getFirstErrors()];
        }
        $ahora = date('Y-m-d H:i:s');
        if ($nueva) {
            $s->orden = (int) ProyectoSeccion::find()->where(self::donde($c))->max('orden') + 1;
            $s->fecha_registro = $ahora;
        }
        $s->ancla = $this->anclaUnica($c, $s->menu ?? $s->titulo ?? 'seccion', $s->idseccion);
        $s->fecha_actualizacion = $ahora;
        $s->save(false);
        $this->registrar($c, ($nueva ? 'Sección agregada: ' : 'Sección editada: ') . $s->nombrePanel());
        return ['exito' => true, 'mensaje' => $nueva ? 'Sección agregada. Ahora súmele bloques.' : 'Sección guardada.', 'id' => $s->idseccion];
    }

    /** "sintesis", "sintesis-2"… único dentro del proyecto o plantilla. */
    private function anclaUnica($c, string $base, $excepto = null): string
    {
        $base = mb_substr(Inflector::slug($base) ?: 'seccion', 0, 50);
        $ancla = $base;
        for ($i = 2; ProyectoSeccion::find()->where(self::donde($c))->andWhere(['ancla' => $ancla])
            ->andFilterWhere(['<>', 'idseccion', $excepto])->exists(); $i++) {
            $ancla = $base . '-' . $i;
        }
        return $ancla;
    }

    public function moverSeccion($idseccion, $direccion): array
    {
        [$s, $c] = $this->seccion($idseccion);
        $r = $this->intercambiar(ProyectoSeccion::find()->where(self::donde($c)), $s, (int) $direccion < 0);
        if ($r['exito']) {
            $this->registrar($c, 'Sección "' . $s->nombrePanel() . '": ' . ((int) $direccion < 0 ? 'subió' : 'bajó') . ' un lugar.');
        }
        return $r;
    }

    public function duplicarSeccion($idseccion): array
    {
        [$s, $c] = $this->seccion($idseccion);
        $tx = Yii::$app->db->beginTransaction();
        $this->correrOrden(ProyectoSeccion::class, self::donde($c), $s->orden);
        $copia = CopiaPagina::seccion($s, self::donde($c), ['orden' => $s->orden + 1, 'ancla' => $this->anclaUnica($c, $s->ancla)],
            self::carpeta($c), self::carpeta($c));
        $tx->commit();
        $this->registrar($c, 'Sección duplicada: ' . $s->nombrePanel());
        return ['exito' => true, 'mensaje' => 'Sección duplicada (quedó debajo, con sus bloques).', 'id' => $copia->idseccion];
    }

    public function cambiarVisibilidadSeccion($idseccion): array
    {
        [$s, $c] = $this->seccion($idseccion);
        $visible = $s->visible !== '1';
        $s->updateAttributes(['visible' => $visible ? '1' : '0', 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        $this->registrar($c, 'Sección "' . $s->nombrePanel() . '": ' . ($visible ? 'visible' : 'oculta'));
        return ['exito' => true, 'mensaje' => $visible ? 'La sección se ve en la web.' : 'La sección quedó oculta (no se borra nada).'];
    }

    public function eliminarSeccion($idseccion): array
    {
        [$s, $c] = $this->seccion($idseccion);
        $fotos = [];
        foreach ($s->bloques as $b) {
            $fotos = array_merge($fotos, $b->contenido() ? $b->contenido()->imagenes() : []);
        }
        $s->delete(); // los bloques se borran en cascada
        foreach ($fotos as $f) {
            $this->borrarFoto(self::carpeta($c), $f);
        }
        $this->registrar($c, 'Sección eliminada: ' . $s->nombrePanel() . ' (' . count($s->bloques) . ' bloques)');
        return ['exito' => true, 'mensaje' => 'Sección eliminada.'];
    }

    /* ================= Bloques ================= */

    public function formularioBloque($idseccion, $tipo, $idbloque = null): array
    {
        if ($idbloque) {
            [$b, $s, $c] = $this->bloque($idbloque);
            $contenido = $b->contenido() ?? throw new NotFoundHttpException('Ese tipo de bloque ya no existe.');
        } else {
            [$s, $c] = $this->seccion($idseccion);
            if (!isset(Catalogo::TIPOS[$tipo])) {
                throw new NotFoundHttpException('Tipo de bloque desconocido.');
            }
            $b = new ProyectoBloque(['idseccion' => $s->idseccion, 'tipo' => $tipo, 'ancho' => 'completo', 'visible' => '1']);
            $contenido = Catalogo::crear($tipo);
        }
        $medios = MediosProyecto::panel(self::tipoDe($c), self::idDe($c));
        $fuentes = $contenido::camposElegir() ? $this->fuentes($c, $medios) : null;
        return ['bloque' => $b, 'contenido' => $contenido, 'seccion' => $s, 'medios' => $medios, 'fuentes' => $fuentes];
    }

    public function guardarBloque($idseccion, $tipo, $idbloque, array $post): array
    {
        if ($idbloque) {
            [$b, $s, $c] = $this->bloque($idbloque);
            $antes = $b->contenido() ? $b->contenido()->imagenes() : [];
        } else {
            [$s, $c] = $this->seccion($idseccion);
            if (!isset(Catalogo::TIPOS[$tipo])) {
                return ['exito' => false, 'mensaje' => 'Tipo de bloque desconocido.'];
            }
            $b = new ProyectoBloque(['idseccion' => $s->idseccion, 'tipo' => $tipo, 'visible' => '1']);
            $antes = [];
        }
        $contenido = Catalogo::crear($b->tipo, (array) ($post['Bloque'] ?? []));
        if ($contenido::camposElegir()) {
            // solo ids de este proyecto (y en una plantilla, ninguno)
            if ($c instanceof Proyecto) {
                $contenido->filtrarElegidos(array_map(fn($lista) => array_column($lista, 'id'), $this->fuentes($c)));
            } else {
                $contenido->sinElegir();
            }
        }
        $b->ancho = array_key_exists($post['ancho'] ?? '', ProyectoBloque::ANCHOS) ? $post['ancho'] : 'completo';

        // Fotos nuevas: archivos[<ruta>] (ej. archivos[imagen], archivos[fotos.2.imagen])
        $carpeta = self::carpeta($c);
        $nuevas = [];
        foreach (array_keys((array) ($_FILES['archivos']['name'] ?? [])) as $ruta) {
            $f = UploadedFile::getInstanceByName('archivos[' . $ruta . ']');
            if ($f === null || $f->error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $r = SubidaImagen::guardar($f, $carpeta, Proyecto::MAX_IMAGEN_MB);
            if (!$r['ok']) {
                $this->borrarFotos($carpeta, $nuevas);
                return ['exito' => false, 'mensaje' => $r['error']];
            }
            if ($contenido->asignarImagen((string) $ruta, $r['archivo'])) {
                $nuevas[] = $r['archivo'];
            } else {
                $this->borrarFoto($carpeta, $r['archivo']);
            }
        }
        // Una foto que ya existía solo puede ser de este bloque (no se aceptan nombres de otros archivos).
        $ajenas = array_diff($contenido->imagenes(), $antes, $nuevas);
        if ($ajenas) {
            $this->borrarFotos($carpeta, $nuevas);
            return ['exito' => false, 'mensaje' => 'Una de las fotos no es de este bloque. Vuelva a subirla.'];
        }
        if (!$contenido->validate()) {
            $this->borrarFotos($carpeta, $nuevas);
            return ['exito' => false, 'mensaje' => implode(' ', array_unique($contenido->getFirstErrors())), 'errores' => $contenido->getFirstErrors()];
        }

        $nuevo = $b->isNewRecord;
        $ahora = date('Y-m-d H:i:s');
        $b->setContenido($contenido);
        if ($nuevo) {
            $b->orden = (int) ProyectoBloque::find()->where(['idseccion' => $s->idseccion])->max('orden') + 1;
            $b->fecha_registro = $ahora;
        }
        $b->fecha_actualizacion = $ahora;
        $b->save(false);
        $this->borrarFotos($carpeta, array_diff($antes, $contenido->imagenes())); // las que se quitaron o reemplazaron
        $this->registrar($c, ($nuevo ? 'Bloque agregado: ' : 'Bloque editado: ') . Catalogo::nombre($b->tipo) . ' (sección ' . $s->nombrePanel() . ')');
        return ['exito' => true, 'mensaje' => $nuevo ? 'Bloque agregado.' : 'Bloque guardado.', 'id' => $b->idbloque];
    }

    public function moverBloque($idbloque, $direccion): array
    {
        [$b, $s, $c] = $this->bloque($idbloque);
        $r = $this->intercambiar(ProyectoBloque::find()->where(['idseccion' => $s->idseccion]), $b, (int) $direccion < 0);
        if ($r['exito']) {
            $this->registrar($c, 'Bloque ' . Catalogo::nombre($b->tipo) . ' (sección ' . $s->nombrePanel() . '): ' . ((int) $direccion < 0 ? 'subió' : 'bajó'));
        }
        return $r;
    }

    public function duplicarBloque($idbloque): array
    {
        [$b, $s, $c] = $this->bloque($idbloque);
        $tx = Yii::$app->db->beginTransaction();
        $this->correrOrden(ProyectoBloque::class, ['idseccion' => $s->idseccion], $b->orden);
        $copia = CopiaPagina::bloque($b, $s->idseccion, $b->orden + 1, self::carpeta($c), self::carpeta($c));
        $tx->commit();
        $this->registrar($c, 'Bloque duplicado: ' . Catalogo::nombre($b->tipo) . ' (sección ' . $s->nombrePanel() . ')');
        return ['exito' => true, 'mensaje' => 'Bloque duplicado.', 'id' => $copia->idbloque];
    }

    public function cambiarVisibilidadBloque($idbloque): array
    {
        [$b, $s, $c] = $this->bloque($idbloque);
        $visible = $b->visible !== '1';
        $b->updateAttributes(['visible' => $visible ? '1' : '0', 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        $this->registrar($c, 'Bloque ' . Catalogo::nombre($b->tipo) . ' (sección ' . $s->nombrePanel() . '): ' . ($visible ? 'visible' : 'oculto'));
        return ['exito' => true, 'mensaje' => $visible ? 'El bloque se ve en la web.' : 'El bloque quedó oculto.'];
    }

    public function eliminarBloque($idbloque): array
    {
        [$b, $s, $c] = $this->bloque($idbloque);
        $fotos = $b->contenido() ? $b->contenido()->imagenes() : [];
        $b->delete();
        $this->borrarFotos(self::carpeta($c), $fotos);
        $this->registrar($c, 'Bloque eliminado: ' . Catalogo::nombre($b->tipo) . ' (sección ' . $s->nombrePanel() . ')');
        return ['exito' => true, 'mensaje' => 'Bloque eliminado.'];
    }

    /* ================= Plantillas ================= */

    public function aplicarPlantilla($idproyecto, $idplantilla, bool $reemplazar): array
    {
        $p = $this->proyecto($idproyecto);
        $t = ProyectoPlantilla::find()->with('secciones.bloques')->where(['idplantilla' => (int) $idplantilla, 'estado' => '1'])->one();
        if ($t === null) {
            return ['exito' => false, 'mensaje' => 'Elija una plantilla.'];
        }
        $actuales = ProyectoSeccion::find()->with('bloques')->where(['idproyecto' => $p->idproyecto])->all();
        if ($actuales && !$reemplazar) {
            return ['exito' => false, 'mensaje' => 'El proyecto ya tiene secciones. Para cargar la plantilla hay que reemplazarlas.'];
        }
        $tx = Yii::$app->db->beginTransaction();
        $fotosViejas = [];
        foreach ($actuales as $s) {
            foreach ($s->bloques as $b) {
                $fotosViejas = array_merge($fotosViejas, $b->contenido() ? $b->contenido()->imagenes() : []);
            }
            $s->delete();
        }
        foreach ($t->secciones as $i => $s) {
            CopiaPagina::seccion($s, ['idproyecto' => $p->idproyecto], ['orden' => $i + 1],
                ProyectoPlantilla::carpetaDe($t->idplantilla, 'bloques'), Proyecto::carpetaDe($p->idproyecto, 'bloques'));
        }
        $tx->commit();
        $this->borrarFotos(Proyecto::carpetaDe($p->idproyecto, 'bloques'), $fotosViejas);
        $this->registrar($p, 'Plantilla cargada: ' . $t->nombre . ($actuales ? ' (reemplazó ' . count($actuales) . ' secciones)' : ''));
        return ['exito' => true, 'mensaje' => 'Listo: se cargó la plantilla «' . $t->nombre . '». Reemplace los textos entre « ».'];
    }

    public function guardarComoPlantilla($idproyecto, array $post): array
    {
        $this->permisos->verificarPermiso('plantillas_editar');
        $p = $this->proyecto($idproyecto);
        $secciones = ProyectoSeccion::find()->with('bloques')->where(['idproyecto' => $p->idproyecto])->orderBy('orden')->all();
        if (!$secciones) {
            return ['exito' => false, 'mensaje' => 'El proyecto todavía no tiene secciones.'];
        }
        $t = new ProyectoPlantilla([
            'nombre' => $post['nombre'] ?? '',
            'descripcion' => 'Creada a partir de «' . $p->titulo . '».',
            'idecosistema' => !empty($post['ecosistema']) ? $p->idecosistema : null,
            'estado' => '1',
        ]);
        if (!$t->validate()) {
            return ['exito' => false, 'mensaje' => implode(' ', $t->getFirstErrors())];
        }
        $tx = Yii::$app->db->beginTransaction();
        $t->orden = (int) ProyectoPlantilla::find()->max('orden') + 1;
        $t->idusuario_registro = Yii::$app->user->id;
        $t->fecha_registro = date('Y-m-d H:i:s');
        $t->save(false);
        foreach ($secciones as $i => $s) {
            CopiaPagina::seccion($s, ['idplantilla' => $t->idplantilla], ['orden' => $i + 1],
                Proyecto::carpetaDe($p->idproyecto, 'bloques'), ProyectoPlantilla::carpetaDe($t->idplantilla, 'bloques'));
        }
        $tx->commit();
        $this->registrar($p, 'Se guardó como plantilla: ' . $t->nombre);
        return ['exito' => true, 'mensaje' => 'Plantilla «' . $t->nombre . '» creada. La encuentra en Proyectos → Plantillas.'];
    }

    /* ================= Archivos ================= */

    public function archivoImagen(string $tipo, int $id, string $carpeta, string $archivo, int $ancho): array
    {
        if ($tipo === 'proyecto') {
            $this->proyecto($id, true);
            $permitidas = ['imagenes', 'bloques', 'galeria', 'videos']; // videos: solo las miniaturas .jpg
            $base = Proyecto::carpetaDe($id);
        } else {
            $this->plantilla($id);
            $permitidas = ['bloques'];
            $base = ProyectoPlantilla::carpetaDe($id);
        }
        if (!in_array($carpeta, $permitidas, true) || !preg_match(\app\models\bloques\Bloque::PATRON_IMAGEN, $archivo)) {
            throw new NotFoundHttpException('Foto no encontrada.');
        }
        $ruta = $base . '/' . $carpeta . '/' . $archivo;
        if (!is_file($ruta)) {
            throw new NotFoundHttpException('La foto ya no está en el servidor.');
        }
        return ImagenVariante::obtener($ruta, $ancho);
    }

    /**
     * Fotos, videos y documentos del proyecto para los campos "elegir" del formulario de bloque
     * (fuente => [['id', 'titulo', 'miniatura', 'icono', 'nota'], …]). En una plantilla, null.
     * Sin $medios solo trae los ids y títulos (para validar al guardar).
     */
    /** Íconos (Font Awesome 4, panel) por clase de documento. */
    const ICONOS_DOC = ['pdf' => 'file-pdf-o', 'word' => 'file-word-o', 'excel' => 'file-excel-o', 'powerpoint' => 'file-powerpoint-o', 'imagen' => 'file-image-o', 'otro' => 'file-o'];

    private function fuentes($c, ?MediosProyecto $medios = null): ?array
    {
        if (!$c instanceof Proyecto) {
            return null;
        }
        $id = $c->idproyecto;
        $fotos = [];
        foreach (ProyectoImagen::find()->where(['idproyecto' => $id])->orderBy(['orden' => SORT_ASC, 'idimagen' => SORT_ASC])->all() as $i => $img) {
            $fotos[] = ['id' => (int) $img->idimagen, 'titulo' => 'Foto ' . ($i + 1),
                'miniatura' => $medios ? $medios->imagen('galeria', $img->archivo, 480) : null, 'nota' => $img->nombre_original];
        }
        $videos = [];
        foreach (ProyectoVideo::find()->where(['idproyecto' => $id])->orderBy(['orden' => SORT_ASC, 'idvideo' => SORT_ASC])->all() as $v) {
            $mini = null;
            if ($medios) {
                $mini = ($jpg = $v->miniatura()) ? $medios->imagen('videos', $jpg, 480) : $v->miniaturaEnlace();
            }
            $videos[] = ['id' => (int) $v->idvideo, 'titulo' => $v->titulo, 'miniatura' => $mini,
                'icono' => $v->tipo === 'archivo' ? 'film' : 'vimeo', 'nota' => $v->tipo === 'archivo' ? 'Archivo' : 'Enlace'];
        }
        $documentos = [];
        foreach (ProyectoDocumento::find()->where(['idproyecto' => $id, 'visible_inversor' => '1'])->orderBy(['iddocumento' => SORT_ASC])->all() as $d) {
            $documentos[] = ['id' => (int) $d->iddocumento, 'titulo' => $d->nombre, 'miniatura' => null,
                'icono' => self::ICONOS_DOC[$d->clase()], 'nota' => $d->tipoNombre() . ' · ' . strtoupper($d->extension())];
        }
        return ['fotos' => $fotos, 'videos' => $videos, 'documentos' => $documentos];
    }

    /* ================= Ayudas ================= */

    /** Cambia el orden con el vecino de arriba o de abajo. */
    private function intercambiar(\yii\db\ActiveQuery $hermanos, $fila, bool $subir): array
    {
        $vecino = (clone $hermanos)->andWhere([$subir ? '<' : '>', 'orden', $fila->orden])
            ->orderBy(['orden' => $subir ? SORT_DESC : SORT_ASC])->one();
        if ($vecino === null) {
            return ['exito' => false, 'mensaje' => $subir ? 'Ya está primero.' : 'Ya está último.'];
        }
        $tx = Yii::$app->db->beginTransaction();
        $orden = $fila->orden;
        $fila->updateAttributes(['orden' => $vecino->orden]);
        $vecino->updateAttributes(['orden' => $orden]);
        $tx->commit();
        return ['exito' => true, 'mensaje' => ''];
    }

    /** Hace lugar después de $orden (para insertar una copia justo debajo). */
    private function correrOrden(string $clase, array $donde, int $orden): void
    {
        $clase::updateAllCounters(['orden' => 1], ['and', $donde, ['>', 'orden', $orden]]);
    }

    private function borrarFoto(string $carpeta, ?string $archivo): void
    {
        if ($archivo && basename($archivo) === $archivo) {
            ImagenVariante::borrarCopias($carpeta . '/' . $archivo);
            SubidaImagen::borrar($carpeta, $archivo);
        }
    }

    private function borrarFotos(string $carpeta, array $archivos): void
    {
        foreach ($archivos as $a) {
            $this->borrarFoto($carpeta, $a);
        }
    }
}
