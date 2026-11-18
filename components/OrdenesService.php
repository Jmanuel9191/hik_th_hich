<?php
namespace app\components;

use app\models\Ordenes;
use app\models\OrdenDetalle;
use app\models\OrdenPagos;
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

    /**
     * Redondea a 2 decimales. Se usa SIEMPRE antes de comparar montos
     * entre sí (>, <, ==), porque los floats de PHP arrastran ruido
     * binario (ej: 84.99 - 84.98 puede dar 0.0099999999999945 en vez
     * de 0.01 exacto), lo que hacía fallar comparaciones que deberían
     * dar igual. Nunca comparar montos sin pasarlos por acá primero.
     */
    private function redondear($valor)
    {
        return round((float)$valor, 2);
    }

    /**
     * true si dos montos son iguales dentro de un margen de medio
     * centavo — colchón extra para cualquier resta encadenada que se
     * nos haya escapado de redondear en el camino.
     */
    private function montosIguales($a, $b, $epsilon = 0.005)
    {
        return abs($this->redondear($a) - $this->redondear($b)) < $epsilon;
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

    /**
     * Recalcula monto_efectivo/monto_qr de la orden SUMANDO todos sus
     * pagos en orden_pagos (la fuente real de verdad ahora es esa
     * tabla). monto_pagado y tipo_pago los sigue armando solo
     * Ordenes::beforeValidate() a partir de esos dos, como ya hacía.
     * Este método es el que usan nuevaOrden(), actualizarPago() y
     * editarPago() para no repetir esta cuenta tres veces.
     */
    private function recalcularTotalesPago(Ordenes $model)
    {
        $sumas = OrdenPagos::find()
            ->select(['efectivo' => 'SUM(monto_efectivo)', 'qr' => 'SUM(monto_qr)'])
            ->where(['idorden' => $model->idorden])
            ->asArray()
            ->one();

        $model->monto_efectivo = $this->redondear($sumas['efectivo'] ?? 0);
        $model->monto_qr       = $this->redondear($sumas['qr'] ?? 0);

        $totalAPagar  = $this->redondear($model->monto_total - ($model->descuento ?? 0));
        $montoPagado  = $this->redondear($model->monto_efectivo + $model->monto_qr);

        // Si quedó a medio centavo o menos de saldar, lo tratamos como
        // saldado en 0 (evita saldos fantasma tipo "Bs. 0.0000000001").
        $diferencia = $this->redondear($totalAPagar - $montoPagado);
        $model->saldo = ($diferencia <= 0.005) ? 0 : $diferencia;

        $model->estado_pago  = ($model->saldo <= 0) ? 'pagado' : 'pendiente';

        return $model->save();
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

        // Guardamos aparte lo que vino del formulario de pago — lo vamos
        // a insertar como el "Pago 1" DESPUÉS de tener idorden, no antes.
        $efectivoInicial = $this->redondear($model->monto_efectivo);
        $qrInicial       = $this->redondear($model->monto_qr);

        if ($model->tipo_orden === 'cotizacion') {
            // Cotización: no se cobra, estado especial
            $model->monto_pagado   = 0;
            $model->monto_efectivo = 0;
            $model->monto_qr       = 0;
            $model->saldo          = $model->monto_total;
            $model->estado_pago    = 'cotizacion';
            $model->tipo_pago      = 'efectivo'; // valor por defecto, no importa
        } else {
            // Total a pagar = total menos el descuento aplicado
            $totalAPagar = $this->redondear($model->monto_total - ($model->descuento ?? 0));
            $pagoInicial = $this->redondear($efectivoInicial + $qrInicial);

            // Validación real (servidor): el pagado no puede superar el total a pagar
            if ($pagoInicial > $totalAPagar && !$this->montosIguales($pagoInicial, $totalAPagar)) {
                $this->alertService->agregarMensajeError(
                    'El monto pagado (Bs. ' . number_format($pagoInicial, 2) . ') '
                    . 'no puede superar el total a pagar (Bs. ' . number_format($totalAPagar, 2) . ').'
                );
                return ['exito' => false, 'model' => $model];
            }

            // La orden arranca "sin pagar" — el pago real se inserta como
            // fila en orden_pagos apenas tengamos idorden, más abajo.
            $model->monto_pagado   = 0;
            $model->monto_efectivo = 0;
            $model->monto_qr       = 0;
            $model->saldo          = $totalAPagar;
            $model->estado_pago    = ($totalAPagar <= 0) ? 'pagado' : 'pendiente';
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

            // NUEVO: si vino un pago inicial (efectivo y/o QR), se inserta
            // como el "Pago 1" en orden_pagos, y recién ahí se recalculan
            // los totales de la orden sumando esa tabla.
            if ($model->tipo_orden !== 'cotizacion' && ($efectivoInicial > 0 || $qrInicial > 0)) {
                $tipoPago = 'efectivo';
                if ($efectivoInicial > 0 && $qrInicial > 0) $tipoPago = 'mixto';
                elseif ($qrInicial > 0) $tipoPago = 'qr';

                $pago = new OrdenPagos();
                $pago->idorden            = $model->idorden;
                $pago->monto              = $this->redondear($efectivoInicial + $qrInicial);
                $pago->monto_efectivo     = $efectivoInicial;
                $pago->monto_qr           = $qrInicial;
                $pago->tipo_pago          = $tipoPago;
                $pago->fecha_pago         = $model->fecha_registro;
                $pago->idusuario_registro = $idusuario;
                if (!$pago->save()) throw new \Exception('Error al registrar el pago inicial.');

                if (!$this->recalcularTotalesPago($model)) {
                    throw new \Exception('Error al actualizar los totales de la orden.');
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

    /**
     * Registrar pago — YA NO edita/pisa el pago existente. Inserta
     * una fila nueva en orden_pagos ("Pago N") y recalcula los
     * totales de la orden sumando toda la tabla. El nombre del método
     * se mantiene igual (aunque ahora "agrega" en vez de "actualizar")
     * para no tener que tocar el controlador.
     */
    public function actualizarPago($model, $postData)
    {
        $efectivo = (float)($postData['Ordenes']['monto_efectivo'] ?? 0);
        $qr       = (float)($postData['Ordenes']['monto_qr'] ?? 0);
        $montoNuevoPago = $this->redondear($efectivo + $qr);

        if ($montoNuevoPago <= 0) {
            $this->alertService->agregarMensajeError('Ingresá un monto mayor a cero.');
            return ['exito' => false, 'model' => $model];
        }

        // Saldo pendiente ANTES de este pago nuevo (no el total de la orden)
        $totalAPagar = $this->redondear($model->monto_total - ($model->descuento ?? 0));
        $saldoAntes  = $this->redondear(max(0, $totalAPagar - (float)$model->monto_pagado));

        // Si el monto ingresado "empata" con el saldo (diferencia de
        // hasta medio centavo, típico ruido de float), se deja pasar
        // como si fuera exactamente el saldo — así se puede saldar la
        // cuenta sin que un centavo de redondeo lo bloquee.
        if ($montoNuevoPago > $saldoAntes && !$this->montosIguales($montoNuevoPago, $saldoAntes)) {
            $this->alertService->agregarMensajeError(
                'El monto pagado (Bs. ' . number_format($montoNuevoPago, 2) . ') '
                . 'no puede superar el saldo pendiente (Bs. ' . number_format($saldoAntes, 2) . ').'
            );
            return ['exito' => false, 'model' => $model];
        }

        $tipoPago = 'efectivo';
        if ($efectivo > 0 && $qr > 0) $tipoPago = 'mixto';
        elseif ($qr > 0) $tipoPago = 'qr';

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $pago = new OrdenPagos();
            $pago->idorden            = $model->idorden;
            $pago->monto              = $montoNuevoPago;
            $pago->monto_efectivo     = $this->redondear($efectivo);
            $pago->monto_qr           = $this->redondear($qr);
            $pago->tipo_pago          = $tipoPago;
            $pago->fecha_pago         = date('Y-m-d H:i:s');
            $pago->idusuario_registro = Yii::$app->user->id ?? null;
            if (!$pago->save()) throw new \Exception('Error al registrar el pago: ' . json_encode($pago->getErrors()));

            if (!$this->recalcularTotalesPago($model)) {
                throw new \Exception('Error al actualizar la orden.');
            }

            $transaction->commit();
            $this->alertService->agregarMensajeExito('Pago registrado correctamente.');
            return ['exito' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), 'ordenes');
            $this->alertService->agregarMensajeError('Error al registrar el pago.');
            return ['exito' => false, 'model' => $model];
        }
    }

    /**
     * Editar un pago — SOLO si es el último pago registrado de esa
     * orden (ver Resultados::esElUltimoPagoDeLaOrden). Los pagos
     * viejos, superados por uno más nuevo, quedan congelados para
     * siempre. Poner los montos en 0 "cancela" el pago sin borrar la
     * fila — queda el rastro de quién y cuándo lo editó.
     */
    public function editarPago($idpago, $postData)
    {
        $pago = OrdenPagos::findOne($idpago);
        if (!$pago) {
            throw new NotFoundHttpException('El pago solicitado no existe.');
        }

        if (!$pago->esElUltimoPagoDeLaOrden()) {
            $this->alertService->agregarMensajeError('Solo se puede editar el último pago registrado de la orden. Los pagos anteriores quedan congelados.');
            return ['exito' => false];
        }

        $model = Ordenes::findOne($pago->idorden);
        if (!$model) {
            throw new NotFoundHttpException('La orden asociada no existe.');
        }

        $efectivo     = (float)($postData['OrdenPagos']['monto_efectivo'] ?? 0);
        $qr           = (float)($postData['OrdenPagos']['monto_qr'] ?? 0);
        $montoEditado = $this->redondear($efectivo + $qr);

        // Cuánto se había pagado en TOTAL sin contar este pago puntual
        // — es el techo real contra el que hay que validar la edición.
        $totalAPagar       = $this->redondear($model->monto_total - ($model->descuento ?? 0));
        $pagadoSinEstePago = $this->redondear((float)$model->monto_pagado - (float)$pago->monto);
        $saldoDisponible   = $this->redondear(max(0, $totalAPagar - $pagadoSinEstePago));

        if ($montoEditado > $saldoDisponible && !$this->montosIguales($montoEditado, $saldoDisponible)) {
            $this->alertService->agregarMensajeError(
                'Ese monto (Bs. ' . number_format($montoEditado, 2) . ') '
                . 'no puede superar Bs. ' . number_format($saldoDisponible, 2) . '.'
            );
            return ['exito' => false];
        }

        $tipoPago = 'efectivo';
        if ($efectivo > 0 && $qr > 0) $tipoPago = 'mixto';
        elseif ($qr > 0) $tipoPago = 'qr';

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $pago->monto             = $montoEditado;
            $pago->monto_efectivo    = $this->redondear($efectivo);
            $pago->monto_qr          = $this->redondear($qr);
            $pago->tipo_pago         = $tipoPago;
            $pago->fecha_edicion     = date('Y-m-d H:i:s');
            $pago->idusuario_edicion = Yii::$app->user->id ?? null;
            if (!$pago->save()) throw new \Exception('Error al editar el pago: ' . json_encode($pago->getErrors()));

            if (!$this->recalcularTotalesPago($model)) {
                throw new \Exception('Error al actualizar la orden.');
            }

            $transaction->commit();
            $this->alertService->agregarMensajeExito('Pago editado correctamente.');
            return ['exito' => true, 'idorden' => $model->idorden];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), 'ordenes');
            $this->alertService->agregarMensajeError('Error al editar el pago.');
            return ['exito' => false];
        }
    }

    public function getPagosDeOrden($idorden)
    {
        return OrdenPagos::find()
            ->where(['idorden' => $idorden])
            ->orderBy(['idpago' => SORT_ASC])
            ->all();
    }

    /**
     * Cuando "Editar Orden" toca el pago, esto es lo que realmente lo
     * escribe — NUNCA se pisa ordenes.monto_efectivo/monto_qr directo.
     * - Si la orden todavía no tiene ningún pago: crea el primero.
     * - Si ya tiene pagos: ajusta el ÚLTIMO para que la suma total dé
     *   justo lo que se pidió acá (respeta la misma regla de "solo el
     *   último pago se puede tocar" que ya usa editarPago()).
     * Devuelve false y deja un mensaje de error si el monto pedido es
     * menor a lo que ya está en pagos anteriores al último (ahí hay
     * que corregir el historial a mano, no se puede "restar" solo).
     */
    private function sincronizarPagoDesdeEdicion(Ordenes $model, $nuevoEfectivoTotal, $nuevoQrTotal)
    {
        $nuevoEfectivoTotal = $this->redondear($nuevoEfectivoTotal);
        $nuevoQrTotal       = $this->redondear($nuevoQrTotal);

        $pagos = $this->getPagosDeOrden($model->idorden);

        if (empty($pagos)) {
            if ($nuevoEfectivoTotal <= 0 && $nuevoQrTotal <= 0) {
                return true; // no había pago y no se pidió ninguno, no hay nada que hacer
            }
            $tipoPago = 'efectivo';
            if ($nuevoEfectivoTotal > 0 && $nuevoQrTotal > 0) $tipoPago = 'mixto';
            elseif ($nuevoQrTotal > 0) $tipoPago = 'qr';

            $pago = new OrdenPagos();
            $pago->idorden            = $model->idorden;
            $pago->monto              = $this->redondear($nuevoEfectivoTotal + $nuevoQrTotal);
            $pago->monto_efectivo     = $nuevoEfectivoTotal;
            $pago->monto_qr           = $nuevoQrTotal;
            $pago->tipo_pago          = $tipoPago;
            $pago->fecha_pago         = date('Y-m-d H:i:s');
            $pago->idusuario_registro = Yii::$app->user->id ?? null;
            return $pago->save();
        }

        // Ya hay pagos: se ajusta el ÚLTIMO para que la suma de todos
        // dé exactamente lo pedido. Los anteriores no se tocan.
        $ultimo = end($pagos);
        $sumaAnteriorEfectivo = 0;
        $sumaAnteriorQr       = 0;
        foreach ($pagos as $p) {
            if ($p->idpago === $ultimo->idpago) continue;
            $sumaAnteriorEfectivo += (float)$p->monto_efectivo;
            $sumaAnteriorQr       += (float)$p->monto_qr;
        }
        $sumaAnteriorEfectivo = $this->redondear($sumaAnteriorEfectivo);
        $sumaAnteriorQr       = $this->redondear($sumaAnteriorQr);

        $efectivoUltimo = $this->redondear($nuevoEfectivoTotal - $sumaAnteriorEfectivo);
        $qrUltimo       = $this->redondear($nuevoQrTotal - $sumaAnteriorQr);

        // Tolerancia de medio centavo antes de considerar "negativo" un
        // resultado que en realidad es cero con ruido de float.
        if ($efectivoUltimo < -0.005 || $qrUltimo < -0.005) {
            $this->alertService->agregarMensajeError(
                'El monto de pago que pusiste es menor a lo que ya está registrado en pagos anteriores '
                . '(Bs. ' . number_format($sumaAnteriorEfectivo + $sumaAnteriorQr, 2) . '). '
                . 'Para corregir eso, editá el último pago desde el historial de la orden.'
            );
            return false;
        }
        $efectivoUltimo = max(0, $efectivoUltimo);
        $qrUltimo       = max(0, $qrUltimo);

        $tipoPago = 'efectivo';
        if ($efectivoUltimo > 0 && $qrUltimo > 0) $tipoPago = 'mixto';
        elseif ($qrUltimo > 0) $tipoPago = 'qr';

        $ultimo->monto             = $this->redondear($efectivoUltimo + $qrUltimo);
        $ultimo->monto_efectivo    = $efectivoUltimo;
        $ultimo->monto_qr          = $qrUltimo;
        $ultimo->tipo_pago         = $tipoPago;
        $ultimo->fecha_edicion     = date('Y-m-d H:i:s');
        $ultimo->idusuario_edicion = Yii::$app->user->id ?? null;
        return $ultimo->save();
    }

    /**
     * Edición completa de una orden ya creada — SOLO ADMIN (el controlador
     * es el que valida el permiso antes de llamar a esto).
     *
     * Permite: datos del paciente/doctor, agregar o quitar análisis, y la
     * forma de pago (efectivo/QR/descuento), todo en un solo guardado.
     * El pago pasa por sincronizarPagoDesdeEdicion() — nunca se escribe
     * directo en ordenes.monto_efectivo/monto_qr.
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

        $efectivoTotal = $this->redondear($postData['Ordenes']['monto_efectivo'] ?? 0);
        $qrTotal       = $this->redondear($postData['Ordenes']['monto_qr'] ?? 0);
        $montoPagado   = $this->redondear($efectivoTotal + $qrTotal);

        $descuento   = (float)($postData['Ordenes']['descuento'] ?? ($model->descuento ?? 0));
        $totalAPagar = $this->redondear($nuevoTotal - $descuento);

        if ($montoPagado > $totalAPagar && !$this->montosIguales($montoPagado, $totalAPagar)) {
            $this->alertService->agregarMensajeError(
                'El monto pagado (Bs. ' . number_format($montoPagado, 2) . ') '
                . 'no puede superar el total a pagar (Bs. ' . number_format($totalAPagar, 2) . ').'
            );
            return ['exito' => false, 'model' => $model];
        }

        $model->monto_total = $nuevoTotal;
        $model->descuento    = $descuento;

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

            // Pago: nunca directo, siempre a través del helper que
            // respeta "solo el último pago se puede tocar".
            if (!$this->sincronizarPagoDesdeEdicion($model, $efectivoTotal, $qrTotal)) {
                throw new \Exception('Error al sincronizar el pago.');
            }
            if (!$this->recalcularTotalesPago($model)) {
                throw new \Exception('Error al actualizar los totales de la orden.');
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