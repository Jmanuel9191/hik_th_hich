<?php


use dosamigos\ckeditor\CKEditor;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\helpers\Html;

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
        'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL
        ] // important
    ]); ?>




<div class="col-12 col-lg-6 col-md-6 col-sm-12 mspeo">
    <div class="mb-3">
        <div class="form-group password-wrapper">
              <?= $form->field($model, 'contrasena', [
            'template' => '
        <label class="control-label col-sm-3 laelsle">{label}</label>
        <div class="col-sm-9">
            <div class="input-group desl">
                {input}
                <div class="input-group-append">
                    <button type="button" id="toggle-password" class="btn btn-secondary">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
            </div>
            {error}
        </div>
    ',
        ])->passwordInput(['class' => 'form-control', 'required' => true]) ?>
        </div>



    </div>
</div>


<div class="form-group clsgua" >

    <div class="col-12 col-sm-12 col-md-12 col-lg-12" align="center">

    <div class="mb-3">
        <?= Html::submitButton($model->isNewRecord ? 'GUARDAR' : 'ACTUALIZAR',    ['class' => 'btn app-btn-primary btform']
        ) ?>
    </div>
    </div>
    </div>

    <?php ActiveForm::end(); ?>
  <script src="<?= Yii::getAlias('@web/assets_b/js/app.js') ?>"></script>