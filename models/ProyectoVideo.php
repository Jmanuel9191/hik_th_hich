<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Video de un proyecto: archivo subido (privado/proyectos/<id>/videos) o enlace de YouTube / Vimeo.
 *
 * @property int $idvideo
 * @property int $idproyecto
 * @property string $titulo
 * @property string $tipo        archivo | enlace
 * @property string|null $archivo
 * @property string|null $url
 * @property string|null $nombre_original
 * @property string|null $mime
 * @property int|null $tamano
 * @property int $orden
 * @property int|null $idusuario
 * @property string $fecha_registro
 */
class ProyectoVideo extends ActiveRecord
{
    /** Extensión => tipos MIME aceptados (celular: MP4 de Android, MOV/MP4 de iPhone). */
    const FORMATOS = [
        'mp4' => ['video/mp4', 'application/mp4'],
        'm4v' => ['video/mp4', 'video/x-m4v'],
        'mov' => ['video/quicktime', 'video/mp4'],
        'webm' => ['video/webm'],
    ];

    public static function tableName()
    {
        return 'proyecto_video';
    }

    public function rules()
    {
        return [
            [['titulo', 'url'], 'filter', 'filter' => fn($v) => trim((string) $v)],
            [['titulo'], 'required'],
            [['titulo'], 'string', 'max' => 150],
            [['url'], 'string', 'max' => 500],
        ];
    }

    public function attributeLabels()
    {
        return ['titulo' => 'Título del video', 'url' => 'Enlace (YouTube o Vimeo)', 'archivo' => 'Archivo de video'];
    }

    /** URL para incrustar el enlace (YouTube sin cookies / Vimeo), o null si no se reconoce. */
    public static function embed($url)
    {
        $url = trim((string) $url);
        if (preg_match('~^https?://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/' . $m[1];
        }
        if (preg_match('~^https?://(?:www\.)?vimeo\.com/(\d+)(?:/([0-9a-f]+))?~', $url, $m)
            || preg_match('~^https?://player\.vimeo\.com/video/(\d+)(?:\?(?:.*&)?h=([0-9a-f]+))?~', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1] . (!empty($m[2]) ? '?h=' . $m[2] : '');
        }
        return null;
    }

    /** Miniatura .jpg que se genera al subir el archivo (VideoFormato), o null si no hay. Va en la misma carpeta. */
    public function miniatura(): ?string
    {
        if ($this->tipo !== 'archivo' || !$this->archivo) {
            return null;
        }
        $jpg = pathinfo($this->archivo, PATHINFO_FILENAME) . '.jpg';
        return is_file(Proyecto::carpetaDe($this->idproyecto, 'videos') . '/' . $jpg) ? $jpg : null;
    }

    /** Miniatura de YouTube (la de Vimeo necesita su API: se muestra un ícono). */
    public function miniaturaEnlace(): ?string
    {
        $embed = $this->tipo === 'enlace' ? self::embed($this->url) : null;
        return $embed && preg_match('~youtube-nocookie\.com/embed/([A-Za-z0-9_-]{11})~', $embed, $m)
            ? 'https://i.ytimg.com/vi/' . $m[1] . '/hqdefault.jpg' : null;
    }

    /** Tipo con el que se manda al navegador: un MOV con H.264 se reproduce como MP4. */
    public static function mimeReproduccion(?string $mime): string
    {
        return in_array($mime, ['video/quicktime', 'video/x-m4v', 'application/mp4', null, ''], true) ? 'video/mp4' : $mime;
    }

    public function src()
    {
        return $this->tipo === 'archivo'
            ? Url::to(['/admin/banco-proyectos/video', 'id' => $this->idvideo])
            : self::embed($this->url);
    }
}
