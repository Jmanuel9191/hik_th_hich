<?php

namespace app\components;

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * URLs de las fotos y documentos de la página de un proyecto. Las mismas vistas sirven para:
 *  - la web (el inversor): /proyectos/imagen/…, /proyectos/video/… y /proyectos/documento/…  (ProyectosController, revisa su acceso),
 *  - la vista previa y el editor del panel: /admin/paginas-proyecto/imagen… (revisa el permiso del panel).
 * Así las vistas no saben desde dónde se las mira.
 */
final class MediosProyecto
{
    /** Anchos del srcset (ImagenVariante::ANCHOS). */
    const SRCSET = [480, 960, 1440, 1920];

    private function __construct(private string $modo, private string $tipo, private int $id)
    {
    }

    public static function web(int $idproyecto): self
    {
        return new self('web', 'proyecto', $idproyecto);
    }

    public static function panel(string $tipo, int $id): self
    {
        return new self('panel', $tipo, $id);
    }

    public function esPanel(): bool
    {
        return $this->modo === 'panel';
    }

    public function esPlantilla(): bool
    {
        return $this->tipo === 'plantilla';
    }

    /** URL de una foto. $carpeta: imagenes (banner y principal) | bloques | galeria. */
    public function imagen(string $carpeta, string $archivo, int $ancho = 960): string
    {
        $ancho = ImagenVariante::ancho($ancho);
        if ($this->modo === 'web') {
            return Url::to(['/proyectos/imagen', 'id' => $this->id, 'carpeta' => $carpeta, 'archivo' => $archivo, 'w' => $ancho]);
        }
        return Url::to(['/admin/paginas-proyecto/imagen', 'tipo' => $this->tipo, 'id' => $this->id, 'carpeta' => $carpeta, 'archivo' => $archivo, 'w' => $ancho]);
    }

    /**
     * <img> con srcset (el navegador elige el tamaño justo) y carga diferida.
     * $sizes: cuánto ocupa en pantalla, ej. "(max-width: 991px) 100vw, 50vw".
     */
    public function img(string $carpeta, string $archivo, string $alt, string $sizes = '100vw', array $opciones = []): string
    {
        $srcset = implode(', ', array_map(fn($w) => $this->imagen($carpeta, $archivo, $w) . ' ' . $w . 'w', self::SRCSET));
        return Html::img($this->imagen($carpeta, $archivo, 960), $opciones + [
            'srcset' => $srcset,
            'sizes' => $sizes,
            'alt' => $alt,
            'loading' => 'lazy',
            'decoding' => 'async',
        ]);
    }

    /** Video subido del proyecto (con "Range": se puede adelantar sin bajarlo entero). */
    public function video(int $idvideo): string
    {
        return $this->modo === 'web'
            ? Url::to(['/proyectos/video', 'id' => $idvideo])
            : Url::to(['/admin/banco-proyectos/video', 'id' => $idvideo]);
    }

    /** Descarga de un documento del proyecto (registra quién lo bajó). */
    public function documento(int $iddocumento): string
    {
        return $this->modo === 'web'
            ? Url::to(['/proyectos/documento', 'id' => $iddocumento])
            : Url::to(['/admin/banco-proyectos/documento', 'id' => $iddocumento]);
    }
}
