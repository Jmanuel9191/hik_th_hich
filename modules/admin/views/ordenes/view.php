<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Orden #' . $model->numero_referencia;

$meses = [
    1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
    5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
    9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
];
$fechaFormateada = date('d', strtotime($model->fecha_registro))
    . ' de '
    . $meses[(int)date('n', strtotime($model->fecha_registro))]
    . ' del '
    . date('Y', strtotime($model->fecha_registro));

$totalAPagar = $model->monto_total - ($model->descuento ?? 0);
?>

<section class="content-header">
    <h1>
        <?= Html::encode($this->title) ?>
        <?php if ($model->estado_pago === 'pagado'): ?>
            <span class="label label-success" style="font-size:14px">PAGADO</span>
        <?php else: ?>
            <span class="label label-warning" style="font-size:14px">PENDIENTE</span>
        <?php endif; ?>
    </h1>
</section>

<section class="content">
<div class="row">
<div class="col-xs-12">
<div class="box">

<?php
// Mismo criterio que PermisosService::tieneRol('administrador'), sin
// llamar a verificarAdministrador() acá — esa redirige y corta la
// página si falla, no sirve para un simple booleano dentro de la vista.
$esAdmin = !Yii::$app->user->isGuest
    && Yii::$app->user->identity->rol
    && Yii::$app->user->identity->rol->alias === 'administrador';
?>

<div class="box-header no-print">
    <a href="javascript:void(0)" id="btn-imprimir" class="btn btn-success">
        <i class="fa fa-print"></i> Imprimir
    </a>
    <?php if ($model->estado_pago === 'pendiente'): ?>
        <a href="<?= Url::to(['pago', 'id' => $model->idorden]) ?>" class="btn btn-warning">
            <i class="fa fa-money"></i> Registrar pago
        </a>
    <?php endif; ?>
    <?php if ($esAdmin): ?>
    <a href="<?= Url::to(['update', 'id' => $model->idorden]) ?>" class="btn btn-info">
        <i class="fa fa-pencil"></i> Editar orden
    </a>
    <?php endif; ?>
    <a href="<?= Url::to(['index']) ?>" class="btn btn-default">
        <i class="fa fa-list"></i> Volver al listado
    </a>
</div>

<!-- ÁREA IMPRIMIBLE — todo dentro de UNA tabla, con el logo y los
     datos del paciente en un <thead> real: eso es lo que hace que se
     repitan arriba de cada hoja cuando la orden tiene muchos análisis
     y se pasa de una página (mismo mecanismo que ya usamos en la
     ficha de resultados, que sí funciona confiable en Chrome —
     position:fixed no lo sostiene, un <thead> de tabla sí). -->
<div class="box-body printable-area" id="area-imprimible">
<table class="print-wrapper" style="width:100%; border-collapse:collapse;">
<thead>
<tr><td style="padding:0">

    <!-- Encabezado -->
     <table width="100%" style="margin-bottom:2px">
        <tr>
            <td width="40%" style="vertical-align:middle">
                <img src="<?= Yii::$app->request->baseUrl ?>/assets_b/images/logo.jpg"
                     style="max-height:80px; max-width:300px">
            </td>
            <td width="60%" style="text-align:center; vertical-align:middle">
                <h2 style="margin:0; font-size:25px">
                    <strong>
                        ORDEN N° <?= Html::encode($model->numero_referencia ?: $model->idorden) ?>
                    </strong>
                </h2>
                  <p style="margin:4px 0; font-size:17px">
                    <strong>Fecha:</strong> <?= $fechaFormateada ?>
                </p>
                
            </td>
        </tr>
    </table>
      <p style="margin:0; font-size:16px; text-align:center;   font-style: italic; margin-bottom: 5px;">
                    Sucursal Bajío, Zona el Bajío (Frente al Hospital del Bajío del Oriente) <br> Telf. 627 37369 – 780 83843
                </p>


    <!-- Datos del paciente -->
       <table width="100%" style="border:5px double #333333; border-radius:10px; margin-bottom:5px; font-size:14px; border-collapse:separate;">
    <tr>
        <td style="padding:2px 8px; width:65%">
            <table width="100%"><tr>
                <td style="width:100px"><strong>PACIENTE:</strong></td>
                <td><?= Html::encode(strtoupper($model->paciente_nombre)) ?></td>
            </tr></table>
        </td>
        <td style="padding:2px 8px; width:35%">
            <table width="100%"><tr>
                <td style="width:50px"><strong>EDAD:</strong></td>
                <td><?= Html::encode($model->paciente_edad ?? '—') ?></td>
            </tr></table>
        </td>
    </tr>

    <?php if ($model->solicitante_nombre): ?>
    <tr>
        <td style="padding:2px 8px;">
            <table width="100%"><tr>
                <td style="width:100px"><strong>SOLICITANTE:</strong></td>
                <td><?= Html::encode($model->solicitante_nombre) ?></td>
            </tr></table>
        </td>
        <td style="padding:2px 8px;">
            <table width="100%"><tr>
                <td style="width:50px"><strong>TELF.:</strong></td>
                <td><?= Html::encode($model->paciente_telefono ?? '—') ?></td>
            </tr></table>
        </td>
    </tr>
    <tr>
        <td style="padding:2px 8px;" colspan="2">
            <table width="100%"><tr>
                <td style="width:100px"><strong>CENTRO:</strong></td>
                <td>SUCURSAL BAJÍO</td>
            </tr></table>
        </td>
    </tr>
    <?php else: ?>
    <tr>
        <td style="padding:2px 8px;">
            <table width="100%"><tr>
                <td style="width:100px"><strong>CENTRO:</strong></td>
                <td>SUCURSAL BAJÍO</td>
            </tr></table>
        </td>
        <td style="padding:2px 8px;">
            <table width="100%"><tr>
                <td style="width:50px"><strong>TELF.:</strong></td>
                <td><?= Html::encode($model->paciente_telefono ?? '—') ?></td>
            </tr></table>
        </td>
    </tr>
    <?php endif; ?>

    <?php if ($model->doctor): ?>
    <tr>
        <td style="padding:2px 8px;" colspan="2">
            <table width="100%"><tr>
                <td style="width:100px"><strong>DOCTOR:</strong></td>
                <td><?= Html::encode($model->doctor->nombre) ?></td>
            </tr></table>
        </td>
    </tr>
    <?php endif; ?>
</table>

</td></tr>
</thead>
<tbody>
<tr><td style="padding:0">

    <!-- Detalle de análisis -->
        <table width="100%" style="border-collapse:collapse; font-size:14px; margin-bottom:10px">
        <thead>
            <tr style="background-color:#f0f0f0!important">
                      <th style="border:1px solid #333; padding:5px 8px; text-align:left; background-color:#f0f0f0!important">Detalle de la Cotización</th>
                <th style="border:1px solid #333; padding:5px 8px; text-align:right; width:80px; background-color:#f0f0f0!important">Precio</th>
                <th style="border:1px solid #333; padding:5px 8px; text-align:right; width:80px; background-color:#f0f0f0!important">Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($detalles as $d): ?>
            <tr class="fila-analisis">
               <td style="border:1px solid #333333; padding:4px 8px">
                    <?= Html::encode($d->producto->titulo ?? '—') ?>
                </td>
                    <td style="border:1px solid #333333; padding:4px 8px; text-align:right">
                    <?= number_format($d->precio, 2) ?>
                </td>
                      <td style="border:1px solid #333333; padding:4px 8px; text-align:right">
                    <?= number_format($d->subtotal, 2) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
    <tr>
        <td colspan="2" style="background-color:#f0f0f0!important; border:1px solid #333333; padding:4px 8px; text-align:right">
            <strong>Total (Bs.):</strong>
        </td>
        <td style="background-color:#f0f0f0!important; border:1px solid #333333; padding:4px 8px; text-align:right">
            <strong><?= number_format($model->monto_total, 2) ?></strong>
        </td>
    </tr>
    <!-- Descuento — siempre visible aunque sea 0 -->
    <tr>
        <td colspan="2" style=" padding:4px 8px; text-align:right">
            Descuento:
        </td>
        <td style="background-color:#f0f0f0!important; border:1px solid #333333; padding:4px 8px; text-align:right">
            <?= number_format($model->descuento ?? 0, 2) ?>
        </td>
    </tr>
    <!-- Total a pagar — siempre visible -->
    <tr>
        <td colspan="2" style=" padding:4px 8px; text-align:right">
            <strong>Total a pagar:</strong>
        </td>
        <td style="background-color:#f0f0f0!important; border:1px solid #333333; padding:4px 8px; text-align:right">
            <strong><?= number_format($totalAPagar, 2) ?></strong>
        </td>
    </tr>
    <tr>
        <td colspan="2" style=" padding:4px 8px; text-align:right">
            Pagado:
        </td>
        <td style="background-color:#f0f0f0!important; border:1px solid #333333; padding:4px 8px; text-align:right">
            <?= number_format($model->monto_pagado, 2) ?>
        </td>
    </tr>
    <?php if ($model->saldo > 0): ?>
    <tr >
        <td colspan="2" style=" padding:4px 8px; text-align:right">
            <strong>Saldo:</strong>
        </td>
        <td style="background-color:#f0f0f0!important; border:1px solid #333333; padding:4px 8px; text-align:right">
            <strong><?= number_format($model->saldo, 2) ?></strong>
        </td>
    </tr>
    <?php endif; ?>
</tfoot>
    </table>

    <!-- Estado -->
    <div style="text-align:center; margin:15px 0; page-break-inside:avoid;">
        <?php if ($model->estado_pago === 'pagado'): ?>
            <span style="font-size:28px; font-weight:bold; color:#27ae60!important; border:3px solid #27ae60; padding:5px 20px; border-radius:5px">
                PAGADO
            </span>
        <?php else: ?>
            <span style="font-size:28px; font-weight:bold; color:#e1a712!important; border:3px solid #e1a712; padding:5px 20px; border-radius:5px">
                PENDIENTE
            </span>
        <?php endif; ?>

        <?php if (count($pagos) <= 1): ?>
            <!-- Caso normal: un solo pago, se ve igual que siempre -->
            <p style="text-align:left; font-size:13px; margin:8px 0 0; color:#333">
                Pago <strong><?= Html::encode($model->getTipoPagoLabel()) ?></strong>,
                Importe: <strong>Bs. <?= number_format($model->monto_pagado, 2) ?></strong>
            </p>
        <?php else: ?>
            <!-- Más de un pago: se ve el historial completo — solo en
                 pantalla, es info de gestión para el admin, no algo
                 que tenga que salir en el recibo del paciente. -->
            <div class="no-print" style="text-align:left; margin-top:10px;">
                <p style="font-size:13px; margin-bottom:4px; color:#333">
                    <strong>Historial de pagos (<?= count($pagos) ?>):</strong>
                </p>
                <table width="100%" style="border-collapse:collapse; font-size:12px;">
                    <thead>
                        <tr style="background-color:#f0f0f0!important">
                            <th style="border:1px solid #ccc; padding:3px 6px; text-align:left">#</th>
                            <th style="border:1px solid #ccc; padding:3px 6px; text-align:left">Fecha</th>
                            <th style="border:1px solid #ccc; padding:3px 6px; text-align:right">Efectivo</th>
                            <th style="border:1px solid #ccc; padding:3px 6px; text-align:right">QR</th>
                            <th style="border:1px solid #ccc; padding:3px 6px; text-align:right">Total</th>
                            <th style="border:1px solid #ccc; padding:3px 6px; text-align:left"></th>
                            <th style="border:1px solid #ccc; padding:3px 6px" class="no-print"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pagos as $i => $p): $esUltimo = ($i === count($pagos) - 1); ?>
                        <tr>
                            <td style="border:1px solid #ccc; padding:3px 6px"><?= $i + 1 ?></td>
                            <td style="border:1px solid #ccc; padding:3px 6px"><?= date('d/m/Y H:i', strtotime($p->fecha_pago)) ?></td>
                            <td style="border:1px solid #ccc; padding:3px 6px; text-align:right"><?= $p->monto_efectivo > 0 ? number_format($p->monto_efectivo, 2) : '—' ?></td>
                            <td style="border:1px solid #ccc; padding:3px 6px; text-align:right"><?= $p->monto_qr > 0 ? number_format($p->monto_qr, 2) : '—' ?></td>
                            <td style="border:1px solid #ccc; padding:3px 6px; text-align:right"><strong><?= number_format($p->monto, 2) ?></strong></td>
                            <td style="border:1px solid #ccc; padding:3px 6px">
                                <?php if ($p->fueEditado()): ?><span class="label label-default" title="Este pago fue editado">editado</span><?php endif; ?>
                            </td>
                            <td style="border:1px solid #ccc; padding:3px 6px" class="no-print">
                                <?php if ($esUltimo && $esAdmin): ?>
                                    <a href="<?= Url::to(['editar-pago', 'idpago' => $p->idpago]) ?>" class="btn btn-xs btn-warning">
                                        <i class="fa fa-pencil"></i> Editar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pie -->
    <hr style="    margin-top: 2px;
    border-bottom: 5px double #333333;
    margin-bottom: 7px;">
    <table width="100%" style="font-size:11px; color:#555">
        <tr>
            <td>Fecha: <?= date('d/m/Y H:i:s', strtotime($model->fecha_registro)) ?></td>
            <td style="text-align:right">
                Usuario: <?= Html::encode($model->usuarioRegistro->usuario ?? '—') ?>
            </td>
        </tr>
    </table>

</td></tr>
</tbody>
</table>

    <!-- Botón resultados -->
    <div style="margin-top:20px;padding-top:15px;border-top:1px solid #ddd" class="no-print">
        <a href="<?= Url::to(['resultados', 'id' => $model->idorden]) ?>"
           class="btn btn-success btn-lg btn-block">
            <i class="fa fa-flask"></i> Cargar Resultados
        </a>
    </div>

</div><!-- /printable-area -->
</div>
</div>
</div>
</section>

<style>
@media print {
    @page {
        size: auto;
        margin: 5mm 8mm;   /* arriba/abajo 5mm, costados 8mm — reduce el margen de la hoja */
    }

    .no-print,
    .content-header,
    .main-header,
    .main-sidebar,
    .box-header { display: none !important; }

    .box { border: none !important; box-shadow: none !important; margin: 0 !important; }

    /* elimina el padding/margen que agregan los contenedores de AdminLTE/Bootstrap */
    .box-body,
    .printable-area,
    .content,
    .content-wrapper,
    .col-xs-12,
    .row {
        padding: 0 !important;
        margin: 0 !important;
    }

    body { font-size: 12px; margin: 0 !important; }

    /* Ninguna fila de análisis se corta a la mitad entre dos hojas */
    .fila-analisis { page-break-inside: avoid; }

    /* fuerza a que se impriman los colores de fondo (thead, tfoot, etc.) */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
}
</style>

<?php
$this->registerJs("\$('#btn-imprimir').on('click', function() { window.print(); });");
?>