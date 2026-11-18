<?php
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Carrito */
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
$datos = new \app\models\Detallecarrito();
$searchModel = new \app\models\ProductosSearch();
$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
$dataProvider->setSort([
    'defaultOrder' => ['fecha_vencimiento' => SORT_ASC]]);
?>


<?php

$format = <<< SCRIPT
function format(state) {
//Sif (!state.id) return state.text; // optgroup
return state.text;
}
SCRIPT;
$escape = new \yii\web\JsExpression("function(m) { return m; }");
$this->registerJs($format, \yii\web\View::POS_HEAD);




?>


<?php if (Yii::$app->session->hasFlash('mensaje')) : ?>
    <?php $message = Yii::$app->session->getFlash('mensaje'); ?>
    <?= \kartik\widgets\Growl::widget([
        'type' => (!empty($message['type'])) ? $message['type'] : 'danger',
        'icon' => (!empty($message['icon'])) ? $message['icon'] : 'fa fa-info',
        'body' => (!empty($message['message'])) ? \yii\helpers\Html::encode($message['message']) : 'Menjase Desconocido!',
        'delay' => 1, //This delay is how long before the message shows
        'pluginOptions' => [
            'delay' => (!empty($message['duration'])) ? $message['duration'] : 9000, //This delay is how long the message shows for
            'placement' => [
                'from' => (!empty($message['positonY'])) ? $message['positonY'] : 'top',
                'align' => (!empty($message['positonX'])) ? $message['positonX'] : 'right',
            ]
        ]
    ]); ?>
<?php endif; ?>


    <section class="content">
    <div class="row">
    <div class="col-xs-12">
    <div class="box">

    <div class="box-body ">
<?php Pjax::begin(['id' => 'table']); ?>
<div class="table-responsive">
    <?= GridView::widget([

        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,  

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'header' => 'presentacion',
                'value' => function ($model) {
                    if (!empty($model->idcategoria))
                        return $model->categoria['nombre'];
                    return '';
                },
                'filter' => \kartik\widgets\Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'idcategoria',
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
                ])
            ],
            'idproducto',
            'titulo',
            
            //'codigo',
            'precio',
            //'preciocajaventa',

            
 



            [
                'header' => 'stok',
                'value' => function ($model) {


$dias = date("Y").'-'.date("m").'-'.date("d");
                                           $trdias = date("Y-m-d",strtotime($model->fecha_vencimiento."- 3   month"));   
                                                 if (empty($dias >= $trdias)){
                                                echo "<style>  tr[data-key='".$model->idproducto."']{color:#333; } </style>";
                                          
                                                }else{
                                                     echo "<style>  tr[data-key='".$model->idproducto."']{color:#333; } </style>";
                                                   

                                                }   

                    if (!empty($model->idproducto))
                        return $model->saldo['saldo'];
                    return '';
                },



            ],



            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Operaciones',
                'template' => '<div class="btn-group btn-group-justified" role="group">{adda}</div>',
                'buttons' => [
                    'adda' => function ($url, $model, $key) {

                        return Html::beginForm(['carrito/add'], 'get')
                                .' <input type="number" class="horizontal-quantity form-control" value="'. Yii::$app->request->get('cantidad') . ' " name="cantidad"
                                           style="width: 43%" placeholder="0"  min="1" required>'
                                .'<input type="hidden" value="'. $model['idproducto']. '" name="id">'
                            .'  <input type="submit" name="enviar" id="submit_contact" class="btn btn-info"
                                       value="Añadir">'
                        .Html::endForm();
                        //return Html::beginForm('<i class="fa fa-pencil"></i> ', [$url, 'rem_id' => $model->idproducto],['class' => 'btn btn-info']);
                        //return Html::a('<i class="fa fa-pencil"></i> ', [$url, 'rem_id' => $model->idproducto],['class' => '']);
                        //return Html::textInput([$url , 'rem_id' => $model->idproducto ,array("style"=>"width:4px;")]);
                        },

                ],
                'contentOptions' => ['class' => ' col-sm-2'],

            ],



        ],


    ]
    ); ?>



</div>






    <div class="container">
        <div class="row">
            <br>
            <div class="col-md-11">
                <h3
                > <strong>Detalle de Compra</strong></h3>

                <div class="cart-table-container">
                    <table class="table table-cart">
                        <thead style="
    background: #e5e5e5;
">



                        <tr>
                            <th class="product-col">Producto</th>
                            <th class="price-col">Precio (Bs)</th>
                            <th class="qty-col">Cantidad</th>
                            <th class="price-col">Sub total (Bs)</th>
                            <th class="price-col"> </th>
                        </tr>
                        </thead>
                        <tbody>




                        <?php foreach (Yii::$app->cart->positions as $item):
                            ?>







                            <tr class="product-row">
                                <td class="product-col">

                                        <?= $item->producto->titulo ?>
                                </td>
                                <td>
                                    <?= $item->precio ?> Bs.
                                </td>
                                <td><?= $item->quantity ?></td>



                                <td>

                                    <?php $subt = $item->quantity *   $item->precio; ?>
                                    <?= $subt ?> Bs.
                                </td>

                                <td>
                                    <a style="    font-weight: 500;
    font-size: 15px;
    color: white;"   href="<?= \yii\helpers\Url::to(['remove', 'id' => $item->idproducto] ) ?>" title="Remove product" data-confirm="Esta seguro?." class="btn btn-danger">
                                        <i class="fa fa-trash"></i>

                                    </a>

                                </td>


                            </tr>





                        <?php endforeach; ?>
                        </tbody>



                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>


                        <tbody>
                        <tr>
                            <th></th>
                            <th></th>
                            <th style="font-size: 16px"><strong>TOTAL:</strong> <?= Yii::$app->cart->getCount() ?> </th>
                            <th  style="font-size: 16px"><strong>TOTAL:</strong> <?= Yii::$app->cart->getCost() ?> Bs</th>
                            <th  style="font-size: 16px"> <?php
                                // if (empty(Yii::$app->session->get('user'))) {
                                //    echo Html::a('REALIZAR PAGO', Url::to(['site/login']), ['class' => 'btn btn-success btnpag', 'data-target' => '#squarespaceModal']);
                                echo Html::a('Finalizar Compra', Url::to(['create', 'pasos' => '1']), ['class' => 'btn btn-block btn-sm btn-primary']);
                                /*} else {
                                    echo Html::a('REALIZAR PAGO', Url::to(['pasos', 'pasos' => '1']), ['class' => 'btn btn-success btnpag']);
                                }*/
                                ?></th>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                        <td colspan="4" class="clearfix">
                            <div class="float-right">
                            <div class="checkout-methods">

                            </div><!-- End .checkout-methods -->
                            </div><!-- End .checkout-methods -->

                        </td>
                        </tr>
                        </tfoot>




                    </table>
                </div><!-- End .cart-table-container -->


            </div><!-- End .col-lg-8 -->


        </div><!-- End .row -->
    </div><!-- End .container -->

    <div class="mb-6"></div><!-- margin -->


    <style>
        .btn.btn-block.btn-sm.btn-primary{
            width: 100%;
            font-size: 14px;
            font-weight: 600;
            background: green;
            padding: 10px;
        }
    </style>

<?php
echo $this->render('scriptForm');
?>
<?php
$script = <<<JS
    function action(url)
    {
    $.get(url, function(data) {
      $.pjax.reload({container:"#table"});
    });
    }

JS;
$this->registerJs($script, \yii\web\View::POS_HEAD);

?>