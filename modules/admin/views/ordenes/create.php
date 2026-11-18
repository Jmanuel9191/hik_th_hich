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

    <!-- Forma de pago: acá el cajero anota lo que el cliente le dio en
         cada método. Puede ser más que el total sin ningún problema —
         el sistema aplica lo que corresponde y calcula el cambio solo. -->
    <div class="form-group">
        <label class="col-sm-4 control-label">Forma de pago</label>
        <div class="col-sm-8">
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-money"></i> Efectivo</span>
                        <input type="text" id="pago-efectivo" class="form-control"
                               inputmode="decimal" placeholder="0.00"
                               value="<?= $model->monto_efectivo ?: '' ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-qrcode"></i> QR</span>
                        <input type="text" id="pago-qr" class="form-control"
                               inputmode="decimal" placeholder="0.00"
                               value="<?= $model->monto_qr ?: '' ?>">
                    </div>
                </div>
            </div>
            <p class="help-block" style="margin-bottom:0">
                Anotá lo que te dio el cliente en cada método. Si suma más que el total, abajo te calcula el cambio.
            </p>
        </div>
    </div>

    <!-- Lo que REALMENTE se aplica a la orden (nunca supera el total a
         pagar) — esto es lo que efectivamente se guarda. Los campos de
         arriba son lo que el cajero anotó; estos son el resultado ya
         descontado el cambio. -->
    <input type="hidden" id="monto-efectivo-hidden" name="Ordenes[monto_efectivo]" value="<?= $model->monto_efectivo ?: '0' ?>">
    <input type="hidden" id="monto-qr-hidden" name="Ordenes[monto_qr]" value="<?= $model->monto_qr ?: '0' ?>">
    <!-- Se arma solo: 'efectivo', 'qr' o 'mixto' según lo que se aplicó -->
    <input type="hidden" id="tipo-pago-hidden" name="Ordenes[tipo_pago]"
           value="<?= Html::encode($model->tipo_pago ?: 'efectivo') ?>">

    <!-- Cambio a devolver — solo informativo, no se guarda -->
    <div class="form-group" id="grupo-cambio" style="display:none">
        <label class="col-sm-4 control-label">Cambio a devolver</label>
        <div class="col-sm-8">
            <p class="form-control-static" id="cambio-display">
                <strong style="font-size:18px">Bs. 0.00</strong>
            </p>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-4 control-label">Monto pagado (Bs)</label>
        <div class="col-sm-8">
            <input type="text" id="monto-pagado" name="Ordenes[monto_pagado]" class="form-control" readonly
                   value="<?= $model->monto_pagado ?: '0.00' ?>">
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

    // Convierte '' , '1,5' , '1.5' -> número. Nunca NaN.
    function num(val) {
        return parseFloat((val || '').toString().trim().replace(',', '.')) || 0;
    }

    function recalcular() {
        var descuento  = num(\$('#descuento').val());
        var totalPagar = Math.max(0, total - descuento);

        var efectivoIngresado = num(\$('#pago-efectivo').val());
        var qrIngresado       = num(\$('#pago-qr').val());
        var totalBruto        = efectivoIngresado + qrIngresado;

        // Si entra más de lo que corresponde, el excedente es cambio.
        // El cambio SIEMPRE sale del efectivo (un QR no se puede
        // 'devolver' en el momento), y si el efectivo no alcanza para
        // cubrir todo el cambio (caso raro: todo el excedente vino por
        // QR), se descuenta también del QR aplicado — así lo guardado
        // nunca supera el total a pagar.
        var cambio           = Math.max(0, totalBruto - totalPagar);
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

        var saldo = Math.max(0, totalPagar - pagado);
        \$('#total-pagar-display').val(totalPagar.toFixed(2));
        \$('#saldo-display').val(saldo.toFixed(2));
    }

    \$('#descuento').on('input', recalcular);
    \$('#pago-efectivo').on('input', recalcular);
    \$('#pago-qr').on('input', recalcular);

    // Estado inicial correcto al cargar la página (útil si el
    // formulario vuelve a mostrarse con datos por un error de validación)
    recalcular();
");
?>