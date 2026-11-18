<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$this->title = 'Registrar Orden';

$doctores = \yii\helpers\ArrayHelper::map(
    \app\models\Doctores::find()->orderBy('nombre')->all(),
    'iddoctor', 'nombre'
);

$total = array_sum(array_column($detalles, 'precio'));

$ultimaOrden = \app\models\Ordenes::find()
    ->where(['is not', 'numero_referencia', null])
    ->orderBy(['idorden' => SORT_DESC])
    ->one();
?>

<section class="content-header">
    <h1><?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
<div class="row">

    <div class="col-sm-7">
    <div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Datos del paciente</h3>
    </div>
    <div class="box-body">

    <?php $form = ActiveForm::begin([
        'type'       => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 4, 'deviceSize' => ActiveForm::SIZE_SMALL],
        'id'         => 'form-orden',
    ]); ?>

    <?= $form->field($model, 'numero_referencia')
        ->textInput(['placeholder' => 'Ej: 6781'])
        ->hint($ultimaOrden ? "Última orden registrada: <strong>{$ultimaOrden->numero_referencia}</strong>" : 'Aún no hay órdenes con número de referencia') ?>

    <?= $form->field($model, 'paciente_nombre')
        ->textInput(['placeholder' => 'Nombre completo del paciente']) ?>

    <?= $form->field($model, 'paciente_edad')
        ->textInput(['placeholder' => 'Ej: 35 años', 'value' => $model->paciente_edad]) ?>

    <?= $form->field($model, 'paciente_telefono')
        ->textInput(['placeholder' => 'Ej: 70000000', 'value' => $model->paciente_telefono]) ?>

    <?= $form->field($model, 'solicitante_nombre')
        ->textInput(['placeholder' => 'Opcional — si es distinto al paciente'])
        ->hint('Ej: la mamá que trae a su hijo') ?>

    <?= $form->field($model, 'iddoctor')->widget(Select2::classname(), [
        'data'          => $doctores,
        'value'         => $model->iddoctor,
        'options'       => ['placeholder' => 'Seleccioná el doctor (opcional)'],
        'pluginOptions' => ['allowClear' => true],
    ]) ?>

    <input type="hidden" name="Ordenes[tipo_orden]" value="orden">

    <hr>

    <?= $form->field($model, 'tipo_pago')->widget(Select2::classname(), [
        'data'          => \app\models\Ordenes::getTiposPago(),
        'options'       => ['placeholder' => 'Tipo de pago'],
        'pluginOptions' => ['allowClear' => false],
    ]) ?>

    <!-- Total de lista (fijo, suma de análisis) -->
    <div class="form-group">
        <label class="col-sm-4 control-label">Total (Bs)</label>
        <div class="col-sm-8">
            <p class="form-control-static">
                <strong style="font-size:18px">Bs. <?= number_format($total, 2) ?></strong>
            </p>
        </div>
    </div>
    <input type="hidden" name="Ordenes[monto_total]" value="<?= $total ?>">

    <!-- Descuento -->
    <?= $form->field($model, 'descuento')->textInput([
        'id'          => 'descuento',
        'placeholder' => '0.00',
        'inputmode'   => 'decimal',
        'value'       => $model->descuento ?: '0.00',
    ]) ?>

    <!-- Total a pagar (calculado en tiempo real) -->
    <div class="form-group">
        <label class="col-sm-4 control-label">Total a pagar (Bs)</label>
        <div class="col-sm-8">
            <input type="text" id="total-pagar-display" class="form-control" readonly
                   value="<?= number_format($total, 2) ?>">
        </div>
    </div>

    <?= $form->field($model, 'monto_pagado')->textInput([
        'id'          => 'monto-pagado',
        'placeholder' => '0.00',
        'inputmode'   => 'decimal',
        'value'       => $model->monto_pagado ?: '',
    ]) ?>
    <div class="form-group" id="alerta-pagado" style="display:none; margin-top:-15px; margin-bottom:15px">
        <div class="col-sm-offset-4 col-sm-8">
            <span class="text-danger">
                <i class="fa fa-exclamation-triangle"></i>
                El monto pagado no puede superar el total a pagar.
            </span>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-4 control-label">Saldo (Bs)</label>
        <div class="col-sm-8">
            <input type="text" id="saldo-display" class="form-control" readonly
                   value="<?= number_format($total, 2) ?>">
        </div>
    </div>

    <?php foreach ($detalles as $i => $d): ?>
        <input type="hidden" name="detalles[<?= $i ?>][idproducto]" value="<?= Html::encode($d['idproducto']) ?>">
        <input type="hidden" name="detalles[<?= $i ?>][precio]"     value="<?= Html::encode($d['precio']) ?>">
    <?php endforeach; ?>

    <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
            <a href="<?= Url::to(['carrito']) ?>" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Volver a las pruebas
            </a>
            <?= Html::submitButton('<i class="fa fa-check"></i> Aceptar', ['class' => 'btn btn-success', 'id' => 'btn-aceptar-orden']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    </div>
    </div>
    </div>

    <div class="col-sm-5">
    <div class="box box-info" style="padding: 10px;">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list"></i> Análisis seleccionados</h3>
    </div>
    <div class="box-body no-padding">
        <table class="table table-condensed table-striped">
            <thead>
                <tr><th>#</th><th>Análisis</th><th class="text-right">Bs.</th></tr>
            </thead>
            <tbody>
            <?php $num = 1; $catActual = null; foreach ($detalles as $d):
                $cat = $d['categoria'] ?? '';
                if ($cat !== $catActual): $catActual = $cat; ?>
                <tr style="background:#eef2f7">
                    <td colspan="3"><strong><i class="fa fa-flask"></i> <?= Html::encode($cat) ?></strong></td>
                </tr>
            <?php endif; ?>
                <tr>
                    <td><?= $num++ ?></td>
                    <td><?= Html::encode($d['titulo']) ?></td>
                    <td class="text-right"><?= number_format((float)$d['precio'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#dff0d8">
                    <th colspan="2" class="text-right">TOTAL</th>
                    <th class="text-right">Bs. <?= number_format($total, 2) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="box-footer text-muted text-center">
        <small><?= count($detalles) ?> análisis seleccionado(s)</small>
    </div>
    </div>
    </div>

</div>
</section>

<?php
$this->registerJs("
    var total = {$total};

    function recalcular() {
        var descuento  = parseFloat(\$('#descuento').val()) || 0;
        var totalPagar = Math.max(0, total - descuento);
        var pagado     = parseFloat(\$('#monto-pagado').val()) || 0;

        // Restricción: el pagado no puede superar el total a pagar
        if (pagado > totalPagar) {
            \$('#alerta-pagado').show();
            \$('#btn-aceptar-orden').prop('disabled', true);
        } else {
            \$('#alerta-pagado').hide();
            \$('#btn-aceptar-orden').prop('disabled', false);
        }

        var saldo = Math.max(0, totalPagar - pagado);
        \$('#total-pagar-display').val(totalPagar.toFixed(2));
        \$('#saldo-display').val(saldo.toFixed(2));
    }

    \$('#descuento').on('input', recalcular);
    \$('#monto-pagado').on('input', recalcular);

    // Por si acaso: validación extra al enviar el formulario
    \$('#form-orden').on('submit', function(e) {
        var descuento  = parseFloat(\$('#descuento').val()) || 0;
        var totalPagar = Math.max(0, total - descuento);
        var pagado     = parseFloat(\$('#monto-pagado').val()) || 0;
        if (pagado > totalPagar) {
            e.preventDefault();
            \$('#alerta-pagado').show();
            \$('#monto-pagado').focus();
        }
    });
");
?>