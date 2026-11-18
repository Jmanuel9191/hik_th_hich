<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use dosamigos\ckeditor\CKEditor;


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
?>






<div class="box-body">
    <div class="">
    <div class="">
        <div class="col-md-12">
        <h3
        > <strong>Datos cliente</strong>         <a href="<?= Url::to(Yii::$app->request->referrer); ?>" class="btn btn-outline-secondary" style="    BACKGROUND: RED;
    color: white;
" >VOLVER ATRAS</a>
         </h3>
        </div>
<div class="carrito-form">
    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
        'id' => 'formEvento',
    ]); ?>




    <div class="col-md-12">
        <?= $form->field($model, 'nombre')->textInput() ?>

    </div>
    <div class="col-md-12">
        <?= $form->field($model, 'nit')->textInput() ?>

    </div>
  <div class="col-md-12">

           <?= $form->field($model, 'monto_total')->textInput() ?>
    </div>
    
      <div class="col-md-12">
    
        <?= $form->field($model, 'detalle')->textarea(['rows' => 6]) ?>
    
      </div>
    

<div>
    
</div>


    <div class="form-group">
        <div class="col-md-12">
        <?= Html::submitButton($model->isNewRecord ? 'Finalizar' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            <br>
        </div>
    </div>

<div class="form-group" >
        <div class="col-md-12" style="
    background: #f8f8f8;
    margin-top: 20px;
    margin-bottom: 5px;
">

   <input type="hidden" id="caja1" value="<?= Yii::$app->cart->getCost() ?>" placeholder="Precio 1">
   <br>
    Esta pagando con Bs: <input type="text" id="carrito-montopagado" name="Carrito[montopagado]" placeholder="Esta pagando con">
    <br><br>

  

    El cambio es Bs: <input type="text" id="carrito-cambio" name="Carrito[cambio]" placeholder="Clik aqui para ver">
    <div id="aviso"></div>
<br><br>

<br>



</div>
    <?php ActiveForm::end(); ?>



    </div>
    </div>



</div>
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
                        <th  style="font-size: 16px"></th>
                    </tr>
                    </tbody>
                    <tfoot>

                    </tfoot>




                </table>
            </div><!-- End .cart-table-container -->


        </div><!-- End .col-lg-8 -->


    </div><!-- End .row -->
</div><!-- End .container -->

<?php
echo $this->render('scriptForm');
?>


   <script>
        let precio1 = document.getElementById("caja1")
        let precio2 = document.getElementById("carrito-montopagado")
        let precio3 = document.getElementById("carrito-cambio")
        
        precio2.addEventListener("change", () => {
            numb = parseFloat(precio2.value) - parseFloat(precio1.value);
            precio3.value = numb.toFixed(3);

        })
        
    </script>