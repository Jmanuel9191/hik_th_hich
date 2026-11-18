<?php

namespace app\controllers;

use app\components\InterfaceAdmin;
use app\components\InterfaceConsultor;
use app\components\InterfaceCuenta;
use app\components\InterfaceGestor;
use app\components\InterfaceLib;
use app\components\InterfaceNoti;
use app\components\InterfacePermisosService;
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

class ConsultorController extends Controller
{

    private $consultorService;
    private $cuentaService;
    private $libService;
    private $permisosService;
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



    public function __construct($id, $module, InterfaceConsultor $consultorService, InterfacePermisosService $permisosService, InterfaceLib $libService,  InterfaceCuenta $cuentaService,$config = [])
    {
        $this->consultorService = $consultorService;
        $this->cuentaService = $cuentaService;
        $this->libService = $libService;
        $this->permisosService = $permisosService;
        parent::__construct($id, $module, $config);
    }

    public function actionIndex()
    {
        return $this->redirect(['consultor/cuenta']);
    }
    public function actionCuenta()
    {
        $model = $this->permisosService->verificarConsultor();
        return $this->render('cuenta', [
            'model' => $model,
            'render' => 'perfil',
        ]);
    }


    public function actionUpdateperfil()
    {
        $model = $this->permisosService->verificarConsultor();
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

        $model = $this->permisosService->verificarConsultor();
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
