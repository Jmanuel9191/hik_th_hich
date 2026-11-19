<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Módulo Ecosistemas del panel: editor de texto (TinyMCE), vista previa de imágenes,
 * botón Activar/Desactivar y recarga de la galería al terminar de subir.
 */
class EcosistemasAdminAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-ecosistemas.js',
        'js/huk-listado.js', // botón Destacado
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset',
        'app\assets_b\EditorAsset', // TinyMCE + huk-editor.js
    ];
}
