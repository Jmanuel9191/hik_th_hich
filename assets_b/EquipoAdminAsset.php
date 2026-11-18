<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Equipo: orden (↑ ↓), activar/desactivar y eliminar (huk-listado.js) + editor de la biografía.
 * Lo registran modules/admin/views/equipo/*.php.
 */
class EquipoAdminAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-listado.js',
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset',
        'app\assets_b\EditorAsset',
    ];
}
