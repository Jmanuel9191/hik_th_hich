<?php

use dosamigos\ckeditor\CKEditor;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Productos */
/* @var $form yii\widgets\ActiveForm */
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
<?php
$initial = [];
array_push($initial, \yii\helpers\Url::to(['imagen/productos/' . $model->archivo]));
?>
<div class="box-body">

    <?php $form = ActiveForm::begin([
        'id' => 'formProducto',
        'options' => ['enctype' => 'multipart/form-data'],
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 2, 'deviceSize' => ActiveForm::SIZE_SMALL
        ] // important
    ]); ?>


 
    <?= $form->field($model, 'titulo')->textInput(['maxlength' => true]) ?>



    <?php echo $form->field($model, 'idcategoria')->widget(Select2::classname(), [

        'data' => \app\models\Categoria::getSelectMenu('productos'),
        'language' => 'es',
        'options' => [
            'placeholder' => 'Categorias',
            //'multiple' => true,
        ],
        'pluginOptions' => [
            'templateResult' => new \yii\web\JsExpression('format'),
            'templateSelection' => new \yii\web\JsExpression('format'),
            'escapeMarkup' => $escape,
            'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'resumen')->textarea(['rows' => 6]) ?>

   
    <?= $form->field($model, 'preciooferta')->textInput(['maxlength' => true]) ?>
      <?= $form->field($model, 'precio')->textInput(['maxlength' => true]) ?>





  
    <?= $form->field($model, 'foto')->widget(\sadovojav\cutter\Cutter::className(), [
        'cropperOptions' => [
           /* 'aspectRatio' => 253 / 255,*/
        ]
    ]); ?>




    <?= Html::a('Cancelar', Yii::$app->request->referrer, ['class' => 'btn btn-danger']) ?>
    <?= Html::submitButton('Guardar', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>

    <?php ActiveForm::end(); ?>

</div>

<?= $this->render('scriptForm') ?>
