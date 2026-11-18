<?php

namespace app\controllers;

use app\components\InterfaceAsignacion;
use app\components\InterfaceCuenta;
use app\components\InterfaceGestionCampania;
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

class CampaniasController extends Controller
{

    private $gestionCampaniasAService;
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
    public function __construct($id, $module, InterfaceGestionCampania $gestionCampaniaAService, InterfacePermisosService $permisosService, $config = [])
    {
        $this->gestionCampaniasAService = $gestionCampaniaAService;
        $this->permisosService = $permisosService;
        parent::__construct($id, $module, $config);
    }


    public function actionIndex()
    {

        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'campanias_listar');
        $resultadoBusqueda = $this->gestionCampaniasAService->listarCampanias(Yii::$app->request->queryParams);
        $campanias = $resultadoBusqueda['dataProvider']->getModels();
        $campaniasFinalizables = [];
        foreach ($campanias as $campania) {
            if ($this->gestionCampaniasAService->puedeFinalizarCampania($campania->idcampania)) {
                $campaniasFinalizables[$campania->idcampania] = true;
            }
        }
        return $this->render('index', [
            'searchModel' => $resultadoBusqueda['searchModel'],
            'dataProvider' => $resultadoBusqueda['dataProvider'],
            'campaniasFinalizables' => $campaniasFinalizables,
            'render' => 'listac',
        ]);
    }


    public function actionHistorial($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $model = $this->gestionCampaniasAService->findModel($id);
        $resultadoBusqueda = $this->gestionCampaniasAService->listHistorial(
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


 $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'campanias_ver');

        $this->permisosService->verificarLogin();
        $model = $this->gestionCampaniasAService->obtenerCampania($id);
        $asignacionesFinalizables = $this->gestionCampaniasAService->obtenerAsignacionesFinalizablescamp($id);
        $model->populateRelation('sucursales', $model->sucursales);
        $model->populateRelation('sectores', $model->sectores);
        $model->populateRelation('audiencias', $model->audiencias);
        $asignacionesPorHacer = Asignacion::obtenerPorHacerSinMonitoreo($model->idcampania);


        $porprgres = \app\models\Asignacion::obtenerEnProgresoPorCampania($model->idcampania); // <- NUEVO
        $finalizadas = \app\models\Asignacion::obtenerFinalizadosPorCampania($model->idcampania); // <- NUEVO

        return $this->render('index', [
            'model' => $model,
            'render' => 'view',
            'asignacionesFinalizables' => $asignacionesFinalizables,
            'porprgres' => $porprgres,
            'finalizadas' => $finalizadas,
            'asignacionesPorHacer' => $asignacionesPorHacer,
        ]);
    }




    public function actionViewproyect($id)
    {

        $this->permisosService->verificarLogin();

        $model = $this->gestionCampaniasAService->obtenerCampania($id);
        $asignacionesFinalizables = $this->gestionCampaniasAService->obtenerAsignacionesFinalizablescamp($id);

        $porprgres = \app\models\Asignacion::obtenerEnProgresoPorCampania($model->idcampania); // <- NUEVO
        $finalizadas = \app\models\Asignacion::obtenerFinalizadosPorCampania($model->idcampania); // <- NUEVO
        $asignacionesPorHacer = Asignacion::obtenerPorHacerSinMonitoreo($model->idcampania);

        return $this->render('index', [
            'model' => $model,
            'render' => 'view',
            'asignacionesFinalizables' => $asignacionesFinalizables,
            'porprgres' => $porprgres,
            'finalizadas' => $finalizadas,
            'asignacionesPorHacer' => $asignacionesPorHacer,
        ]);
    }



    public function actionCreate()
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'campanias_crear');
        $resultado = $this->gestionCampaniasAService->nuevoCampania(Yii::$app->request->post());
        if ($resultado && $resultado['exito']) {
            return $this->redirect(['index', 'render' => 'listac']);


        }

        $this->gestionCampaniasAService->limpiarFlashes();
        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'create',

        ]);
    }


    public function actionAsignaciondetareas($idcampania)
    {

        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'asignaciones_crear');
        $model = $this->gestionCampaniasAService->prepararModeloAsignacioncamp($idcampania);
        if (Yii::$app->request->isPost) {
            $resultado = $this->gestionCampaniasAService->procesarAsignacionTarea($model, Yii::$app->request->post());
            if ($resultado['exito']) {
                return $this->redirect(['view', 'id' => $idcampania]);
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
        $this->gestionCampaniasAService->verificarAccesoAdmingestoryadmin();
        $model = $this->gestionCampaniasAService->obtenerAsignacionPorId($id);
        $model->scenario = 'update';

        if (Yii::$app->request->isPost) {
            $resultado = $this->gestionCampaniasAService->procesarEdicionAsignacion($id, Yii::$app->request->post());
            if ($resultado['exito']) {
                return $this->redirect(['view', 'id' => $model->idcampania]);
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



    /**
     */
    public function actionUpdate($id)
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'campanias_editar');

        $resultado = $this->gestionCampaniasAService->actualizarCampania(Yii::$app->request->post(), $id);
        if ($resultado && $resultado['exito']) {
            return $this->redirect(['index']);
        }
        $this->gestionCampaniasAService->limpiarFlashes();

        $model = $resultado['model'];
        $model->sucursales = $model->getSucursalesIds();
        $model->sectores = $model->getSectoresIds();
        $model->audiencias = $model->getAudienciasIds();
        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'update',

        ]);
    }


    public function actionDeletasignaciondetareas($id)
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'asignaciones_eliminar');

        $asignacion = Asignacion::findOne($id);
        $resultado = $this->gestionCampaniasAService->eliminarAsignacionTarea($id);

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

    public function actionAceptarTarea($id)
    {
        $this->permisosService->verificarAcceso(['consultor'], 'asignaciones_aceptar');
        $resultado = $this->gestionCampaniasAService->aceptarTarea($id);
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'exito' => $resultado['exito'],
            'mensaje' => $resultado['mensaje'],
        ];
    }
    public function actionEstado($id)
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'campanias_estado');
        $motivo = Yii::$app->request->post('motivo');
        $resultado = $this->gestionCampaniasAService->cambiarEstadocampania($id, $motivo);
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'exito' => $resultado['exito'],
            'mensaje' => $resultado['mensaje'],
        ];
    }




    public function actionCrearRecurso($idasignartarea)
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor', 'consultor'], 'recursos_crear');
        $resultado = $this->gestionCampaniasAService->nuevoRecurso(Yii::$app->request->post(), $idasignartarea);

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
        $resultado = $this->gestionCampaniasAService->eliminarRecurso($id);

        return [
            'exito' => $resultado['exito'],
            'mensaje' => $resultado['mensaje'],
        ];
    }

    public function actionEditarRecurso($id)
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor', 'consultor'], 'recursos_editar');

        $request = Yii::$app->request;
        $resultado = $this->gestionCampaniasAService->editarRecurso($id, $request->post());
        if ($resultado['exito']) {
            return $this->redirect($request->referrer ?: ['index']);
        }
        Yii::$app->session->setFlash('error', 'Hubo un problema al editar el recurso.');
        Yii::$app->session->setFlash('abrirModalRecurso', $id); // Para que si falla, se vuelva a abrir el modal
        return $this->redirect($request->referrer ?: ['index']);
    }




    public function actionProgreso($id)
    {
        $this->gestionCampaniasAService->verificarAccesoAdmingestoryadmin();
        $resultado = $this->gestionCampaniasAService->cambiarEstadocampania($id);
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
        $modelu = $this->gestionCampaniasAService->verificarAccesoAdmingestoryadmin();
        //$this->gestionCampaniasAService->eliminarProyecto($id);
       // return $this->redirect(['index']);
    }

        public function actionMiscampanias()
        {

            $this->permisosService->verificarRol(['consultor', 'cliente']);
            $resultadoBusqueda = $this->gestionCampaniasAService->listarmisCampanias(Yii::$app->request->queryParams);
            return $this->render('index', [
                'searchModel' => $resultadoBusqueda['searchModel'],
                'dataProvider' => $resultadoBusqueda['dataProvider'],
                'render' => 'milista',
            ]);
        }



        //BLOQUE PARA HISTORIAL DE MATERIALES

    public function actionHistorialmaterial($idrecurso)
    {
        $this->gestionCampaniasAService->verificarAccesoCompletoAlHistorialcamp($idrecurso);
        $resultado = $this->gestionCampaniasAService->listarHistorial(Yii::$app->request->queryParams, $idrecurso);
        $recurso = $this->gestionCampaniasAService->obtenerRecurso($idrecurso);
        $ultimoId = $this->gestionCampaniasAService->obtenerUltimoIdMensaje($idrecurso);
        $esCliente = $this->gestionCampaniasAService->esClienteDelRecurso($recurso);

        return $this->render('index', [
            'searchModel' => $resultado['searchModel'],
            'dataProvider' => $resultado['dataProvider'],
            'recurso' => $resultado['recurso'], // modelo completo del recurso
            'ultimoId' => $ultimoId, // modelo completo del recurso
            'esCliente' => $esCliente, // modelo completo del recurso
            'render' => 'historialmaterial',     // indica qué vista parcial cargar
        ]);
    }


    public function actionEnviarMensaje()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->permisosService->verificarLogin();
        $resultado = $this->gestionCampaniasAService->procesarMensajeConArchivo(Yii::$app->request->post(), $_FILES);

        return $resultado;
    }

    public function actionFinalizartarea()
    {
        $this->permisosService->verificarAcceso(['administrador', 'consultor', 'gestor'], 'finalizar_tarea');
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $resultado = $this->gestionCampaniasAService->finalizarTarea($post);
        return $resultado;
    }



    public function actionSolicitudfinalizarproyecto()
    {
        $this->permisosService->verificarAcceso(['cliente'], 'campanias_solicitar_cierre');
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $resultado = $this->gestionCampaniasAService->solicitudfinalizarCamp($post);
        return $resultado;
    }


    public function actionVolvarahacer()    {
        $this->gestionCampaniasAService->verificarAccesoAdmingestoradminyconsultor(); // Seguridad
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $resultado = $this->gestionCampaniasAService->revertirFinalizacionTarea($post); // <- lo manejás desde el Service
        return $resultado;
    }

    public function actionFinalizarcampania()
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'campanias_finalizar');

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $resultado = $this->gestionCampaniasAService->finalizarCampania($post);
        return $resultado;
    }

    public function actionCargarNuevos($idrecurso, $ultimoId)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        try {
            return $this->gestionCampaniasAService->obtenerMensajesNuevos($idrecurso, $ultimoId);

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
        $resultado = $this->gestionCampaniasAService->rechazarRecursoConComentario($post);

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
        $resultado = $this->gestionCampaniasAService->aprobarRecursoConComentario($post);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'exito' => $resultado['exito'],
            'mensaje' => $resultado['mensaje'],
        ];
    }

}
