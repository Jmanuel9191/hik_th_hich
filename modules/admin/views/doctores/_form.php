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
    <div class="col-xs-12 " >
        <div class="mb-3">
            <?= $form->field($model, 'nombre')->textInput(['class' => 'form-control','maxlength' => true]) ?>
        </div>
    </div>



    <div class="col-xs-12 " >
    <div class="mb-3">
           <?= $form->field($model, 'descripcion')->textInput(['class' => 'form-control','maxlength' => true]) ?>


    </div>
</div>




    <div class="col-xs-12 " >
    <div class="mb-3">
           <?= $form->field($model, 'email')->textInput(['class' => 'form-control','maxlength' => true]) ?>


    </div>
</div>


    <div class="col-xs-12 " >
    <div class="mb-3">
           <?= $form->field($model, 'telefono')->textInput(['class' => 'form-control','maxlength' => true]) ?>


    </div>
</div>


    <div class="form-group clsgua" align="center">
        <?= Html::submitButton('GUARDAR', ['class' => $model->isNewRecord ? 'btn btn-success btform' : 'btn btn-success btform']) ?>
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
