<?php

namespace app\modules\cuenta;

use Yii;

/**
 * Cuenta del inversor en la web (/cuenta): registro, confirmación de correo, ingreso, recuperar contraseña,
 * estado de la solicitud y acuerdo de confidencialidad. Usa el layout público (sin traductor: ver layouts/main.php).
 * Sesión propia (como el panel admin): solo entran usuarios con rol Inversor.
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\cuenta\controllers';
    public $layout = '@app/views/layouts/main';

    /** Configuración de la sesión del inversor (la misma en este módulo y en el header de la web). */
    const SESION = [
        'class' => 'yii\web\User',
        'identityClass' => 'app\models\Usuarios',
        'enableAutoLogin' => false,
        'loginUrl' => ['/cuenta/acceso/ingresar'],
        'idParam' => '__id_inversor',
        'authKeyParam' => '__authKey_inversor',
        'returnUrlParam' => '__returnUrl_inversor',
        'authTimeout' => 7200,
        'authTimeoutParam' => '__expire_inversor',
    ];

    public function init()
    {
        parent::init();
        Yii::$app->set('user', self::SESION);
    }

    /** Sesión del inversor desde cualquier página de la web (ej. el botón "Mi cuenta" del header). */
    public static function sesion(): \yii\web\User
    {
        static $u = null;
        if (Yii::$app->controller && Yii::$app->controller->module instanceof self) {
            return Yii::$app->user;
        }
        return $u ??= Yii::createObject(self::SESION);
    }
}
