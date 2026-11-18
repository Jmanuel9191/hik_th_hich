<?php

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Select2 (combos con búsqueda) para el presupuesto.
 * Publica los archivos desde vendor, así funciona aunque el .htaccess bloquee /vendor.
 */
class Select2Asset extends AssetBundle
{
    public $sourcePath = '@vendor/select2/select2/dist';
    public $css = [
        'css/select2.min.css',
    ];
    public $js = [
        'js/select2.full.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
