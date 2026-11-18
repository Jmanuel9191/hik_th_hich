<?php

namespace app\controllers;
use app\components\AuthService;
use app\models\LoginWeb;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\components\InterfaceComponents;

class BaseController extends Controller
{
   public function beforeAction($action)
{
    $status = Yii::$app->request->get('status');
    $mensajes = [
        'created' => ['type' => 'success', 'message' => 'Registro creado correctamente.'],
        'updated' => ['type' => 'success', 'message' => 'Registro actualizado correctamente.'],
        'deleted' => ['type' => 'success', 'message' => 'Registro eliminado correctamente.'],
    ];
    if ($status && isset($mensajes[$status])) {
        Yii::$app->session->setFlash('flash', $mensajes[$status]);
    }
    return parent::beforeAction($action);
}

}



