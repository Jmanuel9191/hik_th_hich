<?php

declare(strict_types=1);

namespace app\assets_b;

use yii\web\AssetBundle;

/**
 * Módulo Usuarios del panel: botones Bloquear/Activar (con motivo) y Desbloquear intentos.
 * Lo registran modules/admin/views/usuarios/index.php y verusuario.php.
 */
class UsuariosAdminAsset extends AssetBundle
{
    public $basePath = '@webroot/assets_b';
    public $baseUrl = '@web/assets_b';

    public $js = [
        'js/huk-usuarios.js',
    ];

    public $depends = [
        'app\assets_b\AdminPanelAsset', // jQuery + SweetAlert2
    ];
}
