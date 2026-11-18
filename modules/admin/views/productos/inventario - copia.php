<?php

use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;

//$model = \app\models\Promociones::find()->one();


/* @var $this yii\web\View */
/* @var $model app\models\Productos */

$this->title = 'Inventario';

$id="";
@$nombre = $_GET['id'];
$cac = \app\models\Inventario::find()->where(['idproducto' => $producto['idproducto']])->orderBy(['idinventario' => SORT_DESC])->limit(1)->all();

?>

<style>
    .bg-red, .callout.callout-danger, .alert-danger, .alert-error, .label-danger, .modal-danger .modal-body {
        background-color: #2196f3 !important;
    }


    .btn-danger {
        background-color: #2196f3!important;
        border-color: #2196f3!important;
    }
</style>


<section class="content-header">

    <h1><?= Html::encode($this->title) ?> de <?= $producto['titulo'] ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-sm-4">
            <div class="box">

                <div class="box-body " align="center">
                    <?php

                    $form = ActiveForm::begin([
                        'type' => ActiveForm::TYPE_HORIZONTAL,
                        'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL]
                    ]); ?>
                    <?= $form->field($model, 'movimiento')->textInput(['maxlength' => true, 'value' => 0, 'placeholder' => '0']) ?>
                    <?= $form->field($model, 'estado')->dropDownList(\app\models\Inventario::tipos()) ?>
                    <?= Html::a('Cancelar', Yii::$app->request->referrer, ['class' => 'btn btn-default']) ?>
                    <?= Html::submitButton('Guardar', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    <?php ActiveForm::end(); ?>

                </div>
            </div>

            <div class="info-box">
                <!-- Apply any bg-* class to to the icon to color it -->
                <span class="info-box-icon bg-red"><i class="fa fa-archive"></i></span>




                <?php

       

               foreach ($cac as $item1) {
                ?>
                <div class="info-box-content">
                    <span class="info-box-text">Saldo</span>

                    <span class="info-box-number">
                        <?php echo $item1['saldo']?>

                    </span>
                </div>
                <?php } ?>


                <!-- /.info-box-content -->
            </div>


        </div>


        <div class="col-sm-8">
            <div class="box">

                <div class="box-header with-border">
                    <h3 class="box-title">Historial de Movimientos</h3>
                </div>
                <div class="box-body">
                    <?= \yii\grid\GridView::widget([
                        'dataProvider' => $lista,
                        //'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            'fecha_registro',
                            'movimiento',

                            'detalle',
                            [
                                'attribute' => 'estado',
                                'value' => function ($model) {
                                    return \app\models\Inventario::tipos($model->estado);
                                },
                            ],
                        ],
                    ]); ?>

                </div>
            </div>

        </div>
    </div>
</section>
