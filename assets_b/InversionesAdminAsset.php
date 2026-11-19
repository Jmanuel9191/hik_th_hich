<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Inversiones (panel): formulario de pago y comprobantes (huk-inversiones.js); anular con motivo (huk-inversores.js).
 */
class InversionesAdminAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-inversores.js',
        'js/huk-inversiones.js',
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset',
    ];
}
