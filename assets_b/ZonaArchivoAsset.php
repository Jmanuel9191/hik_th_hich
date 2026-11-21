<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Zona para subir archivos (app\widgets\ZonaArchivo). La registra el propio widget. Estilos: huk-admin.css.
 */
class ZonaArchivoAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-zona.js',
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset',
    ];
}
