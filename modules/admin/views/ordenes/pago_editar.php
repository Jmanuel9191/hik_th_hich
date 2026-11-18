<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;

$this->title = 'Editar pago — Orden #' . ($model->numero_referencia ?: $model->idorden);

$descuento    = $model->descuento ?? 0;
$totalAPagar  = $model->monto_total - $descuento;

// Cuánto está pagado en TOTAL sin contar este pago puntual — es el
// techo real: lo que se edite acá no puede hacer que el total supere
// el total a pagar de la orden.
$pagadoSinEstePago = (float)$model->monto_pagado - (float)$pago->monto;
$saldoDisponible   = max(0, $totalAPagar - $pagadoSinEstePago);
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

<div class="alert alert-warning">
    <i class="fa fa-exclamation-triangle"></i>
    Estás editando un pago ya registrado (el <?= date('d/m/Y H:i', strtotime($pago->fecha_pago)) ?>).
    Poné los montos en 0 si necesitás anularlo — la fila queda igual, solo con el monto en cero, y va a quedar marcada como "editado" con tu usuario y la fecha de ahora.
</div>

<div class="box box-warning">
<div class="box-header with-border">
    <h3 class="box-title">Editar pago</h3>
</div>
<div class="box-body" style="padding: 30px;">

<?php $form = ActiveForm::begin([
    'type'       => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL],
    'id'         => 'form-pago-editar',
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
        <p class="form-control-static">Bs. <?= number_format($model->monto_total, 2) ?></p>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label">Pagado en otros pagos:</label>
    <div class="col-sm-8">
        <p class="form-control-static">Bs. <?= number_format($pagadoSinEstePago, 2) ?></p>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label">Máximo para este pago:</label>
    <div class="col-sm-8">
        <p class="form-control-static"><strong style="font-size:18px">Bs. <?= number_format($saldoDisponible, 2) ?></strong></p>
    </div>
</div>

<div class="form-group">
    <label class="col-sm-3 control-label">Forma de pago</label>
    <div class="col-sm-8">
        <div class="row">
            <div class="col-sm-6">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-money"></i> Efectivo</span>
                    <input type="text" id="pago-efectivo" class="form-control"
                           inputmode="decimal" placeholder="0.00"
                           value="<?= $pago->monto_efectivo ?: '' ?>">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-qrcode"></i> QR</span>
                    <input type="text" id="pago-qr" class="form-control"
                           inputmode="decimal" placeholder="0.00"
                           value="<?= $pago->monto_qr ?: '' ?>">
                </div>
            </div>
        </div>
        <p class="help-block" style="margin-bottom:0">
            Poné 0 y 0 si querés anular este pago sin borrarlo.
        </p>
    </div>
</div>

<input type="hidden" id="monto-efectivo-hidden" name="OrdenPagos[monto_efectivo]" value="<?= $pago->monto_efectivo ?: '0' ?>">
<input type="hidden" id="monto-qr-hidden" name="OrdenPagos[monto_qr]" value="<?= $pago->monto_qr ?: '0' ?>">

<div class="form-group" id="alerta-excedido" style="display:none; margin-top:-15px; margin-bottom:15px">
    <div class="col-sm-offset-3 col-sm-8">
        <span class="text-danger">
            <i class="fa fa-exclamation-triangle"></i>
            No puede superar Bs. <?= number_format($saldoDisponible, 2) ?>.
        </span>
    </div>
</div>

<div class="form-group">
    <div class="col-sm-offset-3 col-sm-8">
        <a href="<?= Url::to(['view', 'id' => $model->idorden]) ?>" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Cancelar
        </a>
        <?= Html::submitButton('<i class="fa fa-check"></i> Guardar cambios', ['class' => 'btn btn-success', 'id' => 'btn-guardar-pago']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
</div>
</div>
</div>
</div>
</section>

<?php
$saldoDisponibleJs = json_encode((float)$saldoDisponible);

$this->registerJs("
    var saldoDisponible = {$saldoDisponibleJs};

    function num(val) { return parseFloat((val || '').toString().trim().replace(',', '.')) || 0; }

    function validar() {
        var efectivo = num(\$('#pago-efectivo').val());
        var qr       = num(\$('#pago-qr').val());
        var total    = efectivo + qr;

        \$('#monto-efectivo-hidden').val(efectivo.toFixed(2));
        \$('#monto-qr-hidden').val(qr.toFixed(2));

        if (total > saldoDisponible) {
            \$('#alerta-excedido').show();
            \$('#btn-guardar-pago').prop('disabled', true);
        } else {
            \$('#alerta-excedido').hide();
            \$('#btn-guardar-pago').prop('disabled', false);
        }
    }

    \$('#pago-efectivo, #pago-qr').on('input', validar);
    validar();
");
?>