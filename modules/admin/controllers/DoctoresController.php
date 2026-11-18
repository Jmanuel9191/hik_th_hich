<?php

namespace app\modules\admin\controllers;
use app\components\InterfacePermisosService;
use app\components\InterfaceTarea;
use app\models\Doctores;
use app\models\DoctoresSearch;
use PhpParser\Comment\Doc;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class DoctoresController extends Controller
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
       $resultadoBusqueda = $this->tareaService->listTareas(Yii::$app->request->queryParams);
        if (Yii::$app->request->get('flashclear')) {
            Yii::$app->session->removeFlash('flash');
        }
        return $this->render('index', [
            'searchModel' => $resultadoBusqueda['searchModel'],
            'dataProvider' => $resultadoBusqueda['dataProvider'],
        ]);

    }

    /**
     * @throws NotFoundHttpException
     */


    public function actionView($id)
    {
        $model = $this->tareaService->findModel($id);
        $resultadoBusqueda = $this->tareaService->listHistorial(
            Yii::$app->request->queryParams,
            $id // <-- Aquí se envía el ID de la tarea
        );
        return $this->render('view', [
            'searchModel' => $resultadoBusqueda['searchModel'],
            'dataProvider' => $resultadoBusqueda['dataProvider'],
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $resultado = $this->tareaService->nuevaTarea(Yii::$app->request->post());
        if ($resultado && $resultado['exito']) {
            return $this->redirect(['index']);

        }

        $this->tareaService->limpiarFlashes();
        return $this->render('create', [
            'model' => $resultado['model'],

        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {

        $resultado = $this->tareaService->actualizarTarea(Yii::$app->request->post(), $id);

        if ($resultado && $resultado['exito']) {
            return $this->redirect(['view', 'id' => $id]);

        }

        $this->tareaService->limpiarFlashes();
        return $this->render('update', [
            'model' => $resultado['model'],
        ]);


    }



    public function actionEstado($id)
    {
        $this->permisosService->verificarAcceso(['administrador', 'usuario'], 'tareas_estado');
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
        $this->findModel($id)->delete();
        return $this->redirect(['index']);

    }


        protected function findModel($id): Doctores
        {
            if (($model = Doctores::findOne($id)) !== null) {
                return $model;
            } else {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
        }



}
