<?php

namespace app\controllers;
use app\components\InterfaceAdmin;
use app\components\InterfaceCuenta;
use app\components\InterfaceLib;
use app\components\InterfacePermisosService;
use app\models\ChangePasswordForm;
use app\models\Forget;
use app\models\Roles;
use app\models\Usuarios;
use app\models\Usuariosperfil;
use app\models\UsuariosSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class AdministradorController extends Controller
{

    private $adminService;
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



    public function __construct($id, $module, InterfaceAdmin $adminService, InterfaceLib $libService, InterfacePermisosService $permisosService, InterfaceCuenta $cuentaService,$config = [])
    {
        $this->adminService = $adminService;
        $this->cuentaService = $cuentaService;
        $this->libService = $libService;
        $this->permisosService = $permisosService;
        parent::__construct($id, $module, $config);
    }

    public function actionIndex()
    {
        return $this->redirect(['administrador/cuenta']);
    }
    public function actionCuenta()
    {
        $model = $this->permisosService->verificarAdministrador();
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

        $model = $this->permisosService->verificarAdministrador();
        if (Yii::$app->request->isPost) {
            $resultado = $this->cuentaService->actualizarUsuario($model, Yii::$app->request->post());
            if ($resultado && $resultado['exito']) {
                return $this->redirect(['cuenta']);
            }

            $modelFinal = $resultado['model'] ?? $model;

            return $this->render('cuenta', [
                'model' => $modelFinal,
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
        $model = $this->permisosService->verificarAdministrador();
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




    public function actionUsuarioslist()
    {
        $this->permisosService->verificarAdministrador();
        $this->permisosService->verificarPermiso('usuarios_listar');
        $resultadoBusqueda = $this->adminService->listUsuarios(Yii::$app->request->queryParams);

        return $this->render('cuenta', [

            'searchModel' => $resultadoBusqueda['searchModel'],
            'dataProvider' => $resultadoBusqueda['dataProvider'],
            'render' => 'listau',
        ]);

    }
    public function actionCreate()
    {
        $this->permisosService->verificarAdministrador();
        $this->permisosService->verificarPermiso('usuarios_crear');
        $resultado = $this->adminService->nuevoUsuario(Yii::$app->request->post());
        $roles = $this->adminService->obtenerRolesActivos();
        if ($resultado && $resultado['exito']) {
            return $this->redirect(['usuarioslist']);      }

        $this->cuentaService->limpiarFlashes();
        return $this->render('cuenta', [
            'model' => $resultado['model'] ?? new Usuarios(),
            'roles' => $roles,
            'render' => 'createusuario',

        ]);
    }

    public function actionView($id)
    {
        $this->permisosService->verificarAdministrador();
        $this->permisosService->verificarPermiso('usuarios_ver');

        $model = $this->adminService->obtenerUsuario($id);
        return $this->render('cuenta', [
            'model' => $model,
            'render' => 'verusuario',

        ]);
    }

    public function actionUpdate($id)
    {
        $this->permisosService->verificarAdministrador();
        $this->permisosService->verificarPermiso('usuarios_editar');
        $model = $this->adminService->findModel($id);
        $roles = $this->adminService->obtenerRolesActivos();
        $resultado = $this->adminService->actualizarUsuario(Yii::$app->request->post(), $id);
        if ($resultado && $resultado['exito']) {
            return $this->redirect(['view', 'id' => $id]);     }

        $this->cuentaService->limpiarFlashes();
        return $this->render('cuenta', [
            'model' => $resultado['model'], // importante: devolver el modelo con errores
            'roles' => $roles,
            'render' => 'updateusuario',
        ]);
    }


    public function actionEstado($id)
    {
        $this->permisosService->verificarAdministrador();
        $this->permisosService->verificarPermiso('usuarios_estado');
        $motivo = Yii::$app->request->post('motivo');
        $resultado = $this->adminService->cambiarEstadoUsuario($id, $motivo);
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'exito' => $resultado['exito'],
            'mensaje' => $resultado['mensaje'],
        ];
    }

    public function actionCambiarInactividad($id)
    {
        $this->permisosService->verificarAdministrador();
        $this->permisosService->verificarPermiso('usuarios_inactividad');
        $motivo = Yii::$app->request->post('motivo');
        $resultado = $this->adminService->cambiarEstadoInactividad($id, $motivo);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'exito' => $resultado['exito'],
            'mensaje' => $resultado['mensaje'],
        ];
    }


    public function actionDesbloquearIntentos($id)
    {
        $this->permisosService->verificarAdministrador();
        $this->permisosService->verificarPermiso('usuarios_resetear_intentos');
        $resultado = $this->cuentaService->desbloquearintentosFallidos($id);
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'exito' => $resultado['exito'],
            'mensaje' => $resultado['mensaje'],
        ];
    }

    public function actionHistorial($id)
    {
        $this->permisosService->verificarAdministrador();
        $this->permisosService->verificarPermiso('usuarios_historial');
        $model = $this->adminService->obtenerUsuario($id);
        $resultadoBusqueda = $this->adminService->listHistorial(
            Yii::$app->request->queryParams,
            $id // <-- Aquí se envía el ID de la tarea
        );
        return $this->render('cuenta', [
            'searchModel' => $resultadoBusqueda['searchModel'],
            'dataProvider' => $resultadoBusqueda['dataProvider'],
            'model' => $model,
            'render' => 'historial',
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
