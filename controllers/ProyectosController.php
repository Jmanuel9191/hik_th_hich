<?php

namespace app\controllers;

use app\components\InterfaceAsignacion;
use app\components\InterfaceCuenta;
use app\components\InterfaceGestionProyecto;
use app\components\InterfacePermisosService;
use app\components\InterfaceProyectos;
use app\models\Asignacion;
use app\models\ChangePasswordForm;
use app\models\Configuracion;
use app\models\Forget;
use app\models\Proyectos;
use app\models\ProyectosSearch;
use app\models\Usuarios;
use app\models\Usuariosperfil;
use app\models\UsuariosSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class ProyectosController extends Controller
{

    private $gestionProyectAService;
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
    public function __construct($id, $module, InterfaceGestionProyecto $gestionProyectAService, InterfacePermisosService $permisosService, $config = [])
    {
        $this->gestionProyectAService = $gestionProyectAService;
        $this->permisosService = $permisosService;
        parent::__construct($id, $module, $config);
    }


    public function actionIndex()
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'proyectos_listar');
        $resultadoBusqueda = $this->gestionProyectAService->listarProyectos(Yii::$app->request->queryParams);
        $proyectos = $resultadoBusqueda['dataProvider']->getModels();
        $proyectosFinalizables = [];
        foreach ($proyectos as $proyecto) {
            if ($this->gestionProyectAService->puedeFinalizarProyecto($proyecto->idproyecto)) {
                $proyectosFinalizables[$proyecto->idproyecto] = true;
            }
        }
        return $this->render('index', [
            'searchModel' => $resultadoBusqueda['searchModel'],
            'dataProvider' => $resultadoBusqueda['dataProvider'],
            'proyectosFinalizables' => $proyectosFinalizables,
            'render' => 'listap',
        ]);
    }
    public function actionListproyectos()
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'proyectos_listar');
        $resultadoBusqueda = $this->gestionProyectAService->listarProyectos(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $resultadoBusqueda['searchModel'],
            'dataProvider' => $resultadoBusqueda['dataProvider'],
            'render' => 'listapr',
        ]);
    }


    public function actionHistorial($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $model = $this->gestionProyectAService->findModel($id);
        $resultadoBusqueda = $this->gestionProyectAService->listHistorial(
            Yii::$app->request->queryParams,
            $id // <-- Aquí se envía el ID de la tarea
        );
        return $this->render('index', [
            'searchModel' => $resultadoBusqueda['searchModel'],
            'dataProvider' => $resultadoBusqueda['dataProvider'],
            'model' => $model,
            'render' => 'historial',
        ]);
    }




    /**
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {

        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'proyectos_ver');
        $model = $this->gestionProyectAService->obtenerProyecto($id);
        $model->populateRelation('sucursales', $model->sucursales);
        $model->populateRelation('sectores', $model->sectores);
        $asignacionesFinalizables = $this->gestionProyectAService->obtenerAsignacionesFinalizables($id);
        $porprgres = \app\models\Asignacion::obtenerEnProgresoPorProyecto($model->idproyecto); // <- NUEVO
        $finalizadas = \app\models\Asignacion::obtenerFinalizadosPorProyecto($model->idproyecto); // <- NUEVO

        return $this->render('index', [
            'model' => $model,
            'render' => 'view',
            'asignacionesFinalizables' => $asignacionesFinalizables,
            'porprgres' => $porprgres,
            'finalizadas' => $finalizadas,
        ]);
    }




    public function actionViewproyect($id)
    {
        $this->permisosService->verificarLogin();
        $model = $this->gestionProyectAService->obtenerProyecto($id);
        $asignacionesFinalizables = $this->gestionProyectAService->obtenerAsignacionesFinalizables($id);
        $porprgres = \app\models\Asignacion::obtenerEnProgresoPorProyecto($model->idproyecto);
        $finalizadas = \app\models\Asignacion::obtenerFinalizadosPorProyecto($model->idproyecto); // <- NUEVO

        return $this->render('index', [
            'model' => $model,
            'render' => 'view',
            'asignacionesFinalizables' => $asignacionesFinalizables,
            'porprgres' => $porprgres,
            'finalizadas' => $finalizadas,
        ]);
    }



    public function actionCreate()
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'proyectos_crear');
        $resultado = $this->gestionProyectAService->nuevoProyecto(Yii::$app->request->post());
        if ($resultado && $resultado['exito']) {
            return $this->redirect(['index', 'render' => 'listap']);


        }

        $this->gestionProyectAService->limpiarFlashes();
        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'create',

        ]);
    }


    public function actionAsignaciondetareas($idproyecto)
    {

        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'asignaciones_crear');
        $model = $this->gestionProyectAService->prepararModeloAsignacion($idproyecto, true);
        if (Yii::$app->request->isPost) {
            $resultado = $this->gestionProyectAService->procesarAsignacionTarea($model, Yii::$app->request->post());
            if ($resultado['exito']) {
                return $this->redirect(['view', 'id' => $idproyecto]);
            } else {
                Yii::$app->session->setFlash('error', 'Error al asginar. Inténtelo más tarde.');
            }
        }
        return $this->render('index', [
            'model' => $model,
            'render' => 'asignar',
        ]);
    }



    public function actionEditasignaciondetareas($id)
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'asignaciones_editar');
        $model = $this->gestionProyectAService->obtenerAsignacionPorId($id);
        $model->scenario = 'update';
        if (Yii::$app->request->isPost) {
            $resultado = $this->gestionProyectAService->procesarEdicionAsignacion($id, Yii::$app->request->post());
            if ($resultado['exito']) {
                return $this->redirect(['view', 'id' => $model->idproyecto]);
            } else {
                Yii::$app->session->setFlash('error', 'Error al editar la asignacion');
                // Aseguramos pasar el modelo con los errores
                $model = $resultado['model'];
            }
        }

        return $this->render('index', [
            'model' => $model,
            'render' => 'editasignar',
        ]);
    }

    public function actionDeletasignaciondetareas($id)
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'asignaciones_eliminar');

        $asignacion = Asignacion::findOne($id);
        $resultado = $this->gestionProyectAService->eliminarAsignacionTarea($id);

        if ($resultado['exito']) {
            Yii::$app->session->setFlash('sucess', 'Asignación eliminada correctamente.');
        } else {
            Yii::$app->session->setFlash('error', 'No se pudo eliminar la asignación.');
        }

        if ($asignacion->idproyecto) {
            return $this->redirect(['/proyectos/view', 'id' => $asignacion->idproyecto]);
        } elseif ($asignacion->idcampania) {
            return $this->redirect(['/campanias/view', 'id' => $asignacion->idcampania]);
        } else {
            return $this->goHome(); // o a algún otro lugar por defecto
        }
    }



    /**
     */
    public function actionUpdate($id)
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'proyectos_editar');
        $resultado = $this->gestionProyectAService->actualizarProyecto(Yii::$app->request->post(), $id);
        if ($resultado && $resultado['exito']) {
            return $this->redirect(['index']);
        }
        $this->gestionProyectAService->limpiarFlashes();
        $model = $resultado['model'];
        $model->sucursales = $model->getSucursalesIds();
        $model->sectores = $model->getSectoresIds();
        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'update',

        ]);
    }




    public function actionAceptarTarea($id)
    {
        $this->permisosService->verificarAcceso(['consultor'], 'asignaciones_aceptar');
        $resultado = $this->gestionProyectAService->aceptarTarea($id);
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'exito' => $resultado['exito'],
            'mensaje' => $resultado['mensaje'],
        ];
    }
    public function actionEstado($id)
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'proyectos_estado');
        $motivo = Yii::$app->request->post('motivo');
        $resultado = $this->gestionProyectAService->cambiarEstadoproyecto($id, $motivo);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'exito' => $resultado['exito'],
            'mensaje' => $resultado['mensaje'],
        ];
    }




    public function actionCrearRecurso($idasignartarea)
    {

        $this->permisosService->verificarAcceso(['administrador', 'gestor', 'consultor'], 'recursos_crear');
        $resultado = $this->gestionProyectAService->nuevoRecurso(Yii::$app->request->post(), $idasignartarea);

        if ($resultado['exito']) {
            return $this->redirect(Yii::$app->request->referrer ?: ['index']);
        }

        Yii::$app->session->setFlash('error', 'Hubo un problema al crear el recurso. Verifique los campos.');
        Yii::$app->session->setFlash('abrirModalRecurso', $idasignartarea); // para saber qué modal abrir
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    public function actionValidarRecurso($idasignartarea)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new \app\models\Recursos();
        $model->idasignartarea = $idasignartarea;
        if ($model->load(Yii::$app->request->post())) {
            return \yii\widgets\ActiveForm::validate($model);
        }

        return [];
    }
    public function actionEliminarRecurso($id)
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor', 'consultor'], 'recursos_eliminar');
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $resultado = $this->gestionProyectAService->eliminarRecurso($id);

        return [
            'exito' => $resultado['exito'],
            'mensaje' => $resultado['mensaje'],
        ];
    }

    public function actionEditarRecurso($id)
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor', 'consultor'], 'recursos_editar');
        $request = Yii::$app->request;
        $resultado = $this->gestionProyectAService->editarRecurso($id, $request->post());
        if ($resultado['exito']) {
            return $this->redirect($request->referrer ?: ['index']);
        }
        Yii::$app->session->setFlash('error', 'Hubo un problema al editar el recurso.');
        Yii::$app->session->setFlash('abrirModalRecurso', $id); // Para que si falla, se vuelva a abrir el modal
        return $this->redirect($request->referrer ?: ['index']);
    }




    public function actionProgreso($id)
    {
        $this->gestionProyectAService->verificarAccesoAdmingestoryadmin();
        $resultado = $this->gestionProyectAService->cambiarEstadoproyecto($id);
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'exito' => $resultado['exito'],
            'mensaje' => $resultado['mensaje'],
        ];
    }
    /**
     * @throws StaleObjectException
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $modelu = $this->gestionProyectAService->verificarAccesoAdmingestoryadmin();
        //$this->gestionProyectAService->eliminarProyecto($id);
       // return $this->redirect(['index']);
    }


        public function actionMisproyectos()
        {
            $this->permisosService->verificarRol(['consultor', 'cliente']);
            $resultadoBusqueda = $this->gestionProyectAService->listarmisProyectos(Yii::$app->request->queryParams);
            return $this->render('index', [
                'searchModel' => $resultadoBusqueda['searchModel'],
                'dataProvider' => $resultadoBusqueda['dataProvider'],
                'render' => 'milista',
            ]);
        }



        //BLOQUE PARA HISTORIAL DE MATERIALES

    public function actionHistorialmaterial($idrecurso)
    {
        $this->permisosService->verificarLogin();
        $this->gestionProyectAService->verificarAccesoCompletoAlHistorial($idrecurso);
        $resultado = $this->gestionProyectAService->listarHistorial(Yii::$app->request->queryParams, $idrecurso);
        $recurso = $this->gestionProyectAService->obtenerRecurso($idrecurso);
        $ultimoId = $this->gestionProyectAService->obtenerUltimoIdMensaje($idrecurso);
        $esCliente = $this->gestionProyectAService->esClienteDelRecurso($recurso);

        return $this->render('index', [
            'searchModel' => $resultado['searchModel'],
            'dataProvider' => $resultado['dataProvider'],
            'recurso' => $resultado['recurso'], // modelo completo del recurso
            'ultimoId' => $ultimoId, // modelo completo del recurso
            'esCliente' => $esCliente, // modelo completo del recurso
            'render' => 'historialmaterial',     // indica qué vista parcial cargar
        ]);
    }




    public function actionHistorialasignaciones($idproyecto)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);

        // Carga el proyecto actual para mostrar su título
        $model = $this->gestionProyectAService->obtenerProyecto($idproyecto);

        // Llama al servicio para filtrar por proyecto
        $resultadoBusqueda = $this->gestionProyectAService->listHistorialAsignacion(
            Yii::$app->request->queryParams,
            $idproyecto
        );

        return $this->render('index', [
            'searchModel' => $resultadoBusqueda['searchModel'],
            'dataProvider' => $resultadoBusqueda['dataProvider'],
            'model' => $model, // Este es el modelo del proyecto
            'render' => 'historialasignaciones',
        ]);
    }



    public function actionEnviarMensaje()
    {

        $this->permisosService->verificarLogin();
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $resultado = $this->gestionProyectAService->procesarMensajeConArchivo(Yii::$app->request->post(), $_FILES);

        return $resultado;
    }

    public function actionFinalizartarea()
    {

        $this->permisosService->verificarAcceso(['administrador', 'consultor', 'gestor'], 'finalizar_tarea');

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $resultado = $this->gestionProyectAService->finalizarTarea($post);
        return $resultado;
    }
    public function actionVolvarahacer()    {
        $this->permisosService->verificarRol(['administrador', 'consultor', 'gestor']);//Seguridad
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $resultado = $this->gestionProyectAService->revertirFinalizacionTarea($post); // <- lo manejás desde el Service
        return $resultado;
    }

    public function actionFinalizarproyecto()
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'proyectos_finalizar');
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $resultado = $this->gestionProyectAService->finalizarProyecto($post);
        return $resultado;
    }

    public function actionCargarNuevos($idrecurso, $ultimoId)
    {

        $this->permisosService->verificarLogin();
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            return $this->gestionProyectAService->obtenerMensajesNuevos($idrecurso, $ultimoId);

        } catch (\Throwable $e) {
            return [
                'exito' => false,
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'traza' => $e->getTraceAsString()
            ];
        }

    }




    public function actionRechazarRecurso()
    {

        $this->permisosService->verificarAcceso(['cliente'], 'recursos_rechazar');


        $post = Yii::$app->request->post();
        $resultado = $this->gestionProyectAService->rechazarRecursoConComentario($post);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'exito' => $resultado['exito'],
            'mensaje' => $resultado['mensaje'],
        ];
    }


    public function actionAprobarRecurso()
    {
        $this->permisosService->verificarAcceso(['cliente'], 'recursos_aprobar');

        $post = Yii::$app->request->post();
        $resultado = $this->gestionProyectAService->aprobarRecursoConComentario($post);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'exito' => $resultado['exito'],
            'mensaje' => $resultado['mensaje'],
        ];
    }

}
