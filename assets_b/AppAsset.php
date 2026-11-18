<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $css = [
        'css/bootstrap.min.css',          // Bootstrap 5.3.9
        'fontawesome/css/all.min.css',    // Font Awesome 7
        'lib/owlcarousel/assets/owl.carousel.min.css',
        'css/style.css',                  // Electra
        'css/huk.css',                    // marca HUK, siempre al final
    ];

    public $js = [
        'js/bootstrap.bundle.min.js',
        'lib/owlcarousel/owl.carousel.min.js',
        'js/main.js',
        'js/huk.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',               // jQuery + yii.js
    ];
}