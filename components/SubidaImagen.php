<?php

namespace app\components;

use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Guarda imágenes subidas de forma segura:
 *  - solo jpg, jpeg, png, webp (y svg si se permite)
 *  - verifica que el contenido sea realmente una imagen (no solo la extensión)
 *  - SVG: rechaza los que traen scripts o eventos
 *  - nombre aleatorio (nunca el nombre del usuario)
 */
class SubidaImagen
{
    const EXT_FOTO = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * @return array ['ok' => true, 'archivo' => 'abc.jpg'] o ['ok' => false, 'error' => '...']
     */
    public static function guardar(UploadedFile $f, $carpeta, $maxMb = 5, $permitirSvg = false)
    {
        if ($f->error !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'No se pudo subir "' . $f->name . '". Intentá de nuevo.'];
        }
        $ext = strtolower($f->extension);
        $permitidas = $permitirSvg ? array_merge(self::EXT_FOTO, ['svg']) : self::EXT_FOTO;
        if (!in_array($ext, $permitidas, true)) {
            return ['ok' => false, 'error' => '"' . $f->name . '": formato no permitido. Usá ' . strtoupper(implode(', ', $permitidas)) . '.'];
        }
        if ($f->size > $maxMb * 1024 * 1024) {
            return ['ok' => false, 'error' => '"' . $f->name . '" pesa más de ' . $maxMb . ' MB.'];
        }

        if ($ext === 'svg') {
            $svg = (string) file_get_contents($f->tempName);
            if (!preg_match('/<svg[\s>]/i', $svg)
                || preg_match('/<script|<foreignObject|<iframe|<embed|<object|javascript:|\son[a-z]+\s*=/i', $svg)) {
                return ['ok' => false, 'error' => '"' . $f->name . '": el SVG no es válido o trae código. Exportalo de nuevo como SVG simple.'];
            }
        } else {
            $info = @getimagesize($f->tempName);
            $tipos = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
            if ($info === false || !in_array($info[2], $tipos, true)) {
                return ['ok' => false, 'error' => '"' . $f->name . '" no es una imagen válida.'];
            }
        }

        FileHelper::createDirectory($carpeta, 0755);
        $nombre = Yii::$app->security->generateRandomString(24) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $ok = is_uploaded_file($f->tempName) ? $f->saveAs($carpeta . '/' . $nombre) : copy($f->tempName, $carpeta . '/' . $nombre);
        if (!$ok) {
            return ['ok' => false, 'error' => 'No se pudo guardar "' . $f->name . '" en el servidor.'];
        }
        return ['ok' => true, 'archivo' => $nombre];
    }

    /** Borra un archivo de la carpeta (sin permitir rutas tipo ../). */
    public static function borrar($carpeta, $archivo)
    {
        if ($archivo && basename($archivo) === $archivo && is_file($carpeta . '/' . $archivo)) {
            @unlink($carpeta . '/' . $archivo);
        }
    }
}
