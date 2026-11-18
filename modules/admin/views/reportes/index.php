<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\date\DatePicker;
use kartik\select2\Select2;

$this->title = 'Reportes de Ventas';
?>

<section class="content-header">
    <h1><i class="fa fa-bar-chart"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">

<!-- TARJETITAS RESUMEN -->
<div class="row cjafera sce2">
    <div class="col-sm-2">
        <div class="info-box bg-blue">
            <span class="info-box-icon"><i class="fa fa-list-ol"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Órdenes</span>
                <span class="info-box-number"><?= $cantidadOrdenes ?></span>
            </div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-flask"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">C. análisis</span>
                <span class="info-box-number"><?= $cantidadAnalisis ?></span>
            </div>
        </div>
    </div>
    
    <div class="col-sm-2">
        <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-tag"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Descuentos</span>
                <span class="info-box-number">Bs. <?= number_format($totalDescuentos, 2) ?></span>
            </div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-credit-card"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Cobrado</span>
                <span class="info-box-number">Bs. <?= number_format($totalCobrado, 2) ?></span>
            </div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-money"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Vendido</span>
                <span class="info-box-number">Bs. <?= number_format($totalVendido, 2) ?></span>
            </div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="info-box <?= $totalSaldo > 0 ? 'bg-red' : 'bg-green' ?>">
            <span class="info-box-icon"><i class="fa fa-exclamation-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Saldo Pendiente</span>
                <span class="info-box-number">Bs. <?= number_format($totalSaldo, 2) ?></span>
            </div>
        </div>
    </div>
  
</div>

<!-- NUEVO: desglose Efectivo / QR, mismo cálculo que "Total Cobrado"
     pero separado por método (suma monto_efectivo y monto_qr de las
     órdenes ya filtradas por fecha/usuario/doctor/examen/tipo_pago) -->
<div class="row cjafera sce2">
      <div class="col-sm-2">
        <div class="info-box <?= $ganancia >= 0 ? 'bg-green' : 'bg-red' ?>">
            <span class="info-box-icon"><i class="fa fa-line-chart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ganancia est.</span>
                <span class="info-box-number">Bs. <?= number_format($ganancia, 2) ?></span>
            </div>
        </div>
    </div>

    <div class="col-sm-2">
        <div class="info-box bg-teal">
            <span class="info-box-icon"><i class="fa fa-money"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Cobrado en Efectivo</span>
                <span class="info-box-number">Bs. <?= number_format($totalEfectivo, 2) ?></span>
            </div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="info-box bg-purple">
            <span class="info-box-icon"><i class="fa fa-qrcode"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Cobrado por QR</span>
                <span class="info-box-number">Bs. <?= number_format($totalQr, 2) ?></span>
            </div>
        </div>
    </div>
</div>

<!-- FILTROS -->
<div class="box box-default" style="margin-bottom:5px">
<div class="box-body">
<form method="get" action="<?= Url::to(['index']) ?>">
    <div class="row">
        <div class="col-sm-3">
            <label>Desde</label>
            <?= DatePicker::widget([
                'name'          => 'fecha_desde',
                'value'         => $fechaDesde,
                'language'      => 'es',
                'options'       => ['class' => 'form-control'],
                'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayBtn' => 'linked'],
            ]) ?>
        </div>
        <div class="col-sm-3">
            <label>Hasta</label>
            <?= DatePicker::widget([
                'name'          => 'fecha_hasta',
                'value'         => $fechaHasta,
                'language'      => 'es',
                'options'       => ['class' => 'form-control'],
                'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayBtn' => 'linked'],
            ]) ?>
        </div>
        <div class="col-sm-3">
            <label>Usuario</label>
            <?= Select2::widget([
                'name'          => 'idusuario',
                'value'         => $idusuario,
                'data'          => $usuarios,
                'options'       => ['placeholder' => 'Todos los usuarios'],
                'pluginOptions' => ['allowClear' => true],
            ]) ?>
        </div>
        <div class="col-sm-3">
            <label>Tipo de pago</label>
            <?= Select2::widget([
                'name'          => 'tipo_pago',
                'value'         => $tipoPago,
                'data'          => \app\models\Ordenes::getTiposPago(),
                'options'       => ['placeholder' => 'Todos (efectivo, QR y mixto)'],
                'pluginOptions' => ['allowClear' => true],
            ]) ?>
        </div>

      <div class="col-sm-3"> <br>
    <label>Tipo de Examen</label>
    <?= Select2::widget([
        'name'          => 'idproducto[]',
        'value'         => $idproducto,
        'data'          => $productos,
        'options'       => [
            'placeholder' => 'Todos',
            'multiple'    => true,
        ],
        'pluginOptions' => ['allowClear' => true],
    ]) ?>
</div>
<div class="col-sm-3"> <br>
    <label>Doctor</label>
    <?= Select2::widget([
        'name'          => 'iddoctor',
        'value'         => $iddoctor,
        'data'          => $doctores,
        'options'       => ['placeholder' => 'Todos'],
        'pluginOptions' => ['allowClear' => true],
    ]) ?>
</div>
<div class="col-sm-2"> <br>
    <label>Con descuento</label>
    <?= Select2::widget([
        'name'          => 'con_descuento',
        'value'         => $conDescuento,
        'data'          => ['1' => 'Sólo con descuento'],
        'options'       => ['placeholder' => 'Todos'],
        'pluginOptions' => ['allowClear' => true],
    ]) ?>
</div>
        <div class="col-sm-2" style="margin-top:25px"><br>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fa fa-search"></i> Filtrar
            </button>
        </div>
    </div>
</form>
</div>
</div>

<!-- GRILLA -->
<div class="box">
<div class="box-header with-border">
    <h3 class="box-title">Detalle de órdenes</h3>
    <div class="box-tools pull-right">
        <?= Html::a(
            '<i class="fa fa-file-excel-o"></i> Exportar Excel',
            Url::to(['exportar-excel',
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'idusuario'   => $idusuario,
                'tipo_pago'   => $tipoPago,
                        'iddoctor'    => $iddoctor,
        'idproducto'  => $idproducto,
        'con_descuento' => $conDescuento,
            ]),
            ['class' => 'btn btn-success btn-sm ldseq12']
        ) ?>
    </div>
</div>
<div class="box-body">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns'      => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'numero_referencia',
            'header'    => 'N° Ref',
            'value'     => fn($m) => $m->numero_referencia ?: '—',
        ],
        'paciente_nombre',
        [
            'header' => 'Doctor',
            'value'  => fn($m) => $m->doctor->nombre ?? '—',
        ],
        'fecha_registro',

        [
    'header' => 'Resultados',
    'format' => 'raw',
    'value'  => function($m) {
        if (empty($m->detalles)) return '<span class="text-muted">—</span>';
        $nombres = array_map(fn($d) => $d->producto->titulo ?? '(desconocido)', $m->detalles);
        $textoCompleto = implode(', ', array_unique($nombres));

        $limite = 100;
        if (mb_strlen($textoCompleto) <= $limite) {
            return Html::encode($textoCompleto);
        }

        $truncado = mb_substr($textoCompleto, 0, $limite);
        $ultimoEspacio = mb_strrpos($truncado, ' ');
        if ($ultimoEspacio !== false) {
            $truncado = mb_substr($truncado, 0, $ultimoEspacio);
        }

        return '<span class="resultados-wrap">'
             . '<span class="res-corto">' . Html::encode($truncado) . '… '
             . '<a href="javascript:void(0)" class="res-toggle">ver todo</a></span>'
             . '<span class="res-completo" style="display:none">' . Html::encode($textoCompleto) . ' '
             . '<a href="javascript:void(0)" class="res-toggle">ver menos</a></span>'
             . '</span>';
    },
],
        [
            'header' => 'Usuario',
            'value'  => fn($m) => $m->usuarioRegistro->nombrecompleto ?? '—',
        ],
        [
            'header' => 'Tipo pago',
            'value'  => fn($m) => $m->tipoPagoLabel,
        ],
        [
            'header'         => 'Efectivo',
            'contentOptions' => ['style' => 'text-align:right'],
            'value'          => fn($m) => ($m->monto_efectivo ?? 0) > 0 ? number_format($m->monto_efectivo, 2) : '—',
        ],
        [
            'header'         => 'QR',
            'contentOptions' => ['style' => 'text-align:right'],
            'value'          => fn($m) => ($m->monto_qr ?? 0) > 0 ? number_format($m->monto_qr, 2) : '—',
        ],
        [
            'header' => 'Estado',
            'format' => 'raw',
            'value'  => function($m) {
                $cls = match($m->estado_pago) {
                    'pagado'    => 'success',
                    'pendiente' => 'warning',
                    default     => 'default',
                };
                return '<span class="label label-'.$cls.'">'.$m->estadoPagoLabel.'</span>';
            },
        ],
        [
            'header' => 'Total',
            'value'  => fn($m) => number_format($m->monto_total, 2),
        ],
        [
            'header' => 'Descuento',
            'format' => 'raw',
            'value'  => function($m) {
                if (($m->descuento ?? 0) > 0)
                    return '<span class="text-warning">'.number_format($m->descuento, 2).'</span>';
                return '<span class="text-muted">0.00</span>';
            },
        ],
        [
            'header' => 'A pagar ',
            'value'  => fn($m) => number_format($m->monto_total - ($m->descuento ?? 0), 2),
        ],
       
        [
            'header' => 'Saldo',
            'format' => 'raw',
            'value'  => function($m) {
                if ($m->saldo > 0)
                    return '<span class="text-danger">'.number_format($m->saldo, 2).'</span>';
                return '<span class="text-success">0.00</span>';
            },
        ],
        [
            'class'    => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'buttons'  => [
                'view' => fn($url, $m) => Html::a(
                    '<i class="fa fa-eye"></i>',
                    Url::to(['/admin/ordenes/view', 'id' => $m->idorden]),
                    ['class' => 'btn btn-success btn-xs']
                ),
            ],
        ],
    ],
]); ?>
</div>
</div>

</section>

<?php
$this->registerJs("
    \$(document).on('click', '.res-toggle', function(e) {
        e.preventDefault();
        var wrap = \$(this).closest('.resultados-wrap');
        wrap.find('.res-corto, .res-completo').toggle();
    });
");
?>