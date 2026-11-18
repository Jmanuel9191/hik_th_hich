<?php

namespace app\modules\admin;

use Yii;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\admin\controllers';
    public $layout = 'main';

    /**
     * Módulos del panel que están en uso. Cualquier otro controlador del admin
     * (los del sistema anterior: órdenes, doctores, inventario, etc.) responde 404.
     * Para volver a activar uno, agregalo acá.
     */
    const CONTROLADORES_ACTIVOS = ['default', 'cuenta', 'usuarios', 'ecosistemas', 'roles', 'datos-generales', 'banners', 'equipo'];
    //public $basePath = '@webroot/admin/';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
        Yii::$app->set('user', [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\Usuarios',
            'loginUrl' => ['admin/default/login'],
            //'identityCookie' => ['name' => 'admin_mym', 'httpOnly' => true],
            'idParam' => 'admin_id', //this is important !
        ]);
    }

    public function beforeAction($action)
    {
        if (!in_array($action->controller->id, self::CONTROLADORES_ACTIVOS, true)) {
            throw new \yii\web\NotFoundHttpException('Página no encontrada.');
        }
        return parent::beforeAction($action);
    }
}
