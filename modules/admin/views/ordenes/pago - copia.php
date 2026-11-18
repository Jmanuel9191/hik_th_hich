<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

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
    <label class="col-sm-3 control-label">Total orden1:</label>
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

<?= $form->field($model, 'monto_pagado')->textInput([
    'id'          => 'monto-pagado',
    'inputmode'   => 'decimal',
    'value'       => $model->monto_pagado,
    'placeholder' => '0.00',
]) ?>
<div class="form-group" id="alerta-pagado" style="display:none; margin-top:-15px; margin-bottom:15px">
    <div class="col-sm-offset-3 col-sm-8">
        <span class="text-danger">
            <i class="fa fa-exclamation-triangle"></i>
            El monto pagado no puede superar el total a pagar.
        </span>
    </div>
</div>

<?= $form->field($model, 'tipo_pago')->widget(Select2::classname(), [
    'data'          => \app\models\Ordenes::getTiposPago(),
    'value'         => $model->tipo_pago,
    'pluginOptions' => ['allowClear' => false],
]) ?>

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
$totalPagarJs = json_encode((float)$totalPagar);

$this->registerJs("
    var totalPagar = {$totalPagarJs};

    function validarPagado() {
        var raw    = \$('#monto-pagado').val().toString().replace(',', '.');
        var pagado = parseFloat(raw) || 0;

        if (pagado > totalPagar) {
            \$('#alerta-pagado').show();
        } else {
            \$('#alerta-pagado').hide();
        }
        var saldo = Math.max(0, totalPagar - pagado).toFixed(2);
        \$('#saldo-display').val(saldo);
    }

    \$('#monto-pagado').on('input change keyup', validarPagado);
    validarPagado(); // corre una vez al cargar, por si el campo ya viene con un valor

    // El bloqueo real ocurre acá: 'beforeSubmit' es un evento propio de
    // yii.activeForm.js que se dispara ANTES de enviar el formulario,
    // y no se ve afectado por la validación automática de Yii (que solo
    // valida las reglas del modelo, no esta regla de negocio nuestra).
    \$('#form-pago').on('beforeSubmit', function(e) {
        var raw    = \$('#monto-pagado').val().toString().replace(',', '.');
        var pagado = parseFloat(raw) || 0;
        if (pagado > totalPagar) {
            \$('#alerta-pagado').show();
            \$('#monto-pagado').focus();
            return false; // cancela el envío del formulario
        }
        return true;
    });
");
?>

<style>
    .form-horizontal {
        padding-top: 20px; }
</style>