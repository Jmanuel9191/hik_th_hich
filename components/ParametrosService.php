<?php

namespace app\components;

use app\models\Parametros;
use app\models\ParametrosSearch;
use Yii;
use yii\web\NotFoundHttpException;

class ParametrosService implements InterfaceParametros
{
    private $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function listParametros($queryParams)
    {
        $searchModel = new ParametrosSearch();
        $dataProvider = $searchModel->search($queryParams);

        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];
    }

    public function findModel($id)
    {
        if (($model = Parametros::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('El parámetro solicitado no existe.');
    }

    public function nuevoParametro($postData)
    {
        $model = new Parametros();

        if ($model->load($postData) && $model->validate()) {
            if ($model->save()) {
                $this->alertService->agregarMensajeExito('Parámetro creado correctamente.');
                return ['exito' => true];
            }
            $this->alertService->agregarMensajeError('Error al guardar. Inténtelo más tarde.');
            return ['exito' => false, 'model' => $model];
        }

        $this->alertService->agregarMensajeError('Por favor revisá los datos del formulario.');
        return ['exito' => false, 'model' => $model];
    }

    public function actualizarParametro($model, $postData)
    {
        if ($model->load($postData) && $model->validate()) {
            if ($model->save()) {
                $this->alertService->agregarMensajeExito('Parámetro actualizado correctamente.');
                return ['exito' => true];
            }
            $this->alertService->agregarMensajeError('Error al actualizar. Inténtelo más tarde.');
            return ['exito' => false, 'model' => $model];
        }

        $this->alertService->agregarMensajeError('Por favor revisá los datos del formulario.');
        return ['exito' => false, 'model' => $model];
    }

    public function eliminarParametro($id)
    {
        $model = $this->findModel($id);
        $cantidad = $model->getCantidadProductos();

        // Protección: si está en uso, no lo borramos en silencio
        if ($cantidad > 0) {
            $nombres = implode(', ', array_map(fn($p) => $p->titulo, $model->productos));
            return [
                'exito' => false,
                'mensaje' => "No se puede eliminar: este parámetro está en uso en {$cantidad} producto(s) ({$nombres}). Quitalo de esos productos primero.",
            ];
        }

        if ($model->delete()) {
            return ['exito' => true, 'mensaje' => 'Parámetro eliminado correctamente.'];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo eliminar el parámetro.'];
    }


    public function agregarAProductos($idparametro, $productosIds)
{
    foreach ((array)$productosIds as $idproducto) {
        $yaExiste = \app\models\ProductoParametro::findOne(['idproducto' => $idproducto, 'idparametro' => $idparametro]);
        if ($yaExiste) continue; // ya estaba vinculado, no lo duplicamos

        $maxOrden = (int)(\app\models\ProductoParametro::find()
            ->where(['idproducto' => $idproducto])
            ->max('orden'));

        $pp = new \app\models\ProductoParametro();
        $pp->idproducto = $idproducto;
        $pp->idparametro = $idparametro;
        $pp->orden = $maxOrden + 1;
        $pp->save();
    }

    $this->alertService->agregarMensajeExito('Vinculado correctamente.');
    return ['exito' => true];
}

public function quitarDeProducto($idparametro, $idproducto)
{
    $eliminado = \app\models\ProductoParametro::deleteAll(['idparametro' => $idparametro, 'idproducto' => $idproducto]);
    return [
        'exito' => $eliminado > 0,
        'mensaje' => $eliminado > 0 ? 'Quitado correctamente.' : 'No se encontró ese vínculo.',
    ];
}





    public function limpiarFlashes()
    {
        Yii::$app->session->removeFlash('flash'); // <-- este es el importante
        Yii::$app->session->removeFlash('success');
        Yii::$app->session->removeFlash('error');
        Yii::$app->session->removeFlash('warning');
        Yii::$app->session->removeFlash('info');
    }


}