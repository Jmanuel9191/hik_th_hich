<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Cotización #' . $model->idorden;
?>
<?php
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
?>
<section class="content-header">
    <h1>
        <?= Html::encode($this->title) ?>
        <span class="label label-warning" style="font-size:14px">COTIZACIÓN</span>
    </h1>
</section>

<section class="content">
<div class="row">
<div class="col-xs-12">
<div class="box">

<div class="box-header no-print">
    <a href="javascript:void(0)" id="btn-imprimir" class="btn btn-primary" style="color: white!important;">
        <i class="fa fa-print"></i> Imprimir
    </a>
    <a href="<?= Url::to(['convertir-orden', 'id' => $model->idorden]) ?>"
       class="btn btn-success"
       onclick="return confirm('¿Convertir esta cotización en una orden real?')">
        <i class="fa fa-check"></i> Convertir en Orden
    </a>
    <a href="<?= Url::to(['index']) ?>" class="btn btn-default">
        <i class="fa fa-list"></i> Volver al listado
    </a>
</div>

<!-- ÁREA IMPRIMIBLE -->
<div class="box-body printable-area" id="area-imprimible">

    <!-- Encabezado -->
    <table width="100%" style="margin-bottom:2px">
        <tr>
            <td width="40%" style="vertical-align:middle">
                <img src="<?= Yii::$app->request->baseUrl ?>/assets_b/images/logo.jpg"
                     style="max-height:80px; max-width:300px">
            </td>
            <td width="60%" style="text-align:center; vertical-align:middle">
                <br>
                 <!-- <h2 style="margin:0; font-size:22px"><strong>COTIZACIÓN N° <?//= $model->idorden ?></strong></h2> -->
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
    <table width="100%" style="    border: 5px double #333333;
    border-radius: 10px;
    margin-bottom: 5px;
    font-size: 14px;
    border-collapse: separate;">
        <tr>
            <td style="padding:2px 8px;  width:65%">
                <strong>PACIENTE:</strong> <?= Html::encode(strtoupper($model->paciente_nombre)) ?>
            </td>
            <td style="padding:2px 8px; width:35%">
                <strong>EDAD:</strong> <?= Html::encode($model->paciente_edad ?? '—') ?>
            </td>
        </tr>
        <?php if ($model->solicitante_nombre): ?>
        <tr>
            <td style="padding:2px 8px; " colspan="2">
                <strong>SOLICITANTE:</strong> <?= Html::encode($model->solicitante_nombre) ?>
            </td>
        </tr>
        <?php endif; ?>
        <tr>
            <td style="padding:2px 8px; ">
                <strong>CENTRO:&nbsp;&nbsp;&nbsp;</strong> SUCURSAL BAJÍO
            </td>
            <td style="padding:2px 8px; ">
                <strong>TELF.:</strong> <?= Html::encode($model->paciente_telefono ?? '—') ?>
            </td>
        </tr>
        <?php if ($model->doctor): ?>
        <tr>
            <td style="padding:2px 8px; " colspan="2">
                <strong>DOCTOR:&nbsp;&nbsp;&nbsp;</strong> <?= Html::encode($model->doctor->nombre) ?>
            </td>
        </tr>
        <?php endif; ?>
    </table>

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
            <tr>
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
        <tfoot style="background-color:#f0f0f0!important
">
            <tr>
                <td colspan="2" style="border:1px solid #333; padding:5px 8px; text-align:right">
                    <strong>Total (Bs.):</strong>
                </td>
                <td style="border:1px solid #333; padding:5px 8px; text-align:right">
                    <strong><?= number_format($model->monto_total, 2) ?></strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Estado COTIZACIÓN -->
    <div style="text-align:center; margin:15px 0">
        <span style="font-size:28px; font-weight:bold; color:#e67e22; border:3px solid #e67e22; padding:5px 20px; border-radius:5px">
            COTIZACIÓN
        </span>
    </div>

    <p style="font-size:11px; color:#666; text-align:center; margin-top:10px">
        Esta cotización tiene validez por 30 días. Los precios pueden variar sin previo aviso.
    </p>

    <!-- Pie -->
    <hr style="border-top:1px solid #ccc; margin-top:15px">
    <table width="100%" style="font-size:11px; color:#555">
        <tr>
            <td>Fecha: <?= date('d/m/Y H:i:s', strtotime($model->fecha_registro)) ?></td>
            <td style="text-align:right">
                Usuario: <?= Html::encode($model->usuarioRegistro->usuario ?? '—') ?>
            </td>
        </tr>
    </table>

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

    /* fuerza a que se impriman los colores de fondo (thead, tfoot, etc.) */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
}
</style>

<?php
$this->registerJs("
    \$('#btn-imprimir').on('click', function() { window.print(); });
");