<?php
use kartik\form\ActiveForm;
use yii\helpers\Html;
use kartik\select2\Select2;

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
                <?= $form->field($model, 'descripcion')->textarea(['rows' => 3]) ?>
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

        <div class="col-12">
            <div class="mb-3">
                <?= $form->field($model, 'permisos')->widget(Select2::classname(), [
                    'data' => \yii\helpers\ArrayHelper::map(
                        \app\models\Permisos::find()->all(),
                        'idpermiso',
                        'alias'
                    ),
                    'language' => 'es',
                    'options' => [
                        'placeholder' => 'Seleccione permisos',
                        'multiple' => true,
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]) ?>
            </div>
        </div>

        <div class="form-group clsgua">
            <?= Html::submitButton('GUARDAR', ['class' => 'btn app-btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
