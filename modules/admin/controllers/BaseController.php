<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;

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