<?php

/**
 * @var yii\web\View $this
 * @var app\models\Moneda $model
 */

use kartik\form\ActiveForm;
use yii\helpers\Html;

\app\assets_b\MonedasAdminAsset::register($this);

$this->title = $model->isNewRecord ? 'Agregar moneda' : 'Editar moneda: ' . $model->codigo;
?>

<section class="content-header">
    <h1><i class="fa fa-money"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
    <?php $form = ActiveForm::begin(['id' => 'moneda-form']); ?>
    <div class="box box-primary">
        <div class="box-body">
            <div class="row">
                <div class="col-sm-3"><?= $form->field($model, 'codigo')->textInput(['maxlength' => 3, 'placeholder' => 'EUR'])->hint('3 letras (ISO).') ?></div>
                <div class="col-sm-3"><?= $form->field($model, 'simbolo')->textInput(['maxlength' => 6, 'placeholder' => '€']) ?></div>
                <div class="col-sm-6"><?= $form->field($model, 'nombre')->textInput(['maxlength' => 60, 'placeholder' => 'Euro']) ?></div>
            </div>
        </div>
    </div>
    <div class="huk-form-acciones text-center">
        <?= Html::submitButton('<i class="fa fa-save"></i> Guardar', ['class' => 'btn btn-success btform']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-default btform']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</section>
