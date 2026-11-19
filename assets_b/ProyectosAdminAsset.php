<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Banco de proyectos: formulario según el tipo, botones de estado, subida de videos y documentos con barra
 * de progreso (huk-proyectos.js), orden/eliminar de videos y documentos (huk-listado.js) y editor de texto.
 * Lo registran modules/admin/views/banco-proyectos/*.php.
 */
class ProyectosAdminAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-listado.js',
        'js/huk-proyectos.js',
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset',
        'app\assets_b\EditorAsset',
    ];
}
