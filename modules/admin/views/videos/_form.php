<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Videos */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box-body">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'titulo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'descripcion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>

    <?php /*= $form->field($model, 'tumb')->textInput(['maxlength' => true]) */ ?>

    <?= $form->field($model, 'idcategoria')->widget(\kartik\widgets\Select2::classname(), [
        'data' => \yii\helpers\ArrayHelper::map(\app\models\Categoria::find()->where(['=', 'modulo', \app\models\Modulos::findOne(['alias' => 'videos'])['idmodulo']])->all(), 'idcategoria', 'nombre'),
        'language' => 'es',
        'options' => ['placeholder' => 'Categorias'],
    ]); ?>

    <?php /*= $form->field($model, 'tags')->textarea(['rows' => 6]) */ ?>

    <?php /*= $form->field($model, 'posicion')->textInput() */ ?>

    <?php /*= $form->field($model, 'fecha_video')->textInput() */ ?>

    <?php /*= $form->field($model, 'fecha_registro')->textInput() */ ?>

    <?php /*= $form->field($model, 'estado')->textInput(['maxlength' => true]) */ ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
