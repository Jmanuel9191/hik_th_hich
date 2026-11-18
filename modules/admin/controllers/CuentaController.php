<?php

namespace app\modules\admin\controllers;
use app\components\InterfaceAuth;
use app\components\InterfaceCuenta;
use app\models\CambioPasswordObligatorioForm;
use app\models\ChangePasswordForm;
use app\models\Configuracion;
use app\models\Forget;
use app\models\Usuarios;
use app\models\Usuariosperfil;
use app\models\UsuariosSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\helpers\Url;
class CuentaController extends Controller
{

    private $cuentaService;
    private $authService;
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],

        ];
    }



    public function __construct($id, $module, InterfaceCuenta $cuentaService, InterfaceAuth $authService, $config = [])
    {
        $this->cuentaService = $cuentaService;
        $this->authService = $authService;
        parent::__construct($id, $module, $config);
    }


    public function actionCuenta()
    {
        $model = $this->cuentaService->obtenerUsuarioSesion();
        if ($model === null) {
            return $this->redirect(['login']);
        }
        return $this->render('perfil', [
            'model' => $model,
            'render' => 'perfil',
        ]);
    }


    public function actionUpdateperfil()
    {
        $model = $this->cuentaService->obtenerUsuarioSesion();
        if ($model === null) {
            return $this->redirect(['login']);
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($this->cuentaService->actualizarUsuario($model)) {
                Yii::$app->session->setFlash('mensaje', ['message' => 'Tu perfil ha sido actualizado correctamente.', 'type' => 'success']);
                return $this->redirect(['cuenta']);
            }
        }

        return $this->render('cuenta', [
            'model' => $model,
            'render' => 'updateperfil',
        ]);
    }


    public function actionForget()
    {
        $this->layout = "login";
        $model = new Forget();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $resultadoRecuperacion = $this->cuentaService->recuperacionCuenta($model->email);
            Yii::$app->session->setFlash('success', ['message' => $resultadoRecuperacion['mensaje'], 'type' => 'success']);
            return $this->refresh();
        }

        return $this->render('forget', [
            'model' => $model,
        ]);
    }

    /**
     * Abre el enlace que llegó por correo y deja crear una contraseña nueva.
     * Estados de la vista: 'form' (pide la clave), 'listo' (ya cambió), 'invalido' (enlace vencido o usado).
     */
    public function actionRestablecer($token = null)
    {
        $this->layout = "login";

        $usuario = $this->cuentaService->buscarUsuarioPorTokenRecuperacion($token);
        if ($usuario === null) {
            return $this->render('restablecer', ['estado' => 'invalido', 'model' => null]);
        }

        $model = new CambioPasswordObligatorioForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $this->cuentaService->restablecerContrasena($usuario, $model->nueva_contrasena);
            return $this->render('restablecer', ['estado' => 'listo', 'model' => null]);
        }

        $model->nueva_contrasena = $model->confirmar_contrasena = null; // nunca devolver la clave al HTML
        return $this->render('restablecer', ['estado' => 'form', 'model' => $model]);
    }




    public function actionUpdatepasswordperfil()
    {
        $model = $this->cuentaService->obtenerUsuarioSesion();
        if ($model === null) {
            return $this->redirect(['login']);
        }

        $changePasswordForm = new ChangePasswordForm();
        if ($changePasswordForm->load(Yii::$app->request->post()) && $changePasswordForm->validate()) {
            if ($this->cuentaService->cambiarPassword($model, $changePasswordForm->newPassword)) {
                Yii::$app->session->setFlash('mensaje', ['message' => 'Tu contraseña ha sido actualizada correctamente1222.', 'type' => 'success']);
                return $this->redirect(['cuenta']);
            }
        }

        return $this->render('cuenta', [
            'model' => $changePasswordForm,
            'render' => 'updatepasswordperfil',
        ]);
    }

}
