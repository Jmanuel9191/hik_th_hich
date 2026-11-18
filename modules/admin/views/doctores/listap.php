<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
$this->title = 'TAREAS';
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


?>






    <div class="app-wrapper">
        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">

                <div class="row g-3 mb-4 align-items-center justify-content-between">
                    <div class="col-12 optionstable">
                        <h1><?= Html::encode($this->title) ?> </h1>
                        <?= Html::a('Nueva Tarea', ['create'], ['class' => 'btn btn-success btncreate']) ?>

                    </div>
                    <div class="tab-content" id="orders-table-tab-content">
                        <div class="app-card app-card-orders-table shadow-sm mb-5">
                            <div class="app-card-body">
                                <div class="box-body ">
                                    <?php Pjax::begin(['id' => 'table']); ?>
                                    <div class="table-responsive">
                                        <?= GridView::widget([
                                            'dataProvider' => $dataProvider,
                                            'filterModel' => $searchModel,
                                            'columns' => [
                                                ['class' => 'yii\grid\SerialColumn'],
                                                'titulo',
                                                [
                                                    'attribute' => 'descripcion2',
                                                    'value' => function ($model) {
                                                        return  \yii\helpers\StringHelper::truncate(strip_tags($model->descripcion), 100) ;
                                                    },
                                                ],
                                                [
                                                    'header' => 'Modulo',
                                                    'filter' => \kartik\widgets\Select2::widget([
                                                        'model' => $searchModel,
                                                        'attribute' => 'modulo',
                                                        'data' => \yii\helpers\ArrayHelper::map(\app\models\Modulos::findAll(['estado' => '1']), 'idmodulo', 'nombre'),
                                                        'options' => ['placeholder' => ''],
                                                        'pluginOptions' => [
                                                            'allowClear' => true
                                                        ],
                                                    ]),
                                                    'value' => function ($model) {
                                                        if (!empty($model->modulos))
                                                            return $model->modulos['nombre'];
                                                        return '';
                                                    },
                                                ],


                                                [
                                                    'header' => 'Tipo de tarea',
                                                    'filter' => \kartik\widgets\Select2::widget([
                                                        'model' => $searchModel,
                                                        'attribute' => 'modulo',
                                                        'data' => \yii\helpers\ArrayHelper::map(\app\models\TipoTareas::findAll(['estado' => '1']), 'idtipo_tarea ', 'nombre'),
                                                        'options' => ['placeholder' => ''],
                                                        'pluginOptions' => [
                                                            'allowClear' => true
                                                        ],
                                                    ]),
                                                    'value' => function ($model) {
                                                        if (!empty($model->tipoTarea))
                                                            return $model->tipoTarea['nombre'];
                                                        return '';
                                                    },
                                                ],



                                                [
                                                    'class' => 'yii\grid\ActionColumn',
                                                    'header' => 'Operaciones',
                                                    'template' => '<div class="btn-group btn-group-justified" role="group">{view}{update}</div>',
                                                    'buttons' => [
                                                        'view' => function ($url, $model, $key) {
                                                            return Html::a('<i class="toggle fa fa-eye"></i>', $url, ['class' => 'btn btn-primary',"title"=>"Ver"]);
                                                        },
                                                        'update' => function ($url, $model, $key) {
                                                            return Html::a('<i class="fa fa-pencil"></i>', $url, ['class' => 'btn btn-info',"title"=>"Editar"]);
                                                        },

                                                    ],
                                                    'contentOptions' => ['class' => 'col-sm-2']
                                                ],

                                                [
                                                    'class' => 'yii\grid\ActionColumn',
                                                    'header' => 'Estado',
                                                    'template' => '<div class="btn-group btn-group-justified" role="group">{estado}</div>',
                                                    'buttons' => [
                                                        'estado' => function ($url, $model, $key) {
                                                            return Html::a('<i class="toggle fa fa-unlock"></i>', "#", [
                                                                'class' => 'btn btn-' . (($model->estado) ? 'success' : 'danger'),
                                                                'title' => 'Estado',
                                                                'data' => [
                                                                    'confirm' => '¿Está seguro?',
                                                                    'method' => 'post', // Define que será una acción POST
                                                                    'pjax' => 0, // Evita problemas con PJAX si es necesario
                                                                ],
                                                                'onclick' => "{ action('{$url}'); } return false;"
                                                            ]);
                                                        },

                                                    ],
                                                    'contentOptions' => ['class' => 'col-sm-1']
                                                ],

                                            ],
                                        ]); ?>
                                    </div>
                                    <?php Pjax::end(); ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
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