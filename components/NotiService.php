<?php
namespace app\components;

use app\models\Usuarios;
use Yii;

class NotiService implements InterfaceNoti
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

    public function agregarMensajeAdvertencia($mensaje)
    {
        Yii::$app->session->setFlash('warning', [
            'type' => 'error',
            'message' => $mensaje
        ]);


    }




}