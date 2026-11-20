<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Blog en el panel: listado (huk-listado.js: estado, destacada, orden, eliminar), editor de la descripción
 * y huk-noticias.js (vista previa de la dirección y contador del resumen).
 * Lo registran modules/admin/views/noticias/*.php y noticias-categorias/*.php.
 */
class NoticiasAdminAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-listado.js',
        'js/huk-noticias.js',
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset',
        'app\assets_b\EditorAsset',
    ];
}
