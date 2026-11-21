<?php

/**
 * Registrar un pago. Si la moneda del pago no es la del proyecto, pide cuánto equivale (huk-inversiones.js).
 *
 * @var yii\web\View $this
 * @var app\models\InversionPago $model
 * @var array $proyectos id => texto
 * @var array $datosProyectos id => ['moneda', 'codigo']
 * @var array $inversores id => texto
 * @var array $interesados idproyecto => [idusuario => ['texto', 'monto', 'idmoneda']] ("Quiero invertir": van primero, con ★)
 * @var string|null $volver proyecto | inversor | interesados
 */

use app\models\InversionPago;
use app\models\Moneda;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Json;
use app\widgets\ZonaArchivo;

\app\assets_b\InversionesAdminAsset::register($this);

$this->title = 'Registrar pago';
$opcionesProyecto = [];
foreach ($datosProyectos as $id => $d) {
    $opcionesProyecto[$id] = ['data-moneda' => $d['moneda'], 'data-codigo' => $d['codigo']];
}
// Con el proyecto ya elegido, sus interesados arriba (el JS los reordena si cambia el proyecto).
$interesadosProyecto = $interesados[$model->idproyecto] ?? [];
if ($interesadosProyecto) {
    $primero = [];
    foreach ($interesadosProyecto as $id => $d) {
        if (isset($inversores[$id])) {
            $primero[$id] = '★ ' . $inversores[$id] . ' · Interesado: ' . $d['texto'];
        }
    }
    $inversores = $primero + array_diff_key($inversores, $primero);
}
$cancelar = in_array($volver, ['proyecto', 'interesados'], true) && $model->idproyecto ? ['banco-proyectos/view', 'id' => $model->idproyecto, 'tab' => $volver === 'interesados' ? 'interesados' : 'inversiones']
    : ($volver === 'inversor' && $model->idusuario ? ['inversores/view', 'id' => $model->idusuario] : ['index']);
?>

<section class="content-header">
    <h1><i class="fa fa-money"></i> <?= Html::encode($this->title) ?></h1>
    <p class="huk-inicio-sub">Si el inversor ya pagó antes en este proyecto, este pago se suma a lo anterior.</p>
</section>

<section class="content">
    <?php if (!$proyectos): ?>
        <div class="callout callout-warning">No hay proyectos publicados (o con la inversión completa) con moneda definida<?= Yii::$app->user->identity->idrol == \app\components\Rol::GERENTE ? ' en sus áreas' : '' ?>.</div>
    <?php elseif (!$inversores): ?>
        <div class="callout callout-warning">No hay inversores aprobados<?= Yii::$app->user->identity->idrol == \app\components\Rol::GERENTE ? ' en sus áreas' : '' ?>.</div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(['id' => 'pago-form', 'options' => ['enctype' => 'multipart/form-data', 'class' => 'huk-pago-form']]); ?>
    <div class="row">
        <div class="col-md-7">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-handshake-o"></i> Quién y en qué</h3></div>
                <div class="box-body">
                    <?= $form->field($model, 'idproyecto')->widget(Select2::class, [
                        'data' => $proyectos,
                        'options' => ['placeholder' => 'Elija el proyecto…', 'options' => $opcionesProyecto, 'data-pago-proyecto' => true],
                        'pluginOptions' => ['allowClear' => false],
                    ])->hint('Solo proyectos publicados o con la inversión completa.') ?>
                    <?= $form->field($model, 'idusuario')->widget(Select2::class, [
                        'data' => $inversores,
                        'options' => ['placeholder' => 'Elija el inversor…', 'data-pago-inversor' => true, 'data-interesados' => Json::encode($interesados)],
                    ])->hint('Todos los inversores aprobados. Primero, con ★, los que tocaron «Quiero invertir» en este proyecto (y su monto estimado). '
                        . 'Entre paréntesis, las áreas que tiene habilitadas: el proyecto tiene que ser de una de ellas.') ?>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-money"></i> El pago</h3></div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-4"><?= $form->field($model, 'fecha_pago')->input('date', ['max' => date('Y-m-d')]) ?></div>
                        <div class="col-sm-4"><?= $form->field($model, 'monto')->input('number', ['min' => 0.01, 'step' => 'any', 'placeholder' => 'Ej: 5000']) ?></div>
                        <div class="col-sm-4"><?= $form->field($model, 'idmoneda')->dropDownList(Moneda::lista($model->idmoneda), ['prompt' => 'Elija…', 'data-pago-moneda' => true]) ?></div>
                    </div>
                    <div class="huk-pago-equivalente" data-pago-equivalente>
                        <?= $form->field($model, 'monto_equivalente')->input('number', ['min' => 0.01, 'step' => 'any'])
                            ->label('Equivale a <span data-pago-codigo>' . Html::encode($model->proyecto->moneda->codigo ?? '') . '</span> (moneda del proyecto)')
                            ->hint('Lo que vale este pago en la moneda del proyecto, con el tipo de cambio del día. Es lo que suma al % de la ronda.') ?>
                    </div>
                    <?= $form->field($model, 'tipo_pago')->radioList(InversionPago::TIPOS_PAGO, [
                        'class' => 'huk-tipos-pago',
                        'item' => fn($i, $label, $name, $checked, $value) => Html::label(
                            Html::radio($name, $checked, ['value' => $value, 'data-pago-tipo' => true, 'data-ayuda' => InversionPago::AYUDA_REFERENCIA[$value]]) . ' ' . Html::encode($label),
                            null, ['class' => 'huk-tipo-pago' . ($checked ? ' is-activo' : '')]),
                    ]) ?>
                    <?= $form->field($model, 'referencia')->textInput(['maxlength' => 150,
                        'placeholder' => InversionPago::AYUDA_REFERENCIA[$model->tipo_pago] ?? '', 'data-pago-referencia' => true]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-paperclip"></i> Comprobante y nota</h3></div>
                <div class="box-body">
                    <?= $form->field($model, 'comprobante', ['template' => ZonaArchivo::plantilla('Foto o PDF · hasta ' . InversionPago::MAX_COMPROBANTE_MB . ' MB')])
                        ->fileInput(['accept' => '.jpg,.jpeg,.png,.webp,.pdf'])
                        ->hint('Si no lo tiene ahora, puede subirlo después desde la lista.') ?>
                    <?= $form->field($model, 'nota')->textarea(['rows' => 4, 'maxlength' => 500]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="huk-form-acciones text-center">
        <?= Html::submitButton('<i class="fa fa-check"></i> Registrar pago', ['class' => 'btn btn-success btform']) ?>
        <?= Html::a('Cancelar', $cancelar, ['class' => 'btn btn-default btform']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</section>
