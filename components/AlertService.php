<?php
namespace app\components;

use app\models\Usuarios;
use Yii;

class AlertService implements InterfaceAlert
{

    public function agregarMensajeExito($mensaje = 'success')
    {
        Yii::$app->session->setFlash('flash', [
            'type' => 'success',
            'message' => $mensaje
        ]);
    }
    public function agregarMensajeError($mensaje = 'error')
    {

        Yii::$app->session->setFlash('flash', [
            'type' => 'error',
            'message' => $mensaje
        ]);


    }

}