<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Cuenta del inversor en la web (/cuenta): formularios de registro e ingreso, panel y acuerdo de confidencialidad.
 */
class CuentaAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $css = [
        'css/huk-cuenta.css',
    ];

    public $js = [
        'js/huk-solicitud.js',
    ];

    public $depends = [
        'app\assets_b\AppAsset',
    ];
}
