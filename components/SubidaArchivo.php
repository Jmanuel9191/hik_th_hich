<?php

namespace app\components;

use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Guarda archivos que no son imágenes (videos, documentos) de forma segura:
 *  - extensión permitida Y contenido real del tipo esperado (finfo, no solo el nombre)
 *  - tamaño máximo
 *  - nombre aleatorio (nunca el del usuario)
 * Se usa con carpetas privadas (privado/…): el archivo nunca queda con URL pública.
 */
class SubidaArchivo
{
    /**
     * @param array $formatos extensión => [tipos MIME aceptados]
     * @return array ['ok' => true, 'archivo' => 'abc.mp4', 'mime' => '…', 'tamano' => 123] o ['ok' => false, 'error' => '…']
     */
    public static function guardar(UploadedFile $f, $carpeta, array $formatos, $maxMb)
    {
        if ($f->error === UPLOAD_ERR_INI_SIZE || $f->error === UPLOAD_ERR_FORM_SIZE) {
            return ['ok' => false, 'error' => '"' . $f->name . '" es más grande de lo que acepta el servidor (' . self::limiteServidorTexto() . ').'];
        }
        if ($f->error !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'No se pudo subir "' . $f->name . '". Intentá de nuevo.'];
        }
        $ext = strtolower($f->extension);
        if (!isset($formatos[$ext])) {
            return ['ok' => false, 'error' => '"' . $f->name . '": formato no permitido. Usá ' . strtoupper(implode(', ', array_keys($formatos))) . '.'];
        }
        if ($f->size > $maxMb * 1024 * 1024) {
            return ['ok' => false, 'error' => '"' . $f->name . '" pesa más de ' . $maxMb . ' MB.'];
        }
        $mime = (string) FileHelper::getMimeType($f->tempName, null, false);
        if (!in_array($mime, $formatos[$ext], true)) {
            return ['ok' => false, 'error' => '"' . $f->name . '" no es un archivo ' . strtoupper($ext) . ' válido.'];
        }

        FileHelper::createDirectory($carpeta, 0755);
        $nombre = Yii::$app->security->generateRandomString(24) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $ok = is_uploaded_file($f->tempName) ? $f->saveAs($carpeta . '/' . $nombre) : copy($f->tempName, $carpeta . '/' . $nombre);
        if (!$ok) {
            return ['ok' => false, 'error' => 'No se pudo guardar "' . $f->name . '" en el servidor.'];
        }
        return ['ok' => true, 'archivo' => $nombre, 'mime' => $mime, 'tamano' => (int) $f->size];
    }

    /** Borra un archivo de la carpeta (sin permitir rutas tipo ../). */
    public static function borrar($carpeta, $archivo)
    {
        if ($archivo && basename($archivo) === $archivo && is_file($carpeta . '/' . $archivo)) {
            @unlink($carpeta . '/' . $archivo);
        }
    }

    /** Lo máximo que el PHP del servidor deja subir en un envío, en bytes. */
    public static function limiteServidor()
    {
        $b = fn($v) => (int) $v * (['k' => 1024, 'm' => 1048576, 'g' => 1073741824][strtolower(substr(trim($v), -1))] ?? 1);
        $limites = array_filter([$b(ini_get('upload_max_filesize')), $b(ini_get('post_max_size'))]);
        return $limites ? min($limites) : 0;
    }

    public static function limiteServidorTexto()
    {
        return 'máx. ' . round(self::limiteServidor() / 1048576) . ' MB';
    }

    /** "12,4 MB" */
    public static function peso($bytes)
    {
        if ($bytes === null) {
            return '—';
        }
        return $bytes >= 1048576 ? number_format($bytes / 1048576, 1, ',', '.') . ' MB' : max(1, round($bytes / 1024)) . ' KB';
    }
}
