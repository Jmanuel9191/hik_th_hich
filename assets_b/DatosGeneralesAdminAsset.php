<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Datos generales: vista previa del mapa de Google mientras se pega el HTML.
 * Lo registra modules/admin/views/datos-generales/index.php.
 */
class DatosGeneralesAdminAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-datos-generales.js',
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset',
    ];
}
