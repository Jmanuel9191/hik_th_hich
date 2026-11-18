<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Campo de una imagen (app\widgets\CampoImagen): botones Elegir / Quitar.
 * Lo registra el propio widget.
 */
class CampoImagenAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-imagen.js',
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset',
        'kartik\file\FileInputAsset',
    ];
}
