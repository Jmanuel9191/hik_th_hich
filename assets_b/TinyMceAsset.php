<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * TinyMCE 7 local (sin CDN). Licencia GPL: se usa con license_key 'gpl' (ver huk-ecosistemas.js).
 * Solo trae los plugins que usamos: advlist, lists, link, table, code, fullscreen, wordcount.
 */
class TinyMceAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b/lib/tinymce';
    public $baseUrl = '@web/assets_b/lib/tinymce';

    public $js = [
        'tinymce.min.js',
    ];
}
