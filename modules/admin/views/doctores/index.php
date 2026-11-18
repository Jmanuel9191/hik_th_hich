<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UsuariosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$format = <<< SCRIPT
function format(state) {
if (!state.id) return state.text; // optgroup
return state.text;
}
SCRIPT;
$escape = new \yii\web\JsExpression("function(m) { return m; }");
$this->registerJs($format, \yii\web\View::POS_HEAD);

$this->title = 'Doctores';
$this->params['breadcrumbs'][] = $this->title;
?>


<section class="content-header">
    <h1><i class="fa fa-user-md"></i> <?= Html::encode($this->title) ?></h1>
</section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">

                    <div class="box-header">
                        <?= Html::a('Nuevo', ['create'], ['class' => 'btn btn-success']) ?>
                    </div>

                    <div class="box-body ">

                        <?php Pjax::begin(['id' => 'table']); ?>
                        <div class="table-responsive">   <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'columns' => [
                                    ['class' => 'yii\grid\SerialColumn'],

                                    //'idusuario',
                                    'nombre',
                                    'descripcion',
                                       'telefono',
                                    // 'pais',
                                    // 'ciudad',
                                    'email:email',
                                    // 'contrasena',
                                    // 'fecha_nacimiento',
                                    // 'movil',
                                    // 'fecha_registro',
                                    // 'estado',
                                    // 'sexo',
                                    // 'activo',
                                    

                                    [
                                        'class' => 'yii\grid\ActionColumn',
                                        'header' => 'Operaciones',
                                        'template' => '<div class="btn-group btn-group-justified" role="group">{view}{update}{delete}</div>',
                                        'buttons' => [
                                            'view' => function ($url, $model, $key) {
                                                return Html::a('<i class="fa fa-eye"></i>', $url, ['class' => 'btn btn-success', "title" => "Editar"]);
                                            },
                                            'update' => function ($url, $model, $key) {
                                                return Html::a('<i class="fa fa-pencil"></i>', $url, ['class' => 'btn btn-info']);
                                            },
                                            'delete' => function ($url, $model, $key) {
                                                return Html::a('<i class="fa fa-trash"></i>', $url, ['class' => 'btn btn-danger', 'data' => [
                                                    'confirm' => 'Esta seguro?.'
                                                ], "title" => "Eliminar"]);
                                            },
                                        ],
                                        'contentOptions' => ['class' => 'col-sm-2']
                                    ],
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

