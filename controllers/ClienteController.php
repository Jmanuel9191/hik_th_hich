<?php

namespace app\controllers;

use app\components\InterfaceAdmin;
use app\components\InterfaceCliente;
use app\components\InterfaceConsultor;
use app\components\InterfaceCuenta;
use app\components\InterfaceGestor;
use app\components\InterfaceLib;
use app\components\InterfaceNoti;
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

class ClienteController extends Controller
{

    private $clienteService;
    private $cuentaService;
    private $libService;
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



    public function __construct($id, $module, InterfaceCliente $clienteService, InterfaceLib $libService,  InterfaceCuenta $cuentaService,$config = [])
    {
        $this->clienteService = $clienteService;
        $this->cuentaService = $cuentaService;
        $this->libService = $libService;
        parent::__construct($id, $module, $config);
    }

    public function actionIndex()
    {
        return $this->redirect(['cliente/cuenta']);
    }
    public function actionCuenta()
    {
        $model = $this->clienteService->verificarAccesoCliente();
        return $this->render('cuenta', [
            'model' => $model,
            'render' => 'perfil',
        ]);
    }





    public function actionUpdateperfil()
    {
        $model = $this->clienteService->verificarAccesoCliente();

        if (Yii::$app->request->isPost) {
            $resultado = $this->cuentaService->actualizarUsuario($model, Yii::$app->request->post());

            if ($resultado && $resultado['exito']) {
                return $this->redirect(['cuenta']);
            }

            return $this->render('cuenta', [
                'model' => $model,
                'render' => 'updateperfil',
            ]);
        }

        // Primera vez que se carga el formulario
        return $this->render('cuenta', [
            'model' => $model,
            'render' => 'updateperfil',
        ]);
    }


    public function actionUpdatepasswordperfil()
    {
        $model = $this->clienteService->verificarAccesoCliente();
        $resultado = $this->cuentaService->procesarFormularioCambioPassword($model, Yii::$app->request->post());

        if ($resultado && $resultado['exito']) {
            return $this->redirect(['cuenta']);
        }

        return $this->render('cuenta', [
            'model' => $resultado['formModel'] ?? new ChangePasswordForm(),
            'render' => 'updatepasswordperfil',
        ]);
    }
    public function actionGetPaises()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $paises = $this->libService->obtenerPaises();
        // Verifica si vino con error
        if (isset($paises['error'])) {
            return ['success' => false, 'message' => $paises['error']];
        }
        return ['success' => true, 'data' => $paises];
    }

    public function actionGetZonaHoraria($ciudad, $pais = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $this->libService->obtenerZonaHoraria($ciudad, $pais);
    }




}
