<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Tema HUK del panel admin.
 * Carga AdminLTE (AssetAdmin) y después lo nuestro:
 *   css/huk-admin.css            estilos (reemplaza a portaladmin.css)
 *   lib/sweetalert2/...          alertas (local, ya no desde un CDN)
 *   js/huk-admin.js              ojo de contraseña + mensajes flash
 * Se usa en el login (layouts/login.php). El panel usa AdminPanelAsset, que la incluye.
 */
class AdminHukAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $css = [
        'css/huk-admin.css',
    ];

    public $js = [
        'lib/sweetalert2/sweetalert2.all.min.js',
        'js/huk-admin.js',
    ];

    public $depends = [
        'app\assets_b\AssetAdmin', // AdminLTE 2 + Bootstrap 3 + Font Awesome 4 + jQuery
    ];
}
