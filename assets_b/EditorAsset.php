<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Editor de texto con formato (TinyMCE local + huk-editor.js).
 * Lo usan Ecosistemas y Equipo: <textarea data-editor data-tinymce-base="...">.
 */
class EditorAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-editor.js',
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset',
        'app\assets_b\TinyMceAsset',
    ];
}
