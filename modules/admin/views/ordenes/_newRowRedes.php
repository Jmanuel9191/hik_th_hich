<?php
use yii\helpers\Html;
use kartik\select2\Select2;

?>
<div class="redes-input col-xs-12">
        <?= Html::activeHiddenInput($model, "[$index]iddetallecarrito") ?>
    <div class="col-md-3">
        <div <?= ($model->hasErrors('idproducto')) ? 'class="has-error"' : ''; ?> >


            <?php

            echo Select2::widget([
                'model' => $model,
                'attribute' => "[$index]idproducto",
                'data' => \app\models\DatosExtraEvento::getSelectMenu32(),
                'language' => 'es',
                //'onchange'=>'mostrarprecio()',
                'options' => [
                    'placeholder' => 'Productos',
                    //'multiple' => true,
                ],
                'pluginOptions' => [
                    'templateResult' => new \yii\web\JsExpression('format'),
                    'templateSelection' => new \yii\web\JsExpression('format'),
                    //'escapeMarkup' => $escape,
                    'allowClear' => true
                ],
            ]);

             ?>
            <?//= Html::activeHiddenInput($model, "[$index]precio") ?>



        </div>
    </div>
    <div class="col-md-5">
        <div <?= ($model->hasErrors('cantidad')) ? 'class="has-error"' : ''; ?> >
            <?= Html::activeTextInput($model, "[$index]cantidad", ['class' => 'form-control input-sm', 'id' => 'trabajo_' . $index]); ?>
        </div>
    </div>



    <div class="col-md-1">
        <?= Html::a('<i class="fa fa-remove"></i>', '#',
            [
                'class' => 'btn btn-block redes-input-remove',
                'id' => 'add'
            ]
        ) . Html::hiddenInput('cancel', $index, ['class' => 'redes-input-index']);
        ?>
    </div>
</div>

<input type="text" id="valor<?=$index?>" />

<script>

        $('select#datosextraevento-<?=$index?>-idproducto').change(function(){
            $('input#valor<?=$index?>').val($(this).val());
        });

</script>


<script>

        $('input#valor<?=$index?>').change(function(){
            $('#valorpre<?=$index?>').val($(this).val());
        });
</script>




<?php
$proddesta = \app\models\Productos::find()->where(['idproducto' => 'valor'.$index])->limit('9')->all();
foreach ($proddesta as $item2){ ?>
    <?php  echo $item2['precio']; ?>
   <?php break; ?>
<?php } ?>



