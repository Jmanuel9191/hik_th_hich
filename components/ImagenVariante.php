<?php

namespace app\components;

use yii\helpers\FileHelper;

/**
 * Copias livianas de las fotos privadas (proyectos y plantillas), para que la web cargue rápido:
 * se generan la primera vez que alguien las pide y quedan en <carpeta>/cache/ (ej. abc-960.webp).
 *  - WEBP si el servidor lo soporta (GD con WebP); si no, JPG (o PNG si la original es PNG).
 *  - Nunca agranda: si la foto es más chica que el ancho pedido, queda de su tamaño.
 *  - Si algo falla (memoria, formato), se sirve la original: la página nunca se rompe por una foto.
 */
final class ImagenVariante
{
    /** Anchos que se generan (los pide el srcset de la web). */
    const ANCHOS = [480, 960, 1440, 1920];
    const CALIDAD = 80;

    /** Ancho válido más cercano por arriba (o el mayor). */
    public static function ancho(int $pedido): int
    {
        foreach (self::ANCHOS as $a) {
            if ($pedido <= $a) {
                return $a;
            }
        }
        return self::ANCHOS[count(self::ANCHOS) - 1];
    }

    public static function webp(): bool
    {
        return function_exists('imagewebp') && (gd_info()['WebP Support'] ?? false);
    }

    /**
     * Ruta de la copia del ancho pedido (la crea si no existe). Si no se puede, la original.
     * @return array ['ruta' => string, 'mime' => string]
     */
    public static function obtener(string $original, int $ancho): array
    {
        $ancho = self::ancho($ancho);
        $info = @getimagesize($original);
        if ($info === false || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true) || !extension_loaded('gd')) {
            return ['ruta' => $original, 'mime' => $info['mime'] ?? 'application/octet-stream'];
        }
        [$ext, $mime] = self::webp() ? ['webp', 'image/webp'] : ($info[2] === IMAGETYPE_PNG ? ['png', 'image/png'] : ['jpg', 'image/jpeg']);
        $carpeta = dirname($original) . '/cache';
        $destino = $carpeta . '/' . pathinfo($original, PATHINFO_FILENAME) . '-' . $ancho . '.' . $ext;
        if (is_file($destino) && filemtime($destino) >= filemtime($original)) {
            return ['ruta' => $destino, 'mime' => $mime];
        }
        return self::generar($original, $destino, $info, min($ancho, (int) $info[0]), $ext)
            ? ['ruta' => $destino, 'mime' => $mime]
            : ['ruta' => $original, 'mime' => $info['mime']];
    }

    /** Borra las copias de una foto (al reemplazarla o borrarla). */
    public static function borrarCopias(string $original): void
    {
        $base = pathinfo($original, PATHINFO_FILENAME);
        foreach (glob(dirname($original) . '/cache/' . $base . '-*') ?: [] as $f) {
            @unlink($f);
        }
    }

    private static function generar(string $original, string $destino, array $info, int $ancho, string $ext): bool
    {
        $memoria = ini_get('memory_limit');
        @ini_set('memory_limit', '512M'); // fotos grandes de cámara
        try {
            $img = match ($info[2]) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($original),
                IMAGETYPE_PNG => @imagecreatefrompng($original),
                IMAGETYPE_WEBP => @imagecreatefromwebp($original),
            };
            if (!$img) {
                return false;
            }
            $img = self::orientar($img, $original, $info[2]);
            $alto = (int) round(imagesy($img) * $ancho / max(1, imagesx($img)));
            $chica = imagescale($img, $ancho, $alto, IMG_BICUBIC);
            imagedestroy($img);
            if (!$chica) {
                return false;
            }
            if ($ext !== 'jpg') {
                imagealphablending($chica, false);
                imagesavealpha($chica, true);
            }
            FileHelper::createDirectory(dirname($destino), 0755);
            $tmp = $destino . '.' . uniqid('', true) . '.tmp';
            $ok = match ($ext) {
                'webp' => imagewebp($chica, $tmp, self::CALIDAD),
                'png' => imagepng($chica, $tmp, 7),
                default => imagejpeg($chica, $tmp, self::CALIDAD),
            };
            imagedestroy($chica);
            return $ok && rename($tmp, $destino); // atómico: nunca se sirve una a medio escribir
        } catch (\Throwable $e) {
            \Yii::warning('No se pudo achicar ' . basename($original) . ': ' . $e->getMessage(), __METHOD__);
            return false;
        } finally {
            @ini_set('memory_limit', $memoria);
            if (isset($tmp) && is_file($tmp)) {
                @unlink($tmp);
            }
        }
    }

    /** Fotos de celular: gira según el dato EXIF de orientación. */
    private static function orientar($img, string $original, int $tipo)
    {
        if ($tipo !== IMAGETYPE_JPEG || !function_exists('exif_read_data')) {
            return $img;
        }
        $o = (int) (@exif_read_data($original)['Orientation'] ?? 1);
        $grados = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
        if ($grados === 0) {
            return $img;
        }
        $girada = imagerotate($img, $grados, 0);
        if ($girada) {
            imagedestroy($img);
            return $girada;
        }
        return $img;
    }
}
