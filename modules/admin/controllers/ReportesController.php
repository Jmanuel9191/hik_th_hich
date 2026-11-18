<?php
namespace app\modules\admin\controllers;

use app\components\InterfacePermisosService;
use app\models\Ordenes;
use app\models\OrdenPagos;
use Yii;
use yii\data\ActiveDataProvider;

class ReportesController extends BaseController
{
    private $permisosService;

    public function __construct($id, $module, InterfacePermisosService $permisosService, $config = [])
    {
        $this->permisosService = $permisosService;
        parent::__construct($id, $module, $config);
    }

   public function actionIndex()
{
    $this->permisosService->verificarAdministrador();

    $fechaDesde = Yii::$app->request->get('fecha_desde', date('Y-m-01'));
    $fechaHasta = Yii::$app->request->get('fecha_hasta', date('Y-m-d'));
    $idusuario  = Yii::$app->request->get('idusuario', null);
    $tipoPago   = Yii::$app->request->get('tipo_pago', null);
    $iddoctor   = Yii::$app->request->get('iddoctor', null);       // <-- NUEVO
    $idproducto = Yii::$app->request->get('idproducto', null);     // <-- NUEVO (array o único)
    $conDescuento = Yii::$app->request->get('con_descuento', null); // <-- NUEVO


    $query = Ordenes::find()
        ->with(['detalles', 'doctor', 'usuarioRegistro'])
        
        ->where(['tipo_orden' => 'orden'])
        ->andWhere(['>=', 'DATE(fecha_registro)', $fechaDesde])
        ->andWhere(['<=', 'DATE(fecha_registro)', $fechaHasta]);

    if ($idusuario) {
        $query->andWhere(['idusuario_registro' => $idusuario]);
    }
    if ($tipoPago === 'efectivo') {
        // Efectivo puro + mixtas (toda orden que tenga algo de efectivo)
        $query->andWhere(['>', 'monto_efectivo', 0]);
    } elseif ($tipoPago === 'qr') {
        // QR puro + mixtas (toda orden que tenga algo de QR)
        $query->andWhere(['>', 'monto_qr', 0]);
    } elseif ($tipoPago === 'mixto') {
        // Mixto exacto — no cambia
        $query->andWhere(['tipo_pago' => 'mixto']);
    }

     if ($iddoctor) {                                                // <-- NUEVO
        $query->andWhere(['iddoctor' => $iddoctor]);
    }
    if (!empty($idproducto)) {                                      // <-- NUEVO
        $query->andWhere([
            'idorden' => \app\models\OrdenDetalle::find()
                ->select('idorden')
                ->where(['idproducto' => $idproducto])
                ->distinct(),
        ]);
    }

    if ($conDescuento === '1') {                                    // <-- NUEVO
        $query->andWhere(['>', 'descuento', 0]);
    }

    $ordenes          = (clone $query)->all();
$totalVendido     = 0;
$totalCosto       = 0;
$totalDescuentos  = 0;
$totalSaldo       = 0;           // ← cuánto de lo vendido todavía no se cobró
$cantidadOrdenes  = count($ordenes);
$cantidadAnalisis = 0;

foreach ($ordenes as $orden) {
    $totalVendido    += (float)($orden->monto_total - ($orden->descuento ?? 0));
    $totalDescuentos += (float)($orden->descuento ?? 0);
    $totalSaldo      += (float)($orden->saldo ?? 0);
    foreach ($orden->detalles as $det) {
        $totalCosto += (float)$det->precio_costo;
        $cantidadAnalisis++;
    }
}

$ganancia = $totalVendido - $totalCosto;

// NUEVO: "Cobrado" (Total Cobrado, Efectivo, QR) se calcula sumando
// orden_pagos filtrado por fecha_pago — el día en que la plata
// REALMENTE entró — no por fecha_registro de la orden. Un pago hecho
// el 5/9 de una orden creada el 2/9 tiene que sumar al reporte del
// 5/9, no al del 2/9. Mismos filtros de doctor/examen/usuario/
// descuento que la query de arriba, para que el desglose sea
// consistente con lo que se ve en la grilla.
$queryPagos = OrdenPagos::find()
    ->joinWith('orden')
    ->where(['ordenes.tipo_orden' => 'orden'])
    ->andWhere(['>=', 'DATE(orden_pagos.fecha_pago)', $fechaDesde])
    ->andWhere(['<=', 'DATE(orden_pagos.fecha_pago)', $fechaHasta]);

if ($idusuario) {
    $queryPagos->andWhere(['ordenes.idusuario_registro' => $idusuario]);
}
if ($iddoctor) {
    $queryPagos->andWhere(['ordenes.iddoctor' => $iddoctor]);
}
if (!empty($idproducto)) {
    $queryPagos->andWhere([
        'ordenes.idorden' => \app\models\OrdenDetalle::find()
            ->select('idorden')
            ->where(['idproducto' => $idproducto])
            ->distinct(),
    ]);
}
if ($conDescuento === '1') {
    $queryPagos->andWhere(['>', 'ordenes.descuento', 0]);
}
// Acá "tipo_pago" filtra el PAGO individual (más preciso que el
// agregado de la orden, que ya no representa "lo cobrado en el rango").
if ($tipoPago === 'efectivo') {
    $queryPagos->andWhere(['>', 'orden_pagos.monto_efectivo', 0]);
} elseif ($tipoPago === 'qr') {
    $queryPagos->andWhere(['>', 'orden_pagos.monto_qr', 0]);
} elseif ($tipoPago === 'mixto') {
    $queryPagos->andWhere(['orden_pagos.tipo_pago' => 'mixto']);
}

$totalCobrado  = (clone $queryPagos)->sum('orden_pagos.monto') ?? 0;
$totalEfectivo = (clone $queryPagos)->sum('orden_pagos.monto_efectivo') ?? 0;
$totalQr       = (clone $queryPagos)->sum('orden_pagos.monto_qr') ?? 0;

    $usuarios = \yii\helpers\ArrayHelper::map(
        \app\models\Usuarios::find()->orderBy('nombrecompleto')->all(),
        'idusuario', 'nombrecompleto'
    );


    $doctores = \yii\helpers\ArrayHelper::map(                      // <-- NUEVO
        \app\models\Doctores::find()->orderBy('nombre')->all(),
        'iddoctor', 'nombre'
    );

    $productos = \yii\helpers\ArrayHelper::map(                     // <-- NUEVO
        \app\models\Productos::find()->orderBy('titulo')->all(),
        'idproducto', 'titulo'
    );

    $dataProvider = new ActiveDataProvider([
        'query'      => $query,
        'pagination' => ['pageSize' => 50],
        'sort'       => ['defaultOrder' => ['fecha_registro' => SORT_DESC]],
    ]);

    return $this->render('index', [
        'dataProvider'     => $dataProvider,
        'totalDescuentos'  => $totalDescuentos,
        'totalEfectivo'    => $totalEfectivo,    // <-- NUEVO
        'totalQr'          => $totalQr,          // <-- NUEVO
        'totalSaldo'       => $totalSaldo,       // <-- NUEVO
        'fechaDesde'       => $fechaDesde,
        'fechaHasta'       => $fechaHasta,
        'idusuario'        => $idusuario,
           'iddoctor'         => $iddoctor,       // <-- NUEVO
        'idproducto'       => $idproducto,     // <-- NUEVO
        'conDescuento'     => $conDescuento,   // <-- NUEVO
        'doctores'         => $doctores,       // <-- NUEVO
        'productos'        => $productos,      // <-- NUEVO
        'tipoPago'         => $tipoPago,
        'usuarios'         => $usuarios,
        'totalVendido'     => $totalVendido,
        'totalCobrado'     => $totalCobrado,
        'totalCosto'       => $totalCosto,
        'ganancia'         => $ganancia,
        'cantidadOrdenes'  => $cantidadOrdenes,
        'cantidadAnalisis' => $cantidadAnalisis,
    ]);
}

  public function actionExportarExcel()
{
    $this->permisosService->verificarAdministrador();

    $fechaDesde = Yii::$app->request->get('fecha_desde', date('Y-m-01'));
    $fechaHasta = Yii::$app->request->get('fecha_hasta', date('Y-m-d'));
    $idusuario  = Yii::$app->request->get('idusuario', null);
    $tipoPago   = Yii::$app->request->get('tipo_pago', null);
    $idproducto = Yii::$app->request->get('idproducto', null);   // <-- puede ser array o null
    $conDescuento = Yii::$app->request->get('con_descuento', null); // <-- NUEVO

    $query = Ordenes::find()
        ->with(['detalles.producto', 'doctor', 'usuarioRegistro'])
        ->where(['tipo_orden' => 'orden'])
        ->andWhere(['>=', 'DATE(fecha_registro)', $fechaDesde])
        ->andWhere(['<=', 'DATE(fecha_registro)', $fechaHasta])
        ->orderBy(['fecha_registro' => SORT_DESC]);

    if ($idusuario) {
        $query->andWhere(['idusuario_registro' => $idusuario]);
    }
    if ($tipoPago === 'efectivo') {
        $query->andWhere(['>', 'monto_efectivo', 0]);
    } elseif ($tipoPago === 'qr') {
        $query->andWhere(['>', 'monto_qr', 0]);
    } elseif ($tipoPago === 'mixto') {
        $query->andWhere(['tipo_pago' => 'mixto']);
    }
    if (!empty($idproducto)) {
        $query->andWhere([
            'idorden' => \app\models\OrdenDetalle::find()
                ->select('idorden')
                ->where(['idproducto' => $idproducto])
                ->distinct(),
        ]);
    }
    if ($conDescuento === '1') {                                    // <-- NUEVO
        $query->andWhere(['>', 'descuento', 0]);
    }

    $ordenes  = $query->all();
    $filename = 'reporte_ventas_' . $fechaDesde . '_' . $fechaHasta . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para acentos en Excel

    fputcsv($out, [
        'N° Orden', 'N° Ref', 'Paciente', 'Doctor', 'Usuario', 'Resultados',
        'Tipo Pago', 'Efectivo (Bs)', 'QR (Bs)', 'Estado', 'Total (Bs)', 'Descuento (Bs)', 'A pagar (Bs)', 'Pagado (Bs)', 'Saldo (Bs)', 'Fecha'
    ], ';');

    foreach ($ordenes as $orden) {
        $descuento   = $orden->descuento ?? 0;
        $totalAPagar = $orden->monto_total - $descuento;

        $resultados = '—';
        if (!empty($orden->detalles)) {
            $nombres = array_map(fn($d) => $d->producto->titulo ?? '(desconocido)', $orden->detalles);
            $resultados = implode(', ', array_unique($nombres));
        }

        fputcsv($out, [
            $orden->idorden,
            $orden->numero_referencia ?: '—',
            $orden->paciente_nombre,
            $orden->doctor->nombre ?? '—',
            $orden->usuarioRegistro->nombrecompleto ?? '—',
            $resultados,
            $orden->tipoPagoLabel,
            number_format($orden->monto_efectivo ?? 0, 2),   // <-- NUEVO
            number_format($orden->monto_qr ?? 0, 2),         // <-- NUEVO
            $orden->estadoPagoLabel,
            number_format($orden->monto_total, 2),
            $descuento > 0 ? number_format($descuento, 2) : '0.00',
            number_format($totalAPagar, 2),
            number_format($orden->monto_pagado, 2),
            number_format($orden->saldo, 2),
            date('d/m/Y H:i', strtotime($orden->fecha_registro)),
        ], ';');
    }

    fclose($out);
    Yii::$app->end();
}
}