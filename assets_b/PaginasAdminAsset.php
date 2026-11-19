<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Páginas institucionales: orden, estado y eliminar de las listas (huk-listado.js)
 * + editor de los textos con formato. Lo registran modules/admin/views/paginas/*.php.
 */
class PaginasAdminAsset extends AssetBundle
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
