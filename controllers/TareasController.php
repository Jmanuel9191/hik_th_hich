<?php

namespace app\controllers;
use app\components\InterfacePermisosService;
use app\components\InterfaceTarea;
use app\models\Tareas;
use app\models\TareasSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class TareasController extends Controller
{

    private $tareaService;
    private $permisosService;
    /**
     * @inheritdoc
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],

        ];
    }
    public function __construct($id, $module, InterfaceTarea $tareaService, InterfacePermisosService $permisosService, $config = [])
    {
        $this->tareaService = $tareaService;
        $this->permisosService = $permisosService;
        parent::__construct($id, $module, $config);
    }


    public function actionIndex()
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'tareas_listar');

       $resultadoBusqueda = $this->tareaService->listTareas(Yii::$app->request->queryParams);
        if (Yii::$app->request->get('flashclear')) {
            Yii::$app->session->removeFlash('flash');
        }
        return $this->render('index', [
            'searchModel' => $resultadoBusqueda['searchModel'],
            'dataProvider' => $resultadoBusqueda['dataProvider'],
            'render' => 'listatotaltareas',
        ]);

    }

    /**
     * @throws NotFoundHttpException
     */


    public function actionView($id)
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'tareas_ver');
        $model = $this->tareaService->findModel($id);
        $resultadoBusqueda = $this->tareaService->listHistorial(
            Yii::$app->request->queryParams,
            $id // <-- Aquí se envía el ID de la tarea
        );
        return $this->render('index', [
            'searchModel' => $resultadoBusqueda['searchModel'],
            'dataProvider' => $resultadoBusqueda['dataProvider'],
            'model' => $model,
            'render' => 'view',
        ]);
    }

    public function actionCreate()
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'tareas_crear');
        $resultado = $this->tareaService->nuevaTarea(Yii::$app->request->post());
        if ($resultado && $resultado['exito']) {
            return $this->redirect(['index']);

        }

        $this->tareaService->limpiarFlashes();
        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'create',

        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {

        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'tareas_editar');
        $resultado = $this->tareaService->actualizarTarea(Yii::$app->request->post(), $id);

        if ($resultado && $resultado['exito']) {
            return $this->redirect(['view', 'id' => $id]);

        }

        $this->tareaService->limpiarFlashes();
        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'update',
        ]);


    }



    public function actionEstado($id)
    {
        $this->permisosService->verificarAcceso(['administrador', 'gestor'], 'tareas_estado');
        $motivo = Yii::$app->request->post('motivo');
        $resultado = $this->tareaService->cambiarEstadoTarea($id, $motivo); // <-- asegúrate de tener esta función bien hecha

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $resultado;
    }




    /**
     * @throws StaleObjectException
     * @throws NotFoundHttpException
     */
    public function actionDelete($id): \yii\web\Response
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $this->findModel($id)->delete();
        return $this->redirect(['index']);

    }


        protected function findModel($id): Tareas
        {
            if (($model = Tareas::findOne($id)) !== null) {
                return $model;
            } else {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
        }



}
