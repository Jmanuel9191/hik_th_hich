<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Configuración → Monedas: activar/desactivar y eliminar (huk-listado.js).
 */
class MonedasAdminAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-listado.js',
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset',
    ];
}
