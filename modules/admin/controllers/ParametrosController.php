<?php

namespace app\modules\admin\controllers;

use app\components\InterfaceParametros;
use app\components\InterfacePermisosService;
use app\models\Parametros;
use Yii;
use yii\web\NotFoundHttpException;
class ParametrosController extends BaseController
{
    private $parametrosService;
    private $permisosService;

    public function __construct($id, $module, InterfaceParametros $parametrosService, InterfacePermisosService $permisosService, $config = [])
    {
        $this->parametrosService = $parametrosService;
        $this->permisosService = $permisosService;
        parent::__construct($id, $module, $config);
    }

    public function actionIndex()
    {
        $this->permisosService->verificarAdministrador();
        $resultado = $this->parametrosService->listParametros(Yii::$app->request->queryParams);
        $this->parametrosService->limpiarFlashes();     
        return $this->render('index', [
            'searchModel' => $resultado['searchModel'],
            'dataProvider' => $resultado['dataProvider'],
        ]);
    }

    public function actionView($id)
    {
        $this->permisosService->verificarAdministrador();
        $model = $this->parametrosService->findModel($id);
        $this->parametrosService->limpiarFlashes();
        return $this->render('view', ['model' => $model]);
    }

    public function actionCreate()
    {
        $this->permisosService->verificarAdministrador();
        $resultado = $this->parametrosService->nuevoParametro(Yii::$app->request->post());

        if ($resultado && $resultado['exito']) {
            return $this->redirect(['index', 'status' => 'created']);
        }
        $this->parametrosService->limpiarFlashes();
        return $this->render('create', [
            'model' => $resultado['model'] ?? new Parametros(),
        ]);
    }

    public function actionUpdate($id)
    {
        $this->permisosService->verificarAdministrador();
        $model = $this->parametrosService->findModel($id);

        if (Yii::$app->request->isPost) {
            $resultado = $this->parametrosService->actualizarParametro($model, Yii::$app->request->post());
            if ($resultado && $resultado['exito']) {
                    return $this->redirect(['view', 'id' => $model->idparametro, 'status' => 'updated']);
       
            }
            $model = $resultado['model'] ?? $model;
        }
        $this->parametrosService->limpiarFlashes();
        return $this->render('update', ['model' => $model]);
    }

    

        public function actionDelete($id): \yii\web\Response
    {
          $this->permisosService->verificarAdministrador();
        $this->findModel($id)->delete();
        return $this->redirect(['index']);

    }

 protected function findModel($id): Parametros
        {
            if (($model = Parametros::findOne($id)) !== null) {
                return $model;
            } else {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
        }

}
