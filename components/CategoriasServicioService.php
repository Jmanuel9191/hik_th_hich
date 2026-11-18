<?php
namespace app\components;

use app\models\CategoriasServicio;
use app\models\CategoriasServicioSearch;
use Yii;
use yii\web\NotFoundHttpException;

class CategoriasServicioService implements InterfaceCategoriasServicio
{
    private $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function listCategorias($queryParams)
    {
        $searchModel  = new CategoriasServicioSearch();
        $dataProvider = $searchModel->search($queryParams);
        return [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ];
    }

    public function findModel($id)
    {
        $model = CategoriasServicio::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('La categoría no existe.');
        }
        // No permitir editar productosg
        if ($model->alias === 'productosg') {
            throw new NotFoundHttpException('Esta categoría no se puede editar.');
        }
        return $model;
    }

    public function nuevaCategoria($postData)
    {
        $model = new CategoriasServicio();

        if ($model->load($postData) && $model->validate()) {
            // Generar alias automáticamente desde el nombre si está vacío
            if (empty($model->alias)) {
                $model->alias = $this->generarAlias($model->nombre);
            }
            if ($model->save()) {
                $this->alertService->agregarMensajeExito('Categoría creada correctamente.');
                return ['exito' => true];
            }
            $this->alertService->agregarMensajeError('Error al guardar.');
            return ['exito' => false, 'model' => $model];
        }

        $this->alertService->agregarMensajeError('Revisá los datos del formulario.');
        return ['exito' => false, 'model' => $model];
    }

    public function actualizarCategoria($model, $postData)
    {
        if ($model->load($postData) && $model->validate()) {
            if (empty($model->alias)) {
                $model->alias = $this->generarAlias($model->nombre);
            }
            if ($model->save()) {
                $this->alertService->agregarMensajeExito('Categoría actualizada correctamente.');
                return ['exito' => true];
            }
            $this->alertService->agregarMensajeError('Error al actualizar.');
            return ['exito' => false, 'model' => $model];
        }

        $this->alertService->agregarMensajeError('Revisá los datos del formulario.');
        return ['exito' => false, 'model' => $model];
    }

    public function eliminarCategoria($id)
    {
        $model = $this->findModel($id);

        // Verificar que no tenga productos vinculados
        $cantProductos = \app\models\Productos::find()
            ->where(['idcategoria' => $id])
            ->count();

        if ($cantProductos > 0) {
            return [
                'exito'   => false,
                'mensaje' => "No se puede eliminar: tiene {$cantProductos} servicio(s) vinculado(s). Reasignálos primero.",
            ];
        }

        if ($model->delete()) {
            return ['exito' => true, 'mensaje' => 'Categoría eliminada correctamente.'];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo eliminar.'];
    }

    public function limpiarFlashes()
    {
        Yii::$app->session->removeAllFlashes();
    }

    private function generarAlias($nombre)
    {
        $alias = strtolower(trim($nombre));
        $alias = preg_replace('/[^a-z0-9]+/', '', $alias);
        return $alias ?: 'categoria' . time();
    }
}
