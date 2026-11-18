<?php

use kartik\widgets\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;


//$model = \app\models\Promociones::find()->one();


/* @var $this yii\web\View */
/* @var $model app\models\Productos */

//$this->title = 'Promocion';

$this->title = 'Promocion para Producto: ' . $model->idproducto;
$this->params['breadcrumbs'][] = ['label' => 'Productos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
$format = <<< SCRIPT
function format(state) {
if (!state.id) return state.text; // optgroup
return state.text;
}
SCRIPT;
$escape = new \yii\web\JsExpression("function(m) { return m; }");
$initial = [];
//array_push($initial, \yii\helpers\Url::to(['imagen/productos/' . $model->archivo]));

$this->registerJs($format, \yii\web\View::POS_HEAD); ?>


<section class="content-header">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">

                <div class="box-body ">
                    <?php

                    $form = ActiveForm::begin([
                        //'options' => ['enctype' => 'multipart/form-data'],
                        'options' => ['enctype' => 'multipart/form-data'],
                        // 'type' => ActiveForm::TYPE_HORIZONTAL,
                        //'formConfig' => ['labelSpan' => 2, 'deviceSize' => ActiveForm::SIZE_SMALL]
                    ]); ?>
                    <div class="row">
                        <div class="col-sm-6">

                            <?= $form->field($model, 'foto')->widget(\sadovojav\cutter\Cutter::className(), [
                                'cropperOptions' => [
                                    'aspectRatio' => 562 / 282,
                                ]
                            ]); ?>

                        </div>
                        <div class="col-sm-6">
                            <?= $form->field($model, 'precio')->textInput(['maxlength' => true]) ?>


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

                            <?= $form->field($model, 'fechafinal')->widget(
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
                    <?= Html::a('Cancelar', Yii::$app->request->referrer, ['class' => 'btn btn-danger']) ?>
                    <?= Html::submitButton('Guardar', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        </div>

    </div>
</section>
