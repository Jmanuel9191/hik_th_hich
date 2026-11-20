<?php

namespace app\components;

use app\models\Proyecto;
use app\models\ProyectoAvance;
use app\models\ProyectoAvanceArchivo;
use app\models\ProyectoDescarga;
use app\models\ProyectoDocumento;
use app\models\ProyectoHistorial;
use app\models\ProyectoVideo;
use Yii;
use yii\helpers\FileHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * Avances de proyecto. El acceso (permiso + área) lo resuelve ProyectoService.
 * % de avance: el que informa el gerente; si lo deja vacío y eligió etapa, el de la etapa (Proyecto::PORCENTAJE_ETAPA).
 * La ficha del proyecto toma la etapa y el % del avance visible más reciente que los tenga.
 * Archivos en privado/proyectos/<id>/avances/<idavance>/. Todo queda en el historial del proyecto.
 */
class AvanceService implements InterfaceAvances
{
    private $proyectos;

    public function __construct(InterfaceProyecto $proyectos)
    {
        $this->proyectos = $proyectos;
    }

    private function proyectoEditable($idproyecto)
    {
        $p = $this->proyectos->buscar($idproyecto, 'proyectos_editar');
        if (!$this->proyectos->puedeEditar($p)) {
            throw new ForbiddenHttpException('No puede editar este proyecto.');
        }
        return $p;
    }

    private function carpeta(ProyectoAvance $a)
    {
        return Proyecto::carpetaDe($a->idproyecto, 'avances/' . (int) $a->idavance);
    }

    public function listar(Proyecto $p): array
    {
        return $p->getAvances()->with(['archivos', 'usuario'])->all();
    }

    public function nuevo($idproyecto): ProyectoAvance
    {
        $p = $this->proyectoEditable($idproyecto);
        $a = new ProyectoAvance(['idproyecto' => $p->idproyecto, 'fecha' => date('Y-m-d')]);
        $a->populateRelation('proyecto', $p);
        return $a;
    }

    public function buscar($idavance, $editar = false): ProyectoAvance
    {
        $a = ProyectoAvance::findOne((int) $idavance);
        if ($a === null) {
            throw new NotFoundHttpException('El avance no existe.');
        }
        $a->populateRelation('proyecto', $editar ? $this->proyectoEditable($a->idproyecto) : $this->proyectos->buscar($a->idproyecto));
        return $a;
    }

    public function guardar(ProyectoAvance $a, array $post)
    {
        $nuevo = $a->isNewRecord;
        $antes = $a->getAttributes();
        $datos = $post['ProyectoAvance'] ?? [];
        $a->setAttributes(array_intersect_key($datos, array_flip(['fecha', 'titulo', 'etapa', 'porcentaje'])), false);
        if (array_key_exists('texto', $datos)) {
            $html = HtmlEditor::limpiar($datos['texto']);
            $plano = preg_replace('/[\s\x{00A0}]+/u', '', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $a->texto = $plano === '' ? null : $html;
        }
        if ($a->proyecto->tipo !== 'financiamiento') {
            $a->etapa = null; // un "busca inversión" no tiene obra: el avance es solo novedad
            $a->porcentaje = null;
        }
        if (!$a->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }
        if (($a->porcentaje === null || $a->porcentaje === '') && $a->etapa) {
            $a->porcentaje = Proyecto::PORCENTAJE_ETAPA[$a->etapa]; // si no lo ajustó, el de la etapa
        }
        if ($nuevo) {
            $a->estado = '1';
            $a->idusuario_registro = Yii::$app->user->id;
            $a->fecha_registro = date('Y-m-d H:i:s');
        } else {
            $a->fecha_actualizacion = date('Y-m-d H:i:s');
        }
        $a->save(false);

        $detalle = [];
        if ($nuevo) {
            $detalle[] = 'Avance agregado: ' . $a->titulo . ' (' . date('d/m/Y', strtotime($a->fecha)) . ')';
            if ($a->etapa) {
                $detalle[] = 'Etapa: ' . $a->etapaNombre();
            }
            if ($a->porcentaje !== null) {
                $detalle[] = 'Avance: ' . $a->porcentaje . ' %';
            }
        } else {
            $detalle[] = 'Avance editado: ' . $a->titulo;
            $nombres = ['fecha' => 'Fecha', 'titulo' => 'Título', 'etapa' => 'Etapa', 'porcentaje' => '%'];
            foreach ($nombres as $c => $etiqueta) {
                $x = (string) $antes[$c];
                $y = (string) $a->$c;
                if ($x !== $y) {
                    if ($c === 'etapa') {
                        [$x, $y] = [Proyecto::ETAPAS[$x] ?? $x, Proyecto::ETAPAS[$y] ?? $y];
                    }
                    $detalle[] = $etiqueta . ': ' . ($x === '' ? '(vacío)' : $x) . ' → ' . ($y === '' ? '(vacío)' : $y);
                }
            }
            if ((string) $antes['texto'] !== (string) $a->texto) {
                $detalle[] = 'Texto: modificado';
            }
        }
        ProyectoHistorial::registrar($a->idproyecto, 'Avances', implode("\n", $detalle));
        $this->recalcular($a->proyecto);
        return ['exito' => true, 'mensaje' => $nuevo ? 'Avance agregado. Si desea, súmele fotos, videos o documentos.' : 'Cambios guardados.'];
    }

    public function cambiarEstado($idavance)
    {
        $a = $this->buscar($idavance, true);
        $mostrar = $a->estado !== '1';
        $a->updateAttributes(['estado' => $mostrar ? '1' : '0', 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        ProyectoHistorial::registrar($a->idproyecto, 'Avances', 'Avance "' . $a->titulo . '": ' . ($mostrar ? 'visible' : 'oculto (los inversores no lo ven)'));
        $this->recalcular($a->proyecto);
        return ['exito' => true, 'mensaje' => $mostrar ? 'El avance se muestra.' : 'El avance quedó oculto.'];
    }

    public function eliminar($idavance)
    {
        $a = $this->buscar($idavance, true);
        $carpeta = $this->carpeta($a);
        $titulo = $a->titulo;
        $p = $a->proyecto;
        $a->delete(); // los archivos de la tabla se borran en cascada
        FileHelper::removeDirectory($carpeta);
        ProyectoHistorial::registrar($p->idproyecto, 'Avances', 'Avance eliminado: ' . $titulo);
        $this->recalcular($p);
        return ['exito' => true, 'mensaje' => 'Avance eliminado.'];
    }

    public function agregarArchivo($idavance, array $post)
    {
        $a = $this->buscar($idavance, true);
        $url = trim((string) ($post['url'] ?? ''));
        $leyenda = $this->leyenda($post['leyenda'] ?? '');
        if ($leyenda === false) {
            return ['exito' => false, 'mensaje' => 'La descripción puede tener hasta ' . ProyectoAvanceArchivo::MAX_LEYENDA . ' caracteres.'];
        }
        $f = UploadedFile::getInstanceByName('archivo');
        if ($f !== null && $f->error === UPLOAD_ERR_NO_FILE) {
            $f = null;
        }
        if ($f === null && $url === '') {
            return ['exito' => false, 'mensaje' => 'Elija un archivo o pegue un enlace de YouTube o Vimeo.'];
        }

        $nuevo = new ProyectoAvanceArchivo(['idavance' => $a->idavance, 'idusuario' => Yii::$app->user->id, 'fecha_registro' => date('Y-m-d H:i:s')]);
        if ($f !== null) {
            $tipo = ProyectoAvanceArchivo::tipoDeExtension($f->extension);
            if ($tipo === null) {
                return ['exito' => false, 'mensaje' => '"' . $f->name . '": formato no permitido. Fotos (JPG, PNG, WEBP), videos (MP4, MOV, WEBM) o documentos (PDF, Word, Excel, PowerPoint).'];
            }
            if ($m = $this->lleno($a, $tipo)) {
                return ['exito' => false, 'mensaje' => $m];
            }
            $carpeta = $this->carpeta($a);
            if ($tipo === 'foto') {
                $r = SubidaImagen::guardar($f, $carpeta, Proyecto::MAX_IMAGEN_MB);
                if ($r['ok']) {
                    $r['mime'] = FileHelper::getMimeType($carpeta . '/' . $r['archivo']);
                    $r['tamano'] = (int) $f->size;
                }
            } else {
                $r = $tipo === 'video'
                    ? SubidaArchivo::guardar($f, $carpeta, ProyectoVideo::FORMATOS, Proyecto::MAX_VIDEO_MB)
                    : SubidaArchivo::guardar($f, $carpeta, ProyectoDocumento::FORMATOS, Proyecto::MAX_DOCUMENTO_MB);
            }
            if (!$r['ok']) {
                return ['exito' => false, 'mensaje' => $r['error']];
            }
            $nuevo->setAttributes(['tipo' => $tipo, 'archivo' => $r['archivo'], 'mime' => $r['mime'], 'tamano' => $r['tamano'],
                'nombre_original' => mb_substr($f->name, 0, 255)], false);
        } else {
            if (ProyectoVideo::embed($url) === null || mb_strlen($url) > 500) {
                return ['exito' => false, 'mensaje' => 'El enlace tiene que ser de YouTube o Vimeo. Ej.: https://vimeo.com/123456789'];
            }
            if ($m = $this->lleno($a, 'enlace')) {
                return ['exito' => false, 'mensaje' => $m];
            }
            $nuevo->setAttributes(['tipo' => 'enlace', 'url' => $url], false);
        }
        $nuevo->leyenda = $leyenda;
        $nuevo->orden = (int) ProyectoAvanceArchivo::find()->where(['idavance' => $a->idavance])->max('orden') + 1;
        $nuevo->save(false);
        ProyectoHistorial::registrar($a->idproyecto, 'Avances', 'Avance "' . $a->titulo . '": '
            . mb_strtolower(ProyectoAvanceArchivo::TIPOS[$nuevo->tipo]) . ' agregado' . ($nuevo->nombre_original ? ' (' . $nuevo->nombre_original . ')' : ''));
        return ['exito' => true, 'mensaje' => ProyectoAvanceArchivo::TIPOS[$nuevo->tipo] . ' agregado al avance.'];
    }

    /** Leyenda limpia (null si está vacía; false si es demasiado larga). */
    private function leyenda($texto)
    {
        $texto = trim(preg_replace('/\s+/u', ' ', (string) $texto));
        if ($texto === '') {
            return null;
        }
        return mb_strlen($texto) > ProyectoAvanceArchivo::MAX_LEYENDA ? false : $texto;
    }

    public function cambiarLeyenda($idarchivo, $leyenda)
    {
        $x = ProyectoAvanceArchivo::findOne((int) $idarchivo) ?? throw new NotFoundHttpException('El archivo ya no existe.');
        $a = $this->buscar($x->idavance, true);
        $nueva = $this->leyenda($leyenda);
        if ($nueva === false) {
            return ['exito' => false, 'mensaje' => 'La descripción puede tener hasta ' . ProyectoAvanceArchivo::MAX_LEYENDA . ' caracteres.'];
        }
        if ((string) $nueva === (string) $x->leyenda) {
            return ['exito' => true, 'mensaje' => 'No había cambios.'];
        }
        $antes = $x->leyenda;
        $x->updateAttributes(['leyenda' => $nueva]);
        $que = mb_strtolower(ProyectoAvanceArchivo::TIPOS[$x->tipo]) . ($x->nombre_original ? ' ' . $x->nombre_original : '');
        ProyectoHistorial::registrar($a->idproyecto, 'Avances', 'Avance "' . $a->titulo . '", ' . $que . "\nDescripción: "
            . ($antes === null ? '(vacía)' : $antes) . ' → ' . ($nueva === null ? '(vacía)' : $nueva));
        return ['exito' => true, 'mensaje' => 'Descripción guardada.'];
    }

    /** Mensaje si el avance ya tiene el máximo de ese tipo. */
    private function lleno(ProyectoAvance $a, $tipo)
    {
        $max = ProyectoAvance::MAXIMOS[$tipo];
        $n = ProyectoAvanceArchivo::find()->where(['idavance' => $a->idavance, 'tipo' => $tipo])->count();
        return $n >= $max ? 'Este avance ya tiene ' . $max . ' ' . mb_strtolower(ProyectoAvanceArchivo::TIPOS[$tipo]) . '(s), el máximo. Cree otro avance o borre uno.' : null;
    }

    public function eliminarArchivo($idarchivo)
    {
        $x = ProyectoAvanceArchivo::findOne((int) $idarchivo) ?? throw new NotFoundHttpException('El archivo ya no existe.');
        $a = $this->buscar($x->idavance, true);
        if ($x->archivo) {
            SubidaArchivo::borrar($this->carpeta($a), $x->archivo);
        }
        $x->delete();
        ProyectoHistorial::registrar($a->idproyecto, 'Avances', 'Avance "' . $a->titulo . '": ' . mb_strtolower(ProyectoAvanceArchivo::TIPOS[$x->tipo])
            . ' eliminado' . ($x->nombre_original ? ' (' . $x->nombre_original . ')' : ''));
        return ['exito' => true, 'mensaje' => 'Eliminado.'];
    }

    public function archivo($idarchivo): array
    {
        $x = ProyectoAvanceArchivo::findOne((int) $idarchivo);
        if ($x === null || $x->tipo === 'enlace') {
            throw new NotFoundHttpException('El archivo ya no existe.');
        }
        $a = $this->buscar($x->idavance);
        $ruta = $this->carpeta($a) . '/' . basename($x->archivo);
        if (!is_file($ruta)) {
            throw new NotFoundHttpException('El archivo ya no está en el servidor.');
        }
        if ($x->tipo === 'documento') {
            ProyectoDescarga::registrarAvance($x, $a->idproyecto);
        }
        return [
            'ruta' => $ruta,
            'nombre' => $x->nombre_original ?: $x->archivo,
            'mime' => $x->mime === 'video/quicktime' ? 'video/mp4' : $x->mime,
            'inline' => $x->tipo !== 'documento',
        ];
    }

    public function recalcular(Proyecto $p)
    {
        $visibles = ProyectoAvance::find()->where(['idproyecto' => $p->idproyecto, 'estado' => '1'])
            ->orderBy(['fecha' => SORT_DESC, 'idavance' => SORT_DESC]);
        $conEtapa = (clone $visibles)->andWhere(['not', ['etapa' => null]])->one();
        $conPorcentaje = (clone $visibles)->andWhere(['not', ['porcentaje' => null]])->one();

        $cambios = [];
        if ($p->tipo === 'financiamiento' && $conEtapa && $conEtapa->etapa !== $p->etapa) {
            ProyectoHistorial::registrar($p->idproyecto, 'Etapa', 'Etapa: ' . ($p->etapaNombre() ?? '(vacío)') . ' → ' . $conEtapa->etapaNombre() . ' (por el avance "' . $conEtapa->titulo . '")');
            $cambios['etapa'] = $conEtapa->etapa;
        }
        $etapa = $cambios['etapa'] ?? $p->etapa;
        $porcentaje = $p->tipo !== 'financiamiento' ? null
            : ($conPorcentaje ? (int) $conPorcentaje->porcentaje : (Proyecto::PORCENTAJE_ETAPA[$etapa] ?? null));
        if ($porcentaje !== ($p->porcentaje_avance === null ? null : (int) $p->porcentaje_avance)) {
            $cambios['porcentaje_avance'] = $porcentaje;
        }
        if ($cambios) {
            $p->updateAttributes($cambios + ['fecha_actualizacion' => date('Y-m-d H:i:s')]);
        }
    }
}
