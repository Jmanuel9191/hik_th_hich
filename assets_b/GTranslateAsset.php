<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Traductor GTranslate (motor oculto del selector de idiomas con banderas).
 * Se registra en todo el sitio, también en /cuenta (ver views/layouts/main.php). Lo marcado con translate="no" no se traduce.
 * Orden: primero la configuración, después el script de GTranslate.
 */
class GTranslateAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/gtranslate-config.js',
        ['https://cdn.gtranslate.net/widgets/latest/dropdown.js', 'defer' => true],
    ];

    public $jsOptions = ['position' => View::POS_END];
}
