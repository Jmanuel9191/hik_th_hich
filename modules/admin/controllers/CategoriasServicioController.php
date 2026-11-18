<?php
namespace app\modules\admin\controllers;

use app\components\InterfaceCategoriasServicio;
use app\components\InterfacePermisosService;
use app\components\InterfaceCuenta;
use app\models\CategoriasServicio;
use Yii;

class CategoriasServicioController extends BaseController
{
    private $categoriasService;
    private $permisosService;
    private $cuentaService;
    public function __construct($id, $module, InterfaceCategoriasServicio $categoriasService, InterfacePermisosService $permisosService, InterfaceCuenta $cuentaService, $config = [])
    {
        $this->categoriasService = $categoriasService;
        $this->permisosService   = $permisosService;
         $this->cuentaService = $cuentaService;
        parent::__construct($id, $module, $config);
    }

    public function actionIndex()
    {
        $resultado = $this->categoriasService->listCategorias(Yii::$app->request->queryParams);
        $this->cuentaService->limpiarFlashes();
        return $this->render('index', [
            'searchModel'  => $resultado['searchModel'],
            'dataProvider' => $resultado['dataProvider'],
        ]);
    }

    public function actionCreate()
    {
        $resultado = $this->categoriasService->nuevaCategoria(Yii::$app->request->post());

        if ($resultado && $resultado['exito']) {
            return $this->redirect(['index', 'status' => 'created']);
        }
        $this->cuentaService->limpiarFlashes();

        return $this->render('create', [
            'model' => $resultado['model'] ?? new CategoriasServicio(),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->categoriasService->findModel($id);

        if (Yii::$app->request->isPost) {
            $resultado = $this->categoriasService->actualizarCategoria($model, Yii::$app->request->post());
            if ($resultado && $resultado['exito']) {
                return $this->redirect(['index', 'status' => 'updated']);
            }
            $model = $resultado['model'] ?? $model;
        }

        $this->categoriasService->limpiarFlashes();
        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $resultado = $this->categoriasService->eliminarCategoria($id);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $resultado;
    }

    public function actionEstado($id)
    {
        $model = $this->categoriasService->findModel($id);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save(false);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['estado' => $model->estado];
    }
}
