<?php

namespace app\modules\admin\controllers;
use app\modules\admin\controllers\BaseController;
use app\components\InterfacePermisosService;
use app\models\Usuarios;
use app\components\InterfaceAdmin;
use app\components\InterfaceCuenta;
use app\models\UsuariosSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\HistorialUsuarios;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\ChangePasswordForm;
/**
 * UsuariosController implements the CRUD actions for Usuarios model.
 */
class UsuariosController extends Controller 
{ 
     private $permisosService;
    private $adminService;
    private $cuentaService;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'estado' => ['POST'],
                    'desbloquear-intentos' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                //'only'  => ['*'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

     public function __construct($id, $module,  InterfaceAdmin $adminService,  InterfacePermisosService $permisosService, InterfaceCuenta $cuentaService,  $config = [])
    {
        $this->permisosService = $permisosService;
        $this->adminService = $adminService;
           $this->cuentaService = $cuentaService;
        parent::__construct($id, $module, $config);
    }


    /**
     * Lists all Usuarios models.
     * @return mixed
     */
    public function actionIndex()
    {
        $this->permisosService->verificarAdministrador();
        $this->permisosService->verificarPermiso('usuarios_listar');
        $lista = $this->adminService->listUsuarios(Yii::$app->request->queryParams); // pestañas Activos / Bloqueados
        $this->cuentaService->limpiarFlashes();
        return $this->render('index', $lista + [
            'puedeExportar' => $this->permisosService->tienePermiso('usuarios_exportar'),
        ]);
    }

    /** Exporta el listado (con los filtros que tenga) a xlsx, csv o pdf. */
    public function actionExportar($formato = 'xlsx')
    {
        $this->permisosService->verificarAdministrador();
        $this->permisosService->verificarPermiso('usuarios_exportar');
        if (!isset(\app\components\Exportador::FORMATOS[$formato])) {
            throw new \yii\web\BadRequestHttpException('Formato no válido.');
        }
        $d = $this->adminService->exportarUsuarios(Yii::$app->request->queryParams);
        Yii::info('Exportación de usuarios (' . $formato . ', ' . count($d['modelos']) . ' registros) por #' . Yii::$app->user->id, 'exportar');
        return \app\components\Exportador::enviar($d['titulo'], $d['columnas'], $d['modelos'], $formato, $d['filtros']);
    }

   

    /**
     * Displays a single Usuarios model.
     * @param integer $id
     * @return mixed
     */
    
      public function actionView($id)
    {
        $this->permisosService->verificarAdministrador();
        $this->permisosService->verificarPermiso('usuarios_ver');
        $model = $this->adminService->obtenerUsuario($id);
        return $this->render('verusuario', [
            'model' => $model,
            'historial' => $this->permisosService->tienePermiso('usuarios_historial')
                ? $this->adminService->historialUsuario($id)
                : null,
        ]);
    }



    /**
     * Creates a new Usuarios model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        
        $this->permisosService->verificarAdministrador();
        $this->permisosService->verificarPermiso('usuarios_crear');
        $resultado = $this->adminService->nuevoUsuario(Yii::$app->request->post());
        $roles = $this->adminService->obtenerRolesActivos();
        if ($resultado && $resultado['exito']) {
             return $this->redirect(['index', 'status' => 'created']);   }
        $this->cuentaService->limpiarFlashes();
        return $this->render('create', [
            'model' => $resultado['model'] ?? new Usuarios(),
            'roles' => $roles,
        ]);

    }




    /**
     * Updates an existing Usuarios model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */

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
        return $this->render('update', [
            'model' => $resultado['model'], // importante: devolver el modelo con errores
            'roles' => $roles,
        ]);
    }





/**
 * Cambiar la contraseña de un usuario (lo hace el admin).
 * Los campos llegan vacíos; solo se guarda si se completan los dos y coinciden.
 */
public function actionUpdatepassword($id)
{
    $usuario = $this->findModel($id);
    $this->permisosService->verificarAdministrador();
    $this->permisosService->verificarPermiso('usuarios_editar');

    $form = new \app\models\CambioPasswordObligatorioForm();
    if ($form->load(Yii::$app->request->post()) && $form->validate()) {
        // Solo cambia la contraseña (y cierra sus sesiones "recordarme").
        $usuario->updateAttributes([
            'contrasena' => Yii::$app->security->generatePasswordHash($form->nueva_contrasena),
            'auth_key' => Yii::$app->security->generateRandomString(),
        ]);
        HistorialUsuarios::registrar($usuario->idusuario, 'Cambio de contraseña',
            'La contraseña fue cambiada por un administrador.', Yii::$app->user->id);
        // Yii guarda el auth_key en la sesión y cierra las sesiones cuyo auth_key ya no coincide.
        // Si se la cambia a sí mismo, actualizamos su sesión actual para que no lo saque a él.
        if ((int) $usuario->idusuario === (int) Yii::$app->user->id) {
            Yii::$app->session->set(Yii::$app->user->authKeyParam, $usuario->auth_key);
        }
        Yii::$app->session->setFlash('flash', ['type' => 'success', 'message' => 'Contraseña actualizada. Sus sesiones abiertas se cierran en el próximo clic.']);
        return $this->redirect(['view', 'id' => $usuario->idusuario]);
    }

    // Nunca devolver al HTML lo que se escribió
    $form->nueva_contrasena = $form->confirmar_contrasena = null;
    return $this->render('updatepass', ['model' => $form, 'usuario' => $usuario]);
}


    /** Mi perfil: mis datos + lo que pasó con mi cuenta. */
    public function actionCuenta()
    {
        $model = Usuarios::findOne(Yii::$app->user->id);
        return $this->render('perfil', [
            'model' => $model,
            'historial' => $this->adminService->historialUsuario($model->idusuario),
        ]);
    }

    /** Editar mis datos (no puedo cambiar mi rol, estado ni contraseña desde acá). */
    public function actionUpdateperfil()
    {
        $model = Usuarios::findOne(Yii::$app->user->id);
        if (Yii::$app->request->isPost) {
            $resultado = $this->cuentaService->actualizarUsuario($model, Yii::$app->request->post());
            if ($resultado && $resultado['exito']) {
                return $this->redirect(['cuenta']);
            }
            $model = $resultado['model'] ?? $model;
        }
        return $this->render('updateperfil', ['model' => $model]);
    }

    /** Cambiar mi contraseña: pide la actual. Cierra mis otras sesiones; esta sigue abierta. */
    public function actionUpdatepasswordperfil()
    {
        $usuario = Usuarios::findOne(Yii::$app->user->id);
        $form = new ChangePasswordForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $usuario->updateAttributes([
                'contrasena' => Yii::$app->security->generatePasswordHash($form->newPassword),
                'auth_key' => Yii::$app->security->generateRandomString(),
            ]);
            Yii::$app->session->set(Yii::$app->user->authKeyParam, $usuario->auth_key);
            HistorialUsuarios::registrar($usuario->idusuario, 'Cambio de contraseña',
                'El usuario cambió su contraseña desde Mi perfil.', $usuario->idusuario);
            Yii::$app->session->setFlash('flash', ['type' => 'success', 'message' => 'Tu contraseña fue actualizada.']);
            return $this->redirect(['cuenta']);
        }
        $form->currentPassword = $form->newPassword = $form->confirmPassword = null;
        return $this->render('updatepasswordperfil', ['model' => $form]);
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




    /**
     * Deletes an existing Usuarios model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->permisosService->verificarAdministrador();
        $this->permisosService->verificarPermiso('usuarios_eliminar');
        $resultado = $this->adminService->eliminarUsuario($id);
        Yii::$app->session->setFlash('flash', [
            'type' => $resultado['exito'] ? 'success' : 'error',
            'message' => $resultado['mensaje'],
        ]);
        return $this->redirect(['index']);
    }

    /**
     * Finds the Usuarios model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Usuarios the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Usuarios::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    public function actionExport()
    {

        //\moonland\phpexcel\Excel::widget([
        \moonland\phpexcel\Excel::export([
            'models' => Usuarios::find()->all(),
            //'mode' => 'export', //default value as 'export'
            'columns' => ['idusuario', 'nombre', 'apellido', 'direccion', 'telefono', 'ciudad', 'email', 'fecha_nacimiento', 'movil', 'sexo'], //without header working, because the header will be get label from attribute label.
            'headers' => ['Numero' => 'idusuario', 'Nombre' => 'nombre', 'Apellido' => 'apellido', 'Direccion' => 'direccion', 'Telefono' => 'telefono', 'Ciudad' => 'ciudad', 'Email' => 'email', 'Fecha Nacimiento' => 'fecha_nacimiento', 'Movil' => 'movil', 'Sexo' => 'sexo'],
        ]);
    }
}
