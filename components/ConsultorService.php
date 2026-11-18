<?php
namespace app\components;
use app\models\Tareas;
use app\models\TareasSearch;
use app\models\Usuarios;
use app\models\UsuariosSearch;
use Yii;
use yii\web\NotFoundHttpException;

class ConsultorService implements InterfaceConsultor
{
    private $correoService;
    private $alertService;

    public function __construct(InterfaceCorreos $correoService, InterfaceAlert $alertService)
    {
        $this->correoService = $correoService;
        $this->alertService = $alertService;
    }

    public function obtenerUsuarioSesion() /**/
    {
        $user = Yii::$app->user->identity;
        if (empty($user)) {
            return null;
        }
        $usuario = Usuarios::findOne(['idusuario' => $user['id']]);
        // Verificar si el usuario existe y si su tipo de usuario es '2'
        if ($usuario !== null && $usuario->idrol == '3') {
            return $usuario;
        }
        return null;
    }
    public function obtenerUsuario($id)
    {
        return $this->findModel($id);
    }

    public function verificarAccesoConsultor()
    {
        $usuario = $this->obtenerUsuarioSesion();
        if ($usuario === null) {
            Yii::$app->session->setFlash('error', 'No tienes permiso para acceder a esta sección.');
            Yii::$app->controller->redirect(Yii::$app->request->referrer ?: ['site/login']);
            Yii::$app->end(); // Termina la ejecución del script, ya que la redirección se realizará aquí
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