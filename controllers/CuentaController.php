<?php

namespace app\controllers;

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
            return $this->redirect(['site/login']);
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
            return $this->redirect(['site/login']);
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
        if ($model->load(Yii::$app->request->post())) {
            $resultadoRecuperacion = $this->cuentaService->recuperacionCuenta($model->email);
            if ($resultadoRecuperacion['exito']) {
                Yii::$app->session->setFlash('success', ['message' => $resultadoRecuperacion['mensaje'], 'type' => 'success']);
            } else {
                Yii::$app->session->setFlash('error', ['message' => $resultadoRecuperacion['mensaje']]);

            }
            return $this->refresh();

        }

        return $this->render('forget', [
            'model' => $model,
        ]);
    }




    public function actionUpdatepasswordperfil()
    {
        $model = $this->cuentaService->obtenerUsuarioSesion();
        if ($model === null) {
            return $this->redirect(['site/login']);
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


    public function actionCambiarClaveObligatoria()
    {

        $usuario = Yii::$app->user->identity;
        $this->cuentaService->verificarUsuarioLogueado();

        if ($usuario->requiere_cambio_contrasena == 0) {
            return $this->redirect(['site/index']); // o redirigir según el rol del usuario
        }

        $formModel = new CambioPasswordObligatorioForm();

        if (Yii::$app->request->isAjax && $formModel->load(Yii::$app->request->post()) && $formModel->validate()) {
            $resultado = $this->cuentaService->cambioPasswordObligatorio($usuario, $formModel->nueva_contrasena);

            if ($resultado['exito']) {
                $ruta = $this->authService->getRedirectRoute($usuario);

                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return [
                    'success' => true,
                    'message' => 'Tu contraseña fue actualizada correctamente.',
                    'redirect' => Url::to($ruta), // Convertí la ruta a URL
                ];
            }
        }
        return $this->render('cambiar-clave-obligatoria', [
            'model' => $formModel,
        ]);
    }




}
