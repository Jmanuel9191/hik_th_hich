<?php

use dosamigos\ckeditor\CKEditor;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Noticias */
/* @var $form yii\widgets\ActiveForm */
?>

<?php
$format = <<< SCRIPT
function format(state) {
    if (!state.id) return state.text;
    return state.text;
}
SCRIPT;

$escape = new \yii\web\JsExpression("function(m) { return m; }");
$this->registerJs($format, \yii\web\View::POS_HEAD);

$idCampo = Html::getInputId($model, 'referencia_texto');
?>

<div class="box-body">

<?php $form = ActiveForm::begin(); ?>

<div class="row">
    <div class="col-md-8">
        <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
    </div>

    <div class="col-md-2" style="padding-top:32px;">
        <?= $form->field($model, 'es_negrita')->checkbox(['label' => 'Negrita']) ?>
    </div>

    <div class="col-md-2" style="padding-top:32px;">
        <?= $form->field($model, 'es_subtitulo')->checkbox(['label' => 'Subtítulo']) ?>
    </div>
</div>

<?= $form->field($model, 'unidad')->textInput([
    'maxlength' => 20,
    'placeholder' => 'Ej: mg/dl, U/L, %'
]) ?>

<?= $form->field($model, 'referencia_texto')->textarea([
    'rows' => 6
]) ?>

<div class="form-group">
    <?= Html::submitButton(
        'Guardar',
        ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
    ) ?>
</div>

<?php ActiveForm::end(); ?>

</div>

<?php
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/summernote/0.9.1/summernote.min.css');

$this->registerJsFile(
    'https://cdnjs.cloudflare.com/ajax/libs/summernote/0.9.1/summernote.min.js',
    [
        'depends' => [\yii\web\JqueryAsset::class],
    ]
);

$this->registerJs("
    $('#{$idCampo}').summernote({
        height: 200,
        toolbar: [
            ['style', ['bold', 'italic', 'underline']],
            ['para', ['ul', 'ol', 'paragraph']]
        ]
    });
");