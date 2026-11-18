<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;

?>
<div class="box-body">
<?php $form = ActiveForm::begin([
    'type'       => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => ['labelSpan' => 2, 'deviceSize' => ActiveForm::SIZE_SMALL],
]); ?>


 <div class="col-xs-12 " >
        <div class="mb-3">
            <?= $form->field($model, 'nombre')->textInput([
    'placeholder' => 'Ej: HEMATOLOGIA',
    'class' => 'form-control',
]) ?>

        </div>
    </div>



    <div class="col-xs-12 " >
        <div class="mb-3">
           <?= $form->field($model, 'alias')
    ->textInput(['placeholder' => 'Se genera automáticamente si lo dejás vacío',  'class' => 'form-control',])
    ->hint('Solo letras minúsculas sin espacios. Ej: hematologia') ?>,

        </div>
    </div>


<div class="form-group clsgua" align="center">
   <a href="<?= \yii\helpers\Url::to(['index']) ?>" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Volver
        </a>
        <?= Html::submitButton(
            $model->isNewRecord
                ? '<i class="fa fa-save"></i> Crear'
                : '<i class="fa fa-save"></i> Actualizar',
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?> 
        
    
    </div>





<?php ActiveForm::end(); ?>
</div>
