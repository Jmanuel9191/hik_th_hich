<?php

namespace app\controllers;

use app\components\InterfaceAdmin;
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

class GestorController extends Controller
{

    private $cuentaService;
    private $gestorService;
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



    public function __construct($id, $module, InterfaceGestor $gestorService, InterfaceLib $libService,  InterfaceCuenta $cuentaService,$config = [])
    {
        $this->gestorService = $gestorService;
        $this->cuentaService = $cuentaService;
        $this->libService = $libService;
        parent::__construct($id, $module, $config);
    }

    public function actionIndex()
    {
        return $this->redirect(['gestor/cuenta']);
    }

    public function actionCuenta()
    {
        $model = $this->gestorService->verificarAccesoGestor();
        if (Yii::$app->request->get('flashclear')) {
            Yii::$app->session->removeFlash('flash');
        }
        return $this->render('cuenta', [
            'model' => $model,
            'render' => 'perfil',
        ]);
    }


    public function actionUpdateperfil()
    {
        $model = $this->gestorService->verificarAccesoGestor();
        if (Yii::$app->request->isPost) {
            $resultado = $this->cuentaService->actualizarUsuario($model, Yii::$app->request->post());
            if ($resultado && $resultado['exito']) {
                return $this->redirect(['cuenta']);

            }
            return $this->render('cuenta', [
                'model' => $resultado['model'],
                'render' => 'updateperfil',
            ]);
        }
        // Primera vez que se carga el formulario
        $this->cuentaService->limpiarFlashes();
        return $this->render('cuenta', [
            'model' => $model,
            'render' => 'updateperfil',
        ]);

    }

    public function actionUpdatepasswordperfil()
    {
        $model = $this->gestorService->verificarAccesoGestor();
        $resultado = $this->cuentaService->procesarFormularioCambioPassword($model, Yii::$app->request->post());


        if ($resultado && $resultado['exito']) {
            return $this->redirect(['cuenta']);
        }
        $this->cuentaService->limpiarFlashes();
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
