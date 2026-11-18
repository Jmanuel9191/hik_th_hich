<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'INICIO';

?>

<section class="content-header">
    <h1>
        <i class="fa fa-dashboard"></i> Panel Principal
    </h1>
</section>

<section class="content">

<!-- ACCESO RÁPIDO -->
<div class="row" style="margin-bottom:10px">
    <div class="col-xs-12 mf-20">
        <?= Html::a('<i class="fa fa-plus"></i> Nueva Orden',
            Url::to(['/admin/ordenes/carrito', 'nueva' => 1]),
            ['class' => 'btn btn-success btnls']
        ) ?>
        <?= Html::a('<i class="fa fa-list"></i> Ver todas las órdenes',
            Url::to(['/admin/ordenes/index']),
            ['class' => 'btn btn-default btnls', 'style' => 'margin-left:8px']
        ) ?>
        <?= Html::a('<i class="fa fa-bar-chart"></i> Reportes',
            Url::to(['/admin/reportes/index']),
            ['class' => 'btn btn-info btnls', 'style' => 'margin-left:8px']
        ) ?>
    </div>
</div>

<!-- HOY -->

<div class="row cjafera">
    <div class="col-sm-4">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-shopping-cart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Órdenes hoy - <?= date('d/m/Y') ?></span>
                <span class="info-box-number"><?= $totalOrdenesHoy ?></span>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Recaudado hoy - <?= date('d/m/Y') ?></span>
                <span class="info-box-number">Bs. <?= number_format($totalRecaudadoHoy, 2) ?></span>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-flask"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Análisis hoy - <?= $mesActual ?></span>
                <span class="info-box-number"><?= $totalAnalisisHoy ?></span>
            </div>
        </div>
    </div>
</div>


<div class="row">

    <!-- ÚLTIMAS ÓRDENES -->
    <div class="col-sm-7">
    <div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-clock-o"></i> Últimas órdenes</h3>
        <div class="box-tools pull-right">
            <?= Html::a('Ver todas', Url::to(['/admin/ordenes/index']), ['class' => 'btn btn-success']) ?>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-condensed table-hover">
            <thead>
                <tr>
                    <th>N° Ref</th>
                    <th>Paciente</th>
                    <th>Doctor</th>
                    <th>Total</th>
                    <th>Estado de Pago</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ultimasOrdenes as $o): ?>
            <tr>
                <td><strong><?= Html::encode($o->numero_referencia ?: '#'.$o->idorden) ?></strong></td>
                <td><?= Html::encode($o->paciente_nombre) ?></td>
                <td><?= Html::encode($o->doctor->nombre ?? '—') ?></td>
                <td>Bs. <?= number_format($o->monto_total, 2) ?></td>
                <td>
                    <?php $cls = $o->estado_pago === 'pagado' ? 'success' : 'warning'; ?>
                    <span class="label label-<?= $cls ?>"><?= $o->estadoPagoLabel ?></span>
                </td>
                <td>
                    <?= Html::a('<i class="fa fa-eye"></i>',
                        Url::to(['/admin/ordenes/view', 'id' => $o->idorden]),
                        ['class' => 'btn btn-xs btn-success']
                    ) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($ultimasOrdenes)): ?>
            <tr><td colspan="6" class="text-center text-muted">No hay órdenes todavía</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>
    </div>

   <!-- PENDIENTES DE COBRO -->
    <div class="col-sm-5">
    <div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fa fa-exclamation-triangle"></i> Pendientes de cobro
        </h3>
    </div>
    <div class="box-body">
        <?php if (empty($pendientes)): ?>
            <p class="text-center text-muted"><i class="fa fa-check-circle text-green"></i> Todo al día</p>
        <?php else: ?>
            <p class="text-danger"><strong>Saldo total pendiente: Bs. <?= number_format($totalSaldoPendiente, 2) ?></strong></p>
            <ul class="list-group">
            <?php foreach ($pendientes as $p): ?>
            <li class="list-group-item" style="padding:6px 10px">
                <strong><?= Html::encode($p->numero_referencia ?: '#'.$p->idorden) ?></strong>
             - <?= Html::encode($p->paciente_nombre) ?>
                <span class="pull-right text-danger">Bs. <?= number_format($p->saldo, 2) ?></span>
                <br>
                <small class="text-muted"><?= date('d/m/Y', strtotime($p->fecha_registro)) ?></small>
                <?= Html::a('Cobrar', Url::to(['/admin/ordenes/pago', 'id' => $p->idorden]),
                    ['class' => 'btn btn-xs btn-warning pull-right cbrar', 'style' => 'margin-top:0px']) ?>
            </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    </div>


<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fa fa-flask"></i> Materiales próximos a vencer
        </h3>
    </div>
    <div class="box-body">
        <?php if (empty($alertasInventario)): ?>
            <p class="text-center text-muted"><i class="fa fa-check-circle text-green"></i> Sin vencimientos próximos</p>
        <?php else: ?>
            <table class="table table-condensed table-hover">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Lote</th>
                        <th>Cantidad</th>
                        <th>Vence</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($alertasInventario as $lote):
                    $alerta = $lote->getAlerta();
                    $cls = $alerta == 'vencido' ? 'danger' : 'warning';
                    $label = $alerta == 'vencido' ? 'Vencido' : 'Por vencer';
                ?>
                <tr>
                    <td><?= Html::encode($lote->productoLote->titulo ?? '—') ?></td>
                    <td><?= $lote->codigo_lote ?></td>
                    <td><?= $lote->cantidad_actual ?></td>
                    <td><?= date('d/m/Y', strtotime($lote->fecha_vencimiento)) ?></td>
                    <td><span class="label label-<?= $cls ?>"><?= $label ?></span></td>
                    <td>
                        <?= Html::a('<i class="fa fa-eye"></i>',
                            Url::to(['/admin/productos/inventario', 'id' => $lote->idproducto]),
                            ['class' => 'btn btn-xs btn-info']
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    </div>


<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fa fa-cubes"></i> Materiales con stock bajo
        </h3>
    </div>
    <div class="box-body">
        <?php if (empty($materialesStockBajo)): ?>
            <p class="text-center text-muted"><i class="fa fa-check-circle text-green"></i> Todo el stock está bien</p>
        <?php else: ?>
            <table class="table table-condensed table-hover">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Stock actual</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($materialesStockBajo as $idproducto => $stock):
                    $prod = \app\models\Productos::findOne($idproducto);
                    if (!$prod) continue;
                    $cls = $stock < 0 ? 'danger' : 'warning';
                    $label = $stock < 0 ? 'Déficit: ' . $stock : 'Stock bajo: ' . $stock;
                ?>
                <tr>
                    <td><?= Html::encode($prod->titulo) ?></td>
                    <td><span class="label label-<?= $cls ?>"><?= $label ?></span></td>
                    <td>
                        <?= Html::a('<i class="fa fa-eye"></i>',
                            Url::to(['/admin/productos/inventario', 'id' => $idproducto]),
                            ['class' => 'btn btn-xs btn-info']
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    </div>


    </div>

</div>

</section>