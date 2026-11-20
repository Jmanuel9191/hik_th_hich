<?php

namespace app\components;

use Yii;

/**
 * Formatos de video: qué se puede ver en el navegador y qué hay que convertir.
 *
 *  - MP4 / MOV / M4V con H.264 (avc1) → se ven en todos los navegadores: se dejan como están.
 *  - MP4 / MOV con H.265 (hvc1/hev1, iPhone "Alta eficiencia") → Safari sí, Chrome y Firefox no siempre:
 *    con ffmpeg se pasan a H.264; sin ffmpeg se aceptan con un aviso.
 *  - WEBM → se ve en todos los navegadores modernos.
 *  - AVI, MKV, MTS/M2TS (cámaras), 3GP, WMV, FLV, MPG y MP4 con otros códecs → con ffmpeg se convierten a MP4 (H.264 + AAC);
 *    sin ffmpeg se rechazan con instrucciones (HandBrake).
 *  - Miniatura .jpg del primer segundo (con ffmpeg). Sin ffmpeg, el navegador muestra el primer cuadro.
 *
 * ffmpeg: se busca en Yii::$app->params['ffmpeg'] (ruta), o en /usr/bin y /usr/local/bin. Si el hosting no deja
 * ejecutar programas (exec deshabilitado), todo sigue funcionando sin conversión.
 */
final class VideoFormato
{
    /** Se ven en el navegador tal cual (si el códec es H.264 o es WEBM). */
    const DIRECTOS = [
        'mp4' => ['video/mp4', 'application/mp4'],
        'm4v' => ['video/mp4', 'video/x-m4v'],
        'mov' => ['video/quicktime', 'video/mp4'],
        'webm' => ['video/webm'],
    ];

    /** Hay que convertirlos (solo con ffmpeg). El original nunca se sirve: se reemplaza por el MP4. */
    const CONVERTIBLES = [
        'avi' => ['video/x-msvideo', 'video/avi', 'video/msvideo'],
        'mkv' => ['video/x-matroska', 'video/webm', 'application/octet-stream'],
        'mts' => ['video/mp2t', 'application/octet-stream'],
        'm2ts' => ['video/mp2t', 'application/octet-stream'],
        '3gp' => ['video/3gpp', 'video/mp4', 'audio/3gpp'],
        'wmv' => ['video/x-ms-wmv', 'video/x-ms-asf', 'application/vnd.ms-asf'],
        'flv' => ['video/x-flv'],
        'mpg' => ['video/mpeg'],
        'mpeg' => ['video/mpeg'],
    ];

    /** Códecs de video que todos los navegadores reproducen dentro de un MP4. */
    const CODECS_OK = ['avc1', 'avc3'];
    const CODECS_HEVC = ['hvc1', 'hev1'];

    /** Tiempo máximo de una conversión (segundos). */
    const TIEMPO_MAXIMO = 900;

    private static $ffmpeg = false;

    /** Ruta de ffmpeg, o null si no hay (o no se puede ejecutar). */
    public static function ffmpeg(): ?string
    {
        if (self::$ffmpeg !== false) {
            return self::$ffmpeg;
        }
        self::$ffmpeg = null;
        if (!function_exists('proc_open') || in_array('proc_open', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true)) {
            return null;
        }
        $candidatos = array_filter([Yii::$app->params['ffmpeg'] ?? null, '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/opt/homebrew/bin/ffmpeg']);
        foreach ($candidatos as $ruta) {
            if (@is_file($ruta) && @is_executable($ruta)) {
                return self::$ffmpeg = $ruta;
            }
        }
        return null;
    }

    /** Extensión → tipos MIME aceptados al subir (los convertibles solo si hay ffmpeg). */
    public static function formatosAceptados(): array
    {
        return self::ffmpeg() ? self::DIRECTOS + self::CONVERTIBLES : self::DIRECTOS;
    }

    /** "MP4, MOV, WEBM, AVI, MKV…" para la ayuda del formulario. */
    public static function textoFormatos(): string
    {
        return strtoupper(implode(', ', array_keys(self::formatosAceptados())));
    }

    /** Para el accept="" del input. */
    public static function accept(): string
    {
        return implode(',', array_map(fn($e) => '.' . $e, array_keys(self::formatosAceptados()))) . ',video/*';
    }

    /**
     * Mensaje si la extensión no se puede usar en este servidor (convertible sin ffmpeg), o null.
     */
    public static function rechazo(string $ext): ?string
    {
        $ext = strtolower($ext);
        if (isset(self::DIRECTOS[$ext]) || (isset(self::CONVERTIBLES[$ext]) && self::ffmpeg())) {
            return null;
        }
        if (isset(self::CONVERTIBLES[$ext])) {
            return 'Los videos ' . strtoupper($ext) . ' no se ven en el navegador y este servidor no puede convertirlos. '
                . 'Páselo a MP4 con HandBrake (gratis, handbrake.fr: preset «Fast 1080p30») y súbalo de nuevo.';
        }
        return null; // otra extensión: la rechaza SubidaArchivo con la lista de formatos
    }

    /**
     * Qué hacer con un archivo ya guardado.
     * @return array ['accion' => 'dejar'|'convertir'|'rechazar', 'codec' => ?string, 'aviso' => ?string]
     */
    public static function analizar(string $ruta, string $ext): array
    {
        $ext = strtolower($ext);
        if ($ext === 'webm') {
            return ['accion' => 'dejar', 'codec' => null, 'aviso' => null];
        }
        if (isset(self::CONVERTIBLES[$ext])) {
            return ['accion' => self::ffmpeg() ? 'convertir' : 'rechazar', 'codec' => null, 'aviso' => self::rechazo($ext)];
        }
        $codec = self::codecMp4($ruta);
        if ($codec !== null && in_array($codec, self::CODECS_OK, true)) {
            return ['accion' => 'dejar', 'codec' => $codec, 'aviso' => null];
        }
        if (self::ffmpeg()) {
            return ['accion' => 'convertir', 'codec' => $codec, 'aviso' => null];
        }
        if ($codec !== null && in_array($codec, self::CODECS_HEVC, true)) {
            return ['accion' => 'dejar', 'codec' => $codec,
                'aviso' => 'El video está en H.265 (iPhone «Alta eficiencia»): se ve en Safari, pero puede no verse en Chrome o Firefox. '
                    . 'Para que lo vean todos, grabe en «Más compatible» o páselo a MP4 con HandBrake.'];
        }
        return ['accion' => 'rechazar', 'codec' => $codec,
            'aviso' => 'Este video usa un formato interno (' . ($codec ?: 'desconocido') . ') que los navegadores no reproducen. '
                . 'Páselo a MP4 con HandBrake (gratis, handbrake.fr: preset «Fast 1080p30») y súbalo de nuevo.'];
    }

    /**
     * Códec de la pista de video de un MP4/MOV (avc1, hvc1, mp4v…) leyendo sus "cajas":
     * moov → trak (la que tiene hdlr = vide) → mdia → minf → stbl → stsd → primera entrada. null si no se puede leer.
     */
    public static function codecMp4(string $ruta): ?string
    {
        $f = @fopen($ruta, 'rb');
        if (!$f) {
            return null;
        }
        try {
            $tamano = filesize($ruta);
            $moov = self::buscarCaja($f, 0, $tamano, 'moov');
            if ($moov === null) {
                return null;
            }
            $pos = $moov[0];
            while (($trak = self::buscarCaja($f, $pos, $moov[1], 'trak')) !== null) {
                $mdia = self::buscarCaja($f, $trak[0], $trak[1], 'mdia');
                if ($mdia !== null && ($hdlr = self::buscarCaja($f, $mdia[0], $mdia[1], 'hdlr')) !== null) {
                    fseek($f, $hdlr[0] + 8); // versión+flags (4) y pre_defined (4)
                    if (fread($f, 4) === 'vide') {
                        $minf = self::buscarCaja($f, $mdia[0], $mdia[1], 'minf');
                        $stbl = $minf ? self::buscarCaja($f, $minf[0], $minf[1], 'stbl') : null;
                        $stsd = $stbl ? self::buscarCaja($f, $stbl[0], $stbl[1], 'stsd') : null;
                        if ($stsd) {
                            fseek($f, $stsd[0] + 8 + 4); // versión+flags, cantidad de entradas, tamaño de la entrada
                            $codec = fread($f, 4);
                            return preg_match('/^[A-Za-z0-9 ._-]{4}$/', (string) $codec) ? trim($codec) : null;
                        }
                    }
                }
                $pos = $trak[1];
            }
            return null;
        } finally {
            fclose($f);
        }
    }

    /**
     * Primera caja $tipo entre $desde y $hasta. Devuelve [inicio del contenido, fin de la caja] o null.
     * @param resource $f
     */
    private static function buscarCaja($f, int $desde, int $hasta, string $tipo): ?array
    {
        $pos = $desde;
        for ($n = 0; $pos + 8 <= $hasta && $n < 10000; $n++) {
            fseek($f, $pos);
            $cab = fread($f, 8);
            if (strlen($cab) < 8) {
                return null;
            }
            $largo = unpack('N', substr($cab, 0, 4))[1];
            $nombre = substr($cab, 4, 4);
            $cabecera = 8;
            if ($largo === 1) { // tamaño de 64 bits
                $grande = fread($f, 8);
                $partes = unpack('N2', $grande);
                $largo = $partes[1] * 4294967296 + $partes[2];
                $cabecera = 16;
            } elseif ($largo === 0) { // hasta el final
                $largo = $hasta - $pos;
            }
            if ($largo < $cabecera) {
                return null;
            }
            if ($nombre === $tipo) {
                return [$pos + $cabecera, min($pos + $largo, $hasta)];
            }
            $pos += $largo;
        }
        return null;
    }

    /**
     * Convierte a MP4 (H.264 + AAC, "faststart" para que empiece a verse antes de bajar todo).
     * Devuelve true si quedó $destino. Puede tardar: se amplía el tiempo de ejecución.
     */
    public static function convertir(string $origen, string $destino): bool
    {
        $ffmpeg = self::ffmpeg();
        if ($ffmpeg === null) {
            return false;
        }
        @set_time_limit(self::TIEMPO_MAXIMO);
        $ok = self::ejecutar([$ffmpeg, '-hide_banner', '-loglevel', 'error', '-y', '-i', $origen,
            '-map', '0:v:0', '-map', '0:a:0?',
            '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '23', '-pix_fmt', 'yuv420p',
            '-vf', 'scale=\'min(1920,iw)\':-2',
            '-c:a', 'aac', '-b:a', '128k', '-movflags', '+faststart', $destino], self::TIEMPO_MAXIMO);
        if (!$ok || !is_file($destino) || filesize($destino) === 0) {
            @unlink($destino);
            return false;
        }
        return true;
    }

    /** Foto del segundo 1 (o del primer cuadro si el video es más corto) en JPG de hasta 1280 px. */
    public static function miniatura(string $video, string $jpg): bool
    {
        $ffmpeg = self::ffmpeg();
        if ($ffmpeg === null) {
            return false;
        }
        foreach (['1', '0'] as $segundo) {
            $ok = self::ejecutar([$ffmpeg, '-hide_banner', '-loglevel', 'error', '-y', '-ss', $segundo, '-i', $video,
                '-frames:v', '1', '-vf', 'scale=\'min(1280,iw)\':-2', '-q:v', '3', $jpg], 60);
            if ($ok && is_file($jpg) && filesize($jpg) > 0) {
                return true;
            }
        }
        @unlink($jpg);
        return false;
    }

    /** Ejecuta un programa con sus argumentos escapados (sin pasar por el shell con texto del usuario). */
    private static function ejecutar(array $comando, int $segundos): bool
    {
        $linea = implode(' ', array_map('escapeshellarg', $comando));
        $p = @proc_open($linea, [0 => ['file', '/dev/null', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['pipe', 'w']], $tubos);
        if (!is_resource($p)) {
            return false;
        }
        stream_set_blocking($tubos[2], false);
        $fin = time() + $segundos;
        $error = '';
        do {
            $error .= (string) stream_get_contents($tubos[2]);
            $estado = proc_get_status($p);
            if (!$estado['running']) {
                break;
            }
            if (time() > $fin) {
                proc_terminate($p, 9);
                Yii::warning('ffmpeg tardó demasiado y se cortó.', __METHOD__);
                fclose($tubos[2]);
                proc_close($p);
                return false;
            }
            usleep(200000);
        } while (true);
        $error .= (string) stream_get_contents($tubos[2]);
        fclose($tubos[2]);
        proc_close($p);
        $codigo = $estado['exitcode'];
        if ($codigo !== 0) {
            Yii::warning('ffmpeg terminó con error ' . $codigo . ': ' . mb_substr($error, 0, 500), __METHOD__);
        }
        return $codigo === 0;
    }
}
