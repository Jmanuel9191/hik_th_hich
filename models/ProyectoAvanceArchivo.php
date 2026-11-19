<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Archivo de un avance: foto, video, documento (privado/proyectos/<id>/avances/<idavance>/) o enlace de YouTube / Vimeo.
 *
 * @property int $idarchivo
 * @property int $idavance
 * @property string $tipo       foto | video | documento | enlace
 * @property string|null $archivo
 * @property string|null $url
 * @property string|null $leyenda    descripción corta que se muestra debajo (opcional)
 * @property string|null $nombre_original
 * @property string|null $mime
 * @property int|null $tamano
 * @property int $orden
 * @property int|null $idusuario
 * @property string $fecha_registro
 */
class ProyectoAvanceArchivo extends ActiveRecord
{
    const MAX_LEYENDA = 300;

    const TIPOS = ['foto' => 'Foto', 'video' => 'Video', 'documento' => 'Documento', 'enlace' => 'Enlace de video'];

    public static function tableName()
    {
        return 'proyecto_avance_archivo';
    }

    /** Qué tipo es según la extensión del archivo (null = no permitido). */
    public static function tipoDeExtension($ext)
    {
        $ext = strtolower($ext);
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return 'foto';
        }
        if (isset(ProyectoVideo::FORMATOS[$ext])) {
            return 'video';
        }
        if (isset(ProyectoDocumento::FORMATOS[$ext])) {
            return 'documento';
        }
        return null;
    }

    public function src()
    {
        return $this->tipo === 'enlace' ? ProyectoVideo::embed($this->url) : Url::to(['/admin/banco-proyectos/avance-archivo', 'id' => $this->idarchivo]);
    }
}
