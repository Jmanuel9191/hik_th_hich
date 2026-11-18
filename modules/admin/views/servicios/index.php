<?php

//use yiister\adminlte\widgets\grid\GridView;
use yii\bootstrap\ButtonDropdown;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\export\ExportMenu;
 use \Mpdf\Mpdf;
//use kartik\widgets\SwitchInput;
/* @var $this yii\web\View */
/* @var $searchModel app\models\Productos_search */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Pruebas';
$this->params['breadcrumbs'][] = $this->title;
 date_default_timezone_set('America/La_Paz');
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
$dias = date("Y").'-'.date("m").'-'.date("d");
$model1 = \app\models\Productos::find()->where(['estado' => '1'])->all();
?>



  <section class="content-header">
    <h1><i class="fa fa-stethoscope"></i> <?= Html::encode($this->title) ?></h1>
</section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">

                    <div class="box-header">
                        <?= Html::a('Nuevo', ['create'], ['class' => 'btn btn-success']) ?>
             
              

                <?php /* Html::a('Nuevo Banner', ['create'], ['class' => 'btn btn-success'])*/
                /*echo ExportMenu::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                      
                        strip_tags('idproducto'),
                        [  'label' => 'Categoria',
                                        'value' => function ($model) {
                                            if (!empty($model->idcategoria))
                                                return $model->categoria['nombre'];
                                            return '';
                                        },
                                       
                                    ],

                        strip_tags('titulo'),                      
                        'resumen',
                        'preciooferta',
                        'precio',
                        'preciocaja',
                        'preciocajaventa',

                        [  'label' => 'Saldo',
                                        'value' => function ($model) {
                                            if (!empty($model->idproducto))
                                                return $model->saldo['saldo'];
                                            return '';
                                        },
                                       
                                    ],


                        'codigo',
                        'fecha_registro',

                    ],
                    'target' => ExportMenu::TARGET_BLANK,
                    'fontAwesome' => true,
                    'stream' => false,
                    'filename' => 'productos'
                ]);*/
                ?>

                    </div>



                        <?php   

                                    function ($model) {                                       


                           
                                              
                                        }



                                              ?>
                    <div class="box-body " id="printTable">

                        <?php Pjax::begin(['id' => 'table']); ?>
                        <div class="table-responsive">
                            <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                               
                                'columns' => [
                               
                                          ['class' => 'yii\grid\SerialColumn'],
                                   

                                    [
                                        'header' => 'Categoria',
                                        'value' => function ($model) {  
                                            


                                            if (!empty($model->idcategoria))
                                                return $model->categoria['nombre'];
                                            return '';
                                        },
                                        'filter' => \kartik\widgets\Select2::widget([
    'model' => $searchModel,
    'attribute' => 'idcategoria',
    'data' => \app\models\CategoriasServicio::getSelectMenu('servicios', 'productosg'),
    'language' => 'es',
    'options' => [
        'placeholder' => 'Categorias',
    ],
    'pluginOptions' => [
        'templateResult' => new \yii\web\JsExpression('format'),
        'templateSelection' => new \yii\web\JsExpression('format'),
        'escapeMarkup' => $escape,
        'allowClear' => true
    ],
])
                                    ],
                                    'titulo',                                    
                                    //'codigo',
                                    
                                    'precio',
                                    'preciooferta',
                                          // 'descripcion',                          
                                    //'preciocaja',
                                    //'preciocajaventa',


                                    
                                    

                                    /*[
                                        'header' => 'Foto',
                                        'format' => 'raw',
                                        'value' => function ($model) {
                                            $img = \yii\helpers\Url::to($model->foto);
                                            return "<img src='{$img}' class='img-thumbnail' style='min-width: 100px'>";
                                        },
                                        'contentOptions' => ['class' => 'col-md-2']
                                    ],*/

                                   /* [
                                        'class' => 'yii\grid\ActionColumn',
                                        'header' => 'Estado',
                                        'template' => '<div class="btn-group btn-group-justified" role="group">{estado}{destacado}</div>',
                                        'buttons' => [
                                            'estado' => function ($url, $model, $key) {
                                                return Html::a('<i class="toggle fa fa-eye"></i>', "#", ['class' => 'btn btn-' . (($model->estado) ? 'success' : 'default'), 'onclick' => "action('{$url}'); return false;", "title" => "Visible"]);
                                            },
                                            'destacado' => function ($url, $model, $key) {
                                                return Html::a('<i class="fa fa-star"></i>', "#", ['class' => 'btn btn-' . (($model->destacado) ? 'success' : 'default'), 'onclick' => "action('{$url}'); return false;", "title" => "Destacado"]);
                                            },
                                        ],
                                        'contentOptions' => ['class' => 'col-sm-1']
                                    ],*/


     [
    'class' => 'yii\grid\ActionColumn',
    'header' => 'Operaciones',
    'template' => '<div class="btn-group btn-group-justified" role="group">{estado}{update}{delete}{parametros}{materiales}</div>',
    'buttons' => [
        'estado' => function ($url, $model, $key) {
            return Html::a('<i class="toggle fa fa-eye"></i>', "#", [
                'class' => 'btn btn-' . (($model->estado) ? 'success' : 'danger'),
                'title' => 'Estado',
                'onclick' => "if (confirm('¿Está seguro?')) { action('{$url}'); } return false;"
            ]);
        },
        'update' => function ($url, $model, $key) {
            return Html::a('<i class="fa fa-pencil"></i>', $url, ['class' => 'btn btn-info']);
        },
        'delete' => function ($url, $model, $key) {
            return Html::a('<i class="fa fa-trash"></i>', $url, ['class' => 'btn btn-danger', 'data' => [
                'confirm' => 'Esta seguro?.'
            ],]);
        },
        'parametros' => function ($url, $model, $key) {
            return Html::a('<i class="fa fa-cogs"></i>', $url, ['class' => 'btn btn-info', 'title' => 'Parámetros']);
        },
        'materiales' => function ($url, $model, $key) {
            return Html::a('<i class="fa fa-book"></i>', $url, ['class' => 'btn btn-warning', 'title' => 'Materiales']);
        },
    ],
    'contentOptions' => ['class' => 'col-sm-2']
],





                                    /*[
                                        'class' => 'yii\grid\ActionColumn',
                                        'header' => 'Operaciones',
                                        'template' => '<div class="btn-group btn-justified" role="group">{all}</div>',
                                        'buttons' => [
                                            'all' => function ($url, $model, $key) {
                                                return ButtonDropdown::widget([
                                                    'encodeLabel' => false,
                                                    'label' => '<i class="glyphicon glyphicon-menu-hamburger"></i>',   // if you're going to use html on the button label
                                                    'dropdown' => [
                                                        'encodeLabels' => false, // if you're going to use html on the items' labels
                                                        'items' => [

                                                            [
                                                                'label' => '<i class="fa fa-pencil"></i>Editar',
                                                                'url' => ['update', 'id' => $key],
                                                                'visible' => true,  // if you want to hide an item based on a condition, use this
                                                            ],
                                                            [
                                                                'icon' => 'fa fa-trash',
                                                                'label' => \Yii::t('yii', '<i class="fa fa-trash"></i>Eliminar'),
                                                                'linkOptions' => [
                                                                    'data' => [
                                                                        'method' => 'post',
                                                                        'confirm' => \Yii::t('yii', 'Esta seguro?'),
                                                                    ],
                                                                ],
                                                                'url' => ['delete', 'id' => $key],
                                                                'visible' => true,   // same as above
                                                            ],

                                                            '<hr style=" border-color: #ddd currentcolor currentcolor; margin-bottom: 8px; margin-top: 8px;">',


                                                           /* [
                                                                'label' => \Yii::t('yii', '<i class="fa fa-bullhorn"></i>Promociones '),
                                                                'url' => ['promocion', 'id' => $key],
                                                                'visible' => true,  // if you want to hide an item based on a condition, use this
                                                            ],*44
                                                            [
                                                                'label' => \Yii::t('yii', '<i class="fa fa-pencil"></i>Inventario'),
                                                                'url' => ['inventario', 'id' => $key],
                                                                'visible' => true,  // if you want to hide an item based on a condition, use this
                                                            ],


                                                        ],
                                                    ],
                                                    'options' => [
                                                        'class' => 'btn-default',
                                                        // btn-success, btn-info, et cetera
                                                    ],
                                                ]);
                                            },

                                        ],
                                        'contentOptions' => ['class' => 'col-sm-2']
                                    ],*/


                                ],
                            ]); ?>
                        </div>
                        <?php Pjax::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
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

<script>
    function printData()
    {
        var divToPrint=document.getElementById("printTable");
        newWin= window.open("");
        newWin.document.write(divToPrint.outerHTML);
        newWin.document.write('<link href="Your-Path/../../assets_b/css/bootstrap.min.css" rel="stylesheet" />');


        newWin.print();
        newWin.close();
    }

    $('button.cim').on('click',function(){
        printData();
    })




</script>

<style type="text/css">
    
       .cim{ padding: 5px 11px 7px;
    /* margin-top: 0px; */
    border: 1px solid #dddddd;
    border-radius: 5px;
}
</style>