<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Inversores y Acuerdo de confidencialidad (panel): formulario persona/empresa (huk-solicitud.js),
 * rechazar / bloquear / reenviar (huk-inversores.js) y editor de texto del NDA.
 */
class InversoresAdminAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-solicitud.js',
        'js/huk-inversores.js',
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset',
        'app\assets_b\EditorAsset',
    ];
}
