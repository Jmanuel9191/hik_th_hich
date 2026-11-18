<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;

$this->title = 'Registrar pago — Orden #' . ($model->numero_referencia ?: $model->idorden);

$descuento   = $model->descuento ?? 0;
$totalPagar  = $model->monto_total - $descuento;
?>

<section class="content-header">
    <h1><?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
<div class="row">
<div class="col-sm-12">

<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fa fa-ban"></i> <?= Yii::$app->session->getFlash('error') ?>
    </div>
<?php endif; ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fa fa-check"></i> <?= Yii::$app->session->getFlash('success') ?>
    </div>
<?php endif; ?>

<div class="box box-warning">
<div class="box-header with-border">
    <h3 class="box-title">Datos del pago</h3>
</div>
<div class="box-body" style="
    padding: 30px;
">

<?php $form = ActiveForm::begin([
    'type'       => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL],
    'id'         => 'form-pago',
]); ?>

<div class="form-group">
    <label class="col-sm-3 control-label">Paciente:</label>
    <div class="col-sm-8">
        <p class="form-control-static"><strong><?= Html::encode($model->paciente_nombre) ?></strong></p>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label">Total orden:</label>
    <div class="col-sm-8">
        <p class="form-control-static"><strong style="font-size:18px">Bs. <?= number_format($model->monto_total, 2) ?></strong></p>
    </div>
</div>

<?php if ($descuento > 0): ?>
<div class="form-group">
    <label class="col-sm-3 control-label">Descuento:</label>
    <div class="col-sm-8">
        <p class="form-control-static">
            <span class="text-warning">- Bs. <?= number_format($descuento, 2) ?></span>
        </p>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label">Total a pagar:</label>
    <div class="col-sm-8">
        <p class="form-control-static"><strong style="font-size:18px">Bs. <?= number_format($totalPagar, 2) ?></strong></p>
    </div>
</div>
<?php endif; ?>

<!-- NUEVO: cuánto lleva pagado hasta ahora (suma de todos los pagos
     anteriores) y cuánto falta — esto es lo que limita el pago nuevo,
     no el total de la orden completa. -->
<div class="form-group">
    <label class="col-sm-3 control-label">Pagado hasta ahora:</label>
    <div class="col-sm-8">
        <p class="form-control-static">Bs. <?= number_format($model->monto_pagado, 2) ?></p>
    </div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label">Saldo pendiente:</label>
    <div class="col-sm-8">
        <p class="form-control-static"><strong style="font-size:18px; color:#c0392b">Bs. <?= number_format($model->saldo, 2) ?></strong></p>
    </div>
</div>

<!-- Forma de pago: acá se anota lo que el cliente dio en cada método,
     para ESTE pago nuevo — arranca siempre vacío, no es una edición
     de lo que ya pagó antes. -->
<div class="form-group">
    <label class="col-sm-3 control-label">Forma de pago</label>
    <div class="col-sm-8">
        <div class="row">
            <div class="col-sm-6">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-money"></i> Efectivo</span>
                    <input type="text" id="pago-efectivo" class="form-control"
                           inputmode="decimal" placeholder="0.00" value="">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-qrcode"></i> QR</span>
                    <input type="text" id="pago-qr" class="form-control"
                           inputmode="decimal" placeholder="0.00" value="">
                </div>
            </div>
        </div>
        <p class="help-block" style="margin-bottom:0">
            Anotá lo que te dio el cliente en cada método, para este pago. Si suma más que el saldo pendiente, abajo te calcula el cambio.
        </p>
    </div>
</div>

<!-- Lo que REALMENTE se aplica a este pago nuevo (nunca supera el
     saldo pendiente) — esto es lo que efectivamente se guarda. -->
<input type="hidden" id="monto-efectivo-hidden" name="Ordenes[monto_efectivo]" value="0">
<input type="hidden" id="monto-qr-hidden" name="Ordenes[monto_qr]" value="0">
<!-- Se arma solo: 'efectivo', 'qr' o 'mixto' según lo que se aplicó -->
<input type="hidden" id="tipo-pago-hidden" name="Ordenes[tipo_pago]"
       value="efectivo">

<!-- Cambio a devolver — solo informativo, no se guarda -->
<div class="form-group" id="grupo-cambio" style="display:none">
    <label class="col-sm-3 control-label">Cambio a devolver</label>
    <div class="col-sm-8">
        <p class="form-control-static" id="cambio-display">
            <strong style="font-size:18px">Bs. 0.00</strong>
        </p>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label">Monto pagado (Bs)</label>
    <div class="col-sm-8">
        <input type="text" id="monto-pagado" name="Ordenes[monto_pagado]" class="form-control" readonly
               value="<?= $model->monto_pagado ?: '0.00' ?>">
        <p class="help-block" style="margin-bottom:0">Lo que se aplica a la orden (nunca supera el total a pagar).</p>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label">Saldo resultante:</label>
    <div class="col-sm-8">
        <input type="text" id="saldo-display" class="form-control" readonly
               value="<?= number_format($model->saldo, 2) ?>">
    </div>
</div>

<div class="form-group">
    <div class="col-sm-offset-3 col-sm-8">
        <a href="<?= Url::to(['view', 'id' => $model->idorden]) ?>" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Cancelar
        </a>
        <?= Html::submitButton('<i class="fa fa-check"></i> Confirmar pago', ['class' => 'btn btn-success', 'id' => 'btn-confirmar-pago']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
</div>
</div>
</div>
</div>
</section>

<?php
// json_encode garantiza que el número siempre use punto decimal en JS,
// sin importar la configuración regional (locale) del servidor.
// OJO: acá usamos $model->saldo (lo que falta pagar AHORA, después de
// pagos anteriores) — no $totalPagar (que es el total de la orden
// completa, ya se muestra arriba solo como referencia).
$saldoPendienteJs = json_encode((float)$model->saldo);

$this->registerJs("
    var saldoPendiente = {$saldoPendienteJs};

    // Convierte '', '1,5', '1.5' -> número. Nunca NaN.
    function num(val) {
        return parseFloat((val || '').toString().trim().replace(',', '.')) || 0;
    }

    function recalcular() {
        var efectivoIngresado = num(\$('#pago-efectivo').val());
        var qrIngresado       = num(\$('#pago-qr').val());
        var totalBruto        = efectivoIngresado + qrIngresado;

        // El cambio siempre sale del efectivo. Si el efectivo no
        // alcanza para cubrirlo todo (caso raro: el excedente vino por
        // QR), se descuenta también del QR aplicado, así lo guardado
        // nunca supera el total a pagar.
        var cambio           = Math.max(0, totalBruto - saldoPendiente);
        var efectivoAplicado = Math.max(0, efectivoIngresado - cambio);
        var cambioRestante   = Math.max(0, cambio - efectivoIngresado);
        var qrAplicado       = Math.max(0, qrIngresado - cambioRestante);

        var pagado = efectivoAplicado + qrAplicado;

        // Esto es lo que efectivamente se guarda en el sistema
        \$('#monto-efectivo-hidden').val(efectivoAplicado.toFixed(2));
        \$('#monto-qr-hidden').val(qrAplicado.toFixed(2));
        \$('#monto-pagado').val(pagado.toFixed(2));

        var tipoPago = 'efectivo';
        if (efectivoAplicado > 0 && qrAplicado > 0)       tipoPago = 'mixto';
        else if (qrAplicado > 0 && efectivoAplicado <= 0) tipoPago = 'qr';
        \$('#tipo-pago-hidden').val(tipoPago);

        if (cambio > 0) {
            \$('#grupo-cambio').show();
            \$('#cambio-display').html(
                '<strong style=\"font-size:18px; color:#c0392b\">Bs. ' + cambio.toFixed(2) + '</strong> '
                + '<span class=\"text-danger\">— Devolver cambio</span>'
            );
        } else {
            \$('#grupo-cambio').hide();
        }

        var saldo = Math.max(0, saldoPendiente - pagado).toFixed(2);
        \$('#saldo-display').val(saldo);
    }

    \$('#pago-efectivo, #pago-qr').on('input change keyup', recalcular);
    recalcular(); // corre una vez al cargar, por si los campos ya vienen con valores
");
?>

<style>
    .form-horizontal {
        padding-top: 20px; }
</style>