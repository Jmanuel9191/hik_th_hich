<?php
namespace app\components;

use app\models\Ordenes;
use app\models\OrdenDetalle;
use app\models\OrdenesSearch;
use Yii;
use yii\web\NotFoundHttpException;

class OrdenesService implements InterfaceOrdenes
{
    private $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function listOrdenes($queryParams, $soloOrdenes = true)
    {
        $searchModel  = new OrdenesSearch();
        $dataProvider = $searchModel->search($queryParams, $soloOrdenes);

        return [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ];
    }

    public function findModel($id)
    {
        if (($model = Ordenes::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('La orden solicitada no existe.');
    }

   public function nuevaOrden($postData, $idusuario)
{
    $model = new Ordenes();

    if (!$model->load($postData) || !$model->validate()) {
        $this->alertService->agregarMensajeError('Revisá los datos del formulario.');
        return ['exito' => false, 'model' => $model];
    }

    $model->idusuario_registro = $idusuario;
    $model->fecha_registro     = date('Y-m-d H:i:s');

    if ($model->tipo_orden === 'cotizacion') {
        // Cotización: no se cobra, estado especial
        $model->monto_pagado = 0;
        $model->saldo        = $model->monto_total;
        $model->estado_pago  = 'cotizacion';
        $model->tipo_pago    = 'efectivo'; // no importa en cotización
    } else {
        // Total a pagar = total menos el descuento aplicado
        $totalAPagar = $model->monto_total - ($model->descuento ?? 0);

        // Validación real (servidor): el pagado no puede superar el total a pagar
        if ($model->monto_pagado > $totalAPagar) {
            $this->alertService->agregarMensajeError(
                'El monto pagado (Bs. ' . number_format($model->monto_pagado, 2) . ') '
                . 'no puede superar el total a pagar (Bs. ' . number_format($totalAPagar, 2) . ').'
            );
            return ['exito' => false, 'model' => $model];
        }

        $model->saldo        = max(0, $totalAPagar - $model->monto_pagado);
        $model->estado_pago  = ($model->saldo <= 0) ? 'pagado' : 'pendiente';
    }

    $detalles = $postData['detalles'] ?? [];
    
    if (empty($detalles)) {
        $this->alertService->agregarMensajeError('Debés agregar al menos un servicio.');
        return ['exito' => false, 'model' => $model];
    }

    $transaction = Yii::$app->db->beginTransaction();
    try {
        if (!$model->save()) throw new \Exception('Error al guardar la orden.');

        foreach ($detalles as $fila) {
            $idproducto = (int)($fila['idproducto'] ?? 0);
            $precio     = (float)($fila['precio']     ?? 0);
            if ($idproducto <= 0 || $precio <= 0) continue;

            $producto = \app\models\Productos::findOne($idproducto);

            $detalle               = new OrdenDetalle();
            $detalle->idorden      = $model->idorden;
            $detalle->idproducto   = $idproducto;
            $detalle->precio       = $precio;
            $detalle->precio_costo = $producto ? (float)($producto->preciooferta ?? 0) : 0;
            $detalle->subtotal     = $precio;
            if (!$detalle->save()) throw new \Exception('Error al guardar el detalle.');

            // NUEVO: descontar materiales solo si es una orden real, NUNCA en cotizaciones
            // NUEVO: descontar materiales solo si es una orden real, NUNCA en cotizaciones
if ($model->tipo_orden !== 'cotizacion') {
    $materialesVinculados = \app\models\ProductoMaterial::find()
        ->where(['idproducto_servicio' => $idproducto])
        ->all();

    foreach ($materialesVinculados as $vm) {
        $detalleDescuento = \app\models\InventarioLote::descontarFEFO($vm->idproducto_material, $vm->cantidad, 0, null, true);

        if ($detalleDescuento !== false) {
            // Registrar el movimiento en el historial (tabla Inventario), igual que una salida manual
            $movInventario = new \app\models\Inventario();
            $movInventario->idproducto     = $vm->idproducto_material;
            $movInventario->movimiento     = $vm->cantidad;
            $movInventario->estado         = 0; // Salida
            $movInventario->fecha_registro = date('Y-m-d H:i:s');
            $movInventario->detalle        = 'Descuento automático - Orden #' . $model->idorden;
            $movInventario->setSaldo();
            if ($movInventario->save()) {
                \app\models\InventarioLoteMovimiento::registrar($movInventario->idinventario, $detalleDescuento);
            }
        }
    }
}
        }

        $transaction->commit();
        $tipo = $model->esCotizacion() ? 'Cotización' : 'Orden';
        $this->alertService->agregarMensajeExito("{$tipo} registrada correctamente.");
        return ['exito' => true, 'idorden' => $model->idorden];

    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::error($e->getMessage(), 'ordenes');
        $this->alertService->agregarMensajeError('Error interno. Inténtelo de nuevo.');
        return ['exito' => false, 'model' => $model];
    }
}

    public function actualizarPago($model, $postData)
{
    // Antes: leía 'monto_pagado' y 'tipo_pago' directo del POST.
    // Ahora: lee los dos montos divididos; monto_pagado y tipo_pago
    // se recalculan solos en Ordenes::beforeValidate() al hacer save().
    $efectivo    = (float)($postData['Ordenes']['monto_efectivo'] ?? 0);
    $qr          = (float)($postData['Ordenes']['monto_qr'] ?? 0);
    $montoPagado = $efectivo + $qr;

    // Total a pagar = total menos el descuento aplicado (si hay)
    $totalAPagar = $model->monto_total - ($model->descuento ?? 0);

    // Validación real (servidor): el pagado no puede superar el total a pagar
    if ($montoPagado > $totalAPagar) {
        $this->alertService->agregarMensajeError(
            'El monto pagado (Bs. ' . number_format($montoPagado, 2) . ') '
            . 'no puede superar el total a pagar (Bs. ' . number_format($totalAPagar, 2) . ').'
        );
        return ['exito' => false, 'model' => $model];
    }

    $model->monto_efectivo = $efectivo;
    $model->monto_qr       = $qr;
    // monto_pagado y tipo_pago: los arma solo Ordenes::beforeValidate()
    $model->saldo        = max(0, $totalAPagar - $montoPagado);
    $model->estado_pago  = ($model->saldo <= 0) ? 'pagado' : 'pendiente';

    if ($model->save()) {
        $this->alertService->agregarMensajeExito('Pago actualizado correctamente.');
        return ['exito' => true];
    }

    $this->alertService->agregarMensajeError('Error al actualizar el pago.');
    return ['exito' => false, 'model' => $model];
}

    /**
     * Edición completa de una orden ya creada — SOLO ADMIN (el controlador
     * es el que valida el permiso antes de llamar a esto).
     *
     * Permite: datos del paciente/doctor, agregar o quitar análisis, y la
     * forma de pago (efectivo/QR/descuento), todo en un solo guardado.
     *
     * IMPORTANTE — lo que esto NO hace: no toca el inventario. Cuando se
     * crea una orden nueva (nuevaOrden), cada análisis descuenta stock de
     * materiales automáticamente. Acá, si agregás un análisis nuevo al
     * editar, NO se descuenta stock — habría que sumarlo a mano si lo
     * necesitás, para no arriesgarme a descontar mal sin ver primero cómo
     * funciona ese mecanismo en detalle.
     */
    public function actualizarOrden($model, $postData)
    {
        if (!$model->load($postData)) {
            $this->alertService->agregarMensajeError('Revisá los datos del formulario.');
            return ['exito' => false, 'model' => $model];
        }

        // Nueva lista de análisis: [idproducto => precio]
        $detallesPost = $postData['detalles'] ?? [];
        $nuevosItems  = [];
        foreach ($detallesPost as $fila) {
            $idp    = (int)($fila['idproducto'] ?? 0);
            $precio = (float)($fila['precio'] ?? 0);
            if ($idp > 0 && $precio > 0) {
                $nuevosItems[$idp] = $precio;
            }
        }

        if (empty($nuevosItems)) {
            $this->alertService->agregarMensajeError('La orden debe tener al menos un análisis.');
            return ['exito' => false, 'model' => $model];
        }

        $nuevoTotal = array_sum($nuevosItems);

        // Pago: mismo criterio que crear/pagar — se recalcula todo del
        // lado del servidor a partir de lo que se aplicó realmente.
        $efectivo    = (float)($postData['Ordenes']['monto_efectivo'] ?? 0);
        $qr          = (float)($postData['Ordenes']['monto_qr'] ?? 0);
        $montoPagado = $efectivo + $qr;

        $descuento   = (float)($postData['Ordenes']['descuento'] ?? ($model->descuento ?? 0));
        $totalAPagar = $nuevoTotal - $descuento;

        if ($montoPagado > $totalAPagar) {
            $this->alertService->agregarMensajeError(
                'El monto pagado (Bs. ' . number_format($montoPagado, 2) . ') '
                . 'no puede superar el total a pagar (Bs. ' . number_format($totalAPagar, 2) . ').'
            );
            return ['exito' => false, 'model' => $model];
        }

        $model->monto_total    = $nuevoTotal;
        $model->descuento      = $descuento;
        $model->monto_efectivo = $efectivo;
        $model->monto_qr       = $qr;
        // monto_pagado y tipo_pago: los arma solo Ordenes::beforeValidate()
        $model->saldo        = max(0, $totalAPagar - $montoPagado);
        $model->estado_pago  = ($model->saldo <= 0) ? 'pagado' : 'pendiente';

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save()) {
                throw new \Exception('Error al guardar la orden: ' . json_encode($model->getErrors()));
            }

            // Sincronizar orden_detalle: borrar lo que ya no está, agregar lo nuevo
            $existentes    = OrdenDetalle::find()->where(['idorden' => $model->idorden])->all();
            $idsExistentes = array_map(fn($d) => (int)$d->idproducto, $existentes);

            foreach ($existentes as $det) {
                if (!isset($nuevosItems[(int)$det->idproducto])) {
                    if (!$det->delete()) throw new \Exception('Error al quitar un análisis.');
                }
            }

            foreach ($nuevosItems as $idproducto => $precio) {
                if (in_array($idproducto, $idsExistentes)) continue; // ya estaba, no se toca

                $producto = \app\models\Productos::findOne($idproducto);

                $det               = new OrdenDetalle();
                $det->idorden      = $model->idorden;
                $det->idproducto   = $idproducto;
                $det->precio       = $precio;
                $det->precio_costo = $producto ? (float)($producto->preciooferta ?? 0) : 0;
                $det->subtotal     = $precio;
                if (!$det->save()) throw new \Exception('Error al agregar un análisis.');
            }

            $transaction->commit();
            $this->alertService->agregarMensajeExito('Orden actualizada correctamente.');
            return ['exito' => true, 'idorden' => $model->idorden];

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), 'ordenes');
            $this->alertService->agregarMensajeError('Error interno. Inténtelo de nuevo.');
            return ['exito' => false, 'model' => $model];
        }
    }

    public function eliminarOrden($id)
    {
        $model = $this->findModel($id);

        // Cotizaciones se eliminan sin restricción
        // Órdenes pagadas no se deben eliminar
        if (!$model->esCotizacion() && $model->estado_pago === 'pagado') {
            return [
                'exito'   => false,
                'mensaje' => 'No se puede eliminar una orden ya pagada.',
            ];
        }

        if ($model->delete()) {
            return ['exito' => true, 'mensaje' => 'Eliminado correctamente.'];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo eliminar.'];
    }

    public function limpiarFlashes()
    {
        Yii::$app->session->removeAllFlashes();
    }
}