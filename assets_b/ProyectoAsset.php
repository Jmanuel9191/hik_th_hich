<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Página de cada proyecto (/proyectos/<slug> y la vista previa del panel): estilos de las secciones y bloques
 * (huk-proyecto.css) y el menú de anclas (huk-proyecto.js). Solo se carga en esa página, así el resto del
 * sitio no baja nada de más. Lo registra views/proyectos/ver.php.
 */
class ProyectoAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $css = [
        'css/huk-proyecto.css',
    ];

    public $js = [
        'js/huk-proyecto.js',
        'js/huk-invertir.js',
    ];

    public $depends = [
        AppAsset::class, // después de huk.css y huk.js
    ];
}
