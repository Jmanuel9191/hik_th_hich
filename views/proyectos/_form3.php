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
    'action' => $model->isNewRecord
        ? ['proyectos/asignaciondetareas', 'idproyecto' => $model->idproyecto]
        : ['proyectos/editasignaciondetareas', 'id' => $model->idasignartarea , 'idproyecto' => $model->idproyecto],
    'class' => 'settings-form',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => ['labelSpan' => 2, 'deviceSize' => ActiveForm::SIZE_SMALL
    ] // important
]); ?>

<?php if ($model->hasErrors()): ?>
    <?= $form->errorSummary($model) ?>
<?php endif; ?>
<div class="col-12 " >
    <div class="mb-3">
        <?= Html::activeHiddenInput($model, 'idtarea') ?>
        <?php echo $form->field($model, 'prioridad')->widget(Select2::classname(), [

            'data' => \app\models\Proyectos::getPrioridad(),
            'language' => 'es',
            'options' => [
                'placeholder' => 'Prioridad',
                //'multiple' => true,
            ],
            'pluginOptions' => [
                'templateResult' => new \yii\web\JsExpression('format'),
                'templateSelection' => new \yii\web\JsExpression('format'),
                'escapeMarkup' => $escape,
                'allowClear' => true
            ],
        ]); ?>

    </div>
</div>

<div class="col-12 " >
    <div class="mb-3">
        <?php echo $form->field($model, 'idconsultor')->widget(Select2::classname(), [

            'data' => \app\models\Usuarios::getSelectConsultor(),
            'language' => 'es',
            'options' => [
                'placeholder' => 'Consultor',
                //'multiple' => true,
            ],
            'pluginOptions' => [
                'templateResult' => new \yii\web\JsExpression('format'),
                'templateSelection' => new \yii\web\JsExpression('format'),
                'escapeMarkup' => $escape,
                'allowClear' => true
            ],
        ]); ?>
    </div>
</div>

<div class="col-12 " >
    <div class="mb-3">
        <?= $form->field($model, 'fechainicio')->widget(
            DatePicker::className(), [
            // modify template for custom rendering
            'language' => 'es',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
                'todayBtn' => "linked",
                'keyboardNavigation' => false,
                'forceParse' => false
            ]
        ]); ?>
    </div>
</div>



<div class="col-12 " >
    <div class="mb-3">
        <?= $form->field($model, 'fechafin')->widget(
            DatePicker::className(), [
            // modify template for custom rendering
            'language' => 'es',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
                'todayBtn' => "linked",
                'keyboardNavigation' => false,
                'forceParse' => false
            ]
        ]); ?>
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

<div class="col-12">
    <div class="mb-3">


        <?= $form->field($model, 'comentario')->textarea(['rows' => 6]) ?>

    </div>
</div>

<div class="form-group clsgua" >
    <?= Html::submitButton('Asignar', ['class' => $model->isNewRecord ? 'btn app-btn-primary' : 'btn app-btn-primary']) ?>
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
