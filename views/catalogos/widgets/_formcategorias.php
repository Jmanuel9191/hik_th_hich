<?php
use kartik\form\ActiveForm;
use yii\helpers\Html;
use kartik\select2\Select2;

$this->title = 'NUEVA CATEGORÍA DE SERVICIO';
?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">

        <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data'],
            'type' => ActiveForm::TYPE_HORIZONTAL,
            'formConfig' => ['labelSpan' => 2, 'deviceSize' => ActiveForm::SIZE_SMALL],
        ]); ?>

        <div class="col-12">
            <div class="mb-3">
                <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <div class="col-12">
            <div class="mb-3">
                <?= $form->field($model, 'alias')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <div class="col-12">
            <div class="mb-3">
                <?= $form->field($model, 'idpadre')->widget(Select2::classname(), [
                    'data' => \yii\helpers\ArrayHelper::map(
                        \app\models\CategoriasServicio::find()->where(['idpadre' => null, 'estado' => 1])->all(),
                        'idcategoria',
                        'nombre'
                    ),
                    'language' => 'es',
                    'options' => ['placeholder' => 'Seleccione categoría padre (si aplica)'],
                    'pluginOptions' => ['allowClear' => true],
                ]) ?>
            </div>
        </div>

        <div class="col-12">
            <div class="mb-3">
                <?= $form->field($model, 'estado')->widget(Select2::classname(), [
                    'data' => ['1' => 'Activo', '0' => 'Inactivo'],
                    'language' => 'es',
                    'options' => ['placeholder' => 'Seleccione estado'],
                    'pluginOptions' => ['allowClear' => true],
                ]) ?>
            </div>
        </div>

        <div class="form-group clsgua">
            <?= Html::submitButton('GUARDAR', ['class' => 'btn app-btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
