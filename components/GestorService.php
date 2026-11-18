<?php
namespace app\components;
use app\models\Tareas;
use app\models\TareasSearch;
use app\models\Usuarios;
use app\models\UsuariosSearch;
use Yii;
use yii\web\NotFoundHttpException;

class GestorService implements InterfaceGestor
{
    private $correoService;
    private $alertService;

    public function __construct(InterfaceCorreos $correoService, InterfaceAlert $alertService)
    {
        $this->correoService = $correoService;
        $this->alertService = $alertService;
    }

    public function obtenerUsuarioSesion()
    {
        $usuario = Yii::$app->user->identity;

        if ($usuario !== null && $usuario->idrol == '2') {
            return $usuario;
        }
        return null;
    }
    public function obtenerUsuario($id)
    {
        return $this->findModel($id);
    }

    public function verificarAccesoGestor()
    {
        $usuario = $this->obtenerUsuarioSesion();
        if ($usuario === null) {
            Yii::$app->response->redirect(['site/login'])->send();
            Yii::$app->end();
        }
        return $usuario;
    }

    public function findModel($id)
    {
        if (($model = Usuarios::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('La página solicitada no existe.');
        }
    }

}