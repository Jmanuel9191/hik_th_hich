<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Banners: orden (↑ ↓), activar/desactivar y eliminar con confirmación (huk-listado.js)
 * y campos del formulario según la página elegida (huk-banners.js).
 * Lo registran modules/admin/views/banners/*.php.
 */
class BannersAdminAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-listado.js',
        'js/huk-banners.js',
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset', // jQuery + SweetAlert2
    ];
}
