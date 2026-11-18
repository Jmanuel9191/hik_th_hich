<?php

use dosamigos\ckeditor\CKEditor;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\helpers\Html;

?>
<?php
$format = <<< SCRIPT
function format(state) {
if (!state.id) return state.text; // optgroup
return state.text;
}
SCRIPT;
$escape = new \yii\web\JsExpression("function(m) { return m; }");
$this->registerJs($format, \yii\web\View::POS_HEAD);
?>
    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
        'class' => 'settings-form',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 2, 'deviceSize' => ActiveForm::SIZE_SMALL
        ] // important
    ]); ?>
    <div class="col-12 " >
        <div class="mb-3">
            <?= $form->field($model, 'titulo')->textInput(['class' => 'form-control','maxlength' => true]) ?>
        </div>
    </div>

<div class="col-12 " >
    <div class="mb-3">

        <?php // Top most parent
         echo $form->field($model, 'modulo')->widget(\kartik\widgets\Select2::classname(), [
             'data' => \yii\helpers\ArrayHelper::map(\app\models\Modulos::findAll(['estado' => '1']), 'idmodulo', 'nombre'),
             'language' => 'es',
             'options' => ['placeholder' => 'Modulos'],
         ]); ?>
    </div>
</div>

<div class="col-12">
    <div class="mb-3">
        <?= $form->field($model, 'idtipo_tarea')->widget(Select2::classname(), [
            'data' => \yii\helpers\ArrayHelper::map(
                \app\models\TipoTareas::find()->where(['estado' => 1])->orderBy('nombre')->all(),
                'idtipo_tarea',
                'nombre'
            ),
            'language' => 'es',
            'options' => ['placeholder' => 'Seleccione tipo de tarea'],
            'pluginOptions' => ['allowClear' => true],
        ]) ?>
    </div>
</div>


<div class="col-12 " >
    <div class="mb-3">
        <?= $form->field($model, 'descripcion')->widget(\dosamigos\ckeditor\CKEditor::className(), [
            'options' => ['rows' => 6],
            'preset' => 'full',
            'clientOptions' => [
                'customConfig' => '', // Esto evita el error de config.js
            ]
        ]) ?>


    </div>
</div>



    <div class="form-group clsgua" >
        <?= Html::submitButton('GUARDAR', ['class' => $model->isNewRecord ? 'btn app-btn-primary' : 'btn app-btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

<?php
$this->registerJsFile('https://cdn.ckeditor.com/4.22.1/full/ckeditor.js', ['position' => \yii\web\View::POS_HEAD]);
?>

<?php
$js = <<<JS
$('form').on('beforeSubmit', function() {
    for (instance in CKEDITOR.instances) {
        CKEDITOR.instances[instance].updateElement();
    }
});
JS;
$this->registerJs($js);
?>
