<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Panel admin ya logueado: todo lo de AdminHukAsset + tu jsnew.js
 * (antes iba con <script src> dentro del layout).
 * Se usa en modules/admin/views/layouts/main.php.
 */
class AdminPanelAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/jsnew.js',
    ];

    public $depends = [
        'app\assets_b\AdminHukAsset',
    ];
}
