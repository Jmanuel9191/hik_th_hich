<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
$this->title = 'CAMPAÑAS';
$format = <<< SCRIPT
function format(state) {
if (!state.id) return state.text; // optgroup
return state.text;
}
SCRIPT;
$escape = new \yii\web\JsExpression("function(m) { return m; }");
$this->registerJs($format, \yii\web\View::POS_HEAD);


?>

        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">

                <div class="row g-3 mb-4 align-items-center justify-content-between">
                    <div class="col-12 optionstable">
                        <h1><?= Html::encode($this->title) ?> </h1>
                        <?= Html::a('Nueva campaña', ['create'], ['class' => 'btn btn-success btncreate']) ?>
                    </div>




                    <div class="tab-content" id="orders-table-tab-content">
                        <div class="app-card app-card-orders-table shadow-sm mb-5">
                            <div class="app-card-body">
                                <div class="box-body ">
                                    <?php \yii\widgets\Pjax::begin(['id' => 'table']); ?>
                                    <div class="table-responsive">
                                        <?= GridView::widget([
                                            'dataProvider' => $dataProvider,
                                            'filterModel' => $searchModel,
                                            'filterSelector' => 'input, select', // esto asegura que
                                            'columns' => [
                                                ['class' => 'yii\grid\SerialColumn'],
                                                'titulo',

                                                //'idnoticia',
                                                [
                                                    'header' => 'cliente',
                                                    'value' => function ($model) {
                                                        if (!empty($model->idcliente))
                                                            return $model->usuario['nombrecompleto'];
                                                        return '';
                                                    },
                                                     'filter' => \kartik\widgets\Select2::widget([
                                                         'model' => $searchModel,
                                                         'attribute' => 'idcliente',

                                                         'data' => \app\models\Usuarios::getSelectCliente(),
                                                         'language' => 'es',
                                                         'options' => [
                                                             'name' => 'CampaniasSearch[idcliente]',
                                                             'placeholder' => 'Clientes',
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

                                                [
                                                    'header' => 'prioridad',
                                                    'value' => function ($model) {
                                                        if (!empty($model->prioridad))
                                                            return $model->prioridad;
                                                        return '';
                                                    },
                                                    'filter' => \kartik\widgets\Select2::widget([
                                                        'model' => $searchModel,
                                                        'attribute' => 'prioridad',

                                                        'data' => \app\models\Campanias::getPrioridad(),
                                                        'language' => 'es',
                                                        'options' => [
                                                            'name' => 'CampaniasSearch[prioridad]',
                                                            'placeholder' => 'Prioridad',
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

                                                [
                                                    'header' => 'Fecha inicial',
                                                    'filter' =>  \kartik\date\DatePicker::widget([
                                                        'model' => $searchModel,
                                                        'language' => 'es',
                                                        'attribute' => 'fecha_inicio',

                                                        //'data' => \yii\helpers\ArrayHelper::map(Modulos::findAll(['estado' => '1']), 'idmodulo', 'nombre'),
                                                        'options' => [ 'name' => 'CampaniasSearch[fecha_inicio]', 'placeholder' => ''],
                                                        'pluginOptions' => [
                                                            'autoclose' => true,
                                                            'format' => 'yyyy-mm-dd',
                                                            'todayBtn' => "linked",
                                                            'keyboardNavigation' => false,
                                                            'forceParse' => false,
                                                            'allowClear' => true

                                                        ],
                                                    ]),
                                                    'value' => 'fecha_inicio'
                                                ],

                                                [
                                                    'header' => 'Fecha final',
                                                    'filter' =>  \kartik\date\DatePicker::widget([
                                                        'model' => $searchModel,
                                                        'language' => 'es',
                                                        'attribute' => 'fecha_fin',

                                                        //'data' => \yii\helpers\ArrayHelper::map(Modulos::findAll(['estado' => '1']), 'idmodulo', 'nombre'),
                                                        'options' => ['name' => 'CampaniasSearch[fecha_fin]', 'placeholder' => ''],
                                                        'pluginOptions' => [
                                                            'autoclose' => true,
                                                            'format' => 'yyyy-mm-dd',
                                                            'todayBtn' => "linked",
                                                            'keyboardNavigation' => false,
                                                            'forceParse' => false,
                                                            'allowClear' => true

                                                        ],
                                                    ]),
                                                    'value' => 'fecha_fin'
                                                ],
                                                [
                                                    'attribute' => 'estado',
                                                    'label' => 'Estado',
                                                    'value' => function ($model) {
                                                        return \app\models\Campanias::obtenerTextoEstado($model->estado);
                                                    },
                                                    'filter' => \kartik\widgets\Select2::widget([
                                                        'model' => $searchModel,
                                                        'attribute' => 'estado',
                                                        'data' => \app\models\Campanias::getEstadosCampanias(),
                                                        'options' => [
                                                            'placeholder' => 'Tipo de estados',
                                                        ],
                                                        'pluginOptions' => [
                                                            'allowClear' => true,
                                                        ],
                                                    ]),
                                                ],
                                                [
                                                    'attribute' => 'finalizado',
                                                    'label' => 'V/F',
                                                    'value' => function ($model) {
                                                        return \app\models\Campanias::obtenerTextoFinalizado($model->finalizado);
                                                    },
                                                    'filter' => \kartik\widgets\Select2::widget([
                                                        'model' => $searchModel,
                                                        'attribute' => 'finalizado',
                                                        'data' => \app\models\Campanias::getFinalizadoProyectos(),
                                                        'options' => [
                                                            'placeholder' => 'Status',
                                                        ],
                                                        'pluginOptions' => [
                                                            'allowClear' => true,
                                                        ],
                                                    ]),
                                                ],

                                                //'resumen:ntext',
                                                // 'fuente',
                                                // 'url:url',
                                                // 'fecha_registro',
                                                // 'fecha_noticia',

                                                /*[
                                                    'class' => 'yii\grid\ActionColumn',
                                                    'header' => 'Posicion',
                                                    'template' => '<div class="btn-group btn-group-justified" role="group">{up}{down}</div>',
                                                    'buttons' => [
                                                        'up' => function ($url, $model, $key) {
                                                            if ($model->idnoticia > 1) {
                                                                return Html::a('<i class="fa fa-chevron-up toggle"></i>', "#", ['class' => 'btn btn-info','onclick'=>"action('{$url}'); return false;", "title"=>"Arriba"]);
                                                            }
                                                            return Html::a('<i class="fa fa-chevron-up"></i>', '#', ['class' => 'btn btn-info disable']);
                                                        },
                                                        'down' => function ($url, $model, $key) {
                                                            return Html::a('<i class="fa fa-chevron-down toggle"></i>', "#", ['class' => 'btn btn-info','onclick'=>"action('{$url}'); return false;","title"=>"Abajo"]);
                                                        },
                                                    ],
                                                    'contentOptions' => ['class' => 'col-sm-1']
                                                ],*/
                                                [
                                                    'class' => 'yii\grid\ActionColumn',
                                                    'header' => 'Operaciones',
                                                    'template' => '<div class="btn-group btn-group-justified" role="group">{view}{historial}{update}</div>',
                                                    'buttons' => [
                                                        'view' => function ($url, $model, $key) {
                                                            return Html::a('<i class="toggle fa fa-eye"></i>', $url, ['class' => 'btn btn-primary',"title"=>"Ver"]);
                                                        },
                                                        'historial' => function ($url, $model, $key) {
                                                            return Html::a('<i class="toggle fa fa-book"></i>', $url, ['class' => 'btn btn-secondary',"title"=>"Historial"]);
                                                        },
                                                        'update' => function ($url, $model, $key) {
                                                            return Html::a('<i class="fa fa-pencil"></i>', $url, ['class' => 'btn btn-info',"title"=>"Editar"]);
                                                        },

                                                    ],
                                                    'contentOptions' => ['class' => 'col-sm-2']
                                                ],



                                                [
                                                    'class' => 'yii\grid\ActionColumn',
                                                    'header' => 'Estado / Finalizar',
                                                    'template' => '<div class="btn-group btn-group-justified" role="group">{estado} {finalizar}</div>',
                                                    'urlCreator' => function ($action, $model, $key, $index) {
                                                        if ($action === 'estado') {
                                                            return \yii\helpers\Url::to(['estado', 'id' => $model->idcampania]);
                                                        }
                                                        if ($action === 'finalizar') {
                                                            return '#'; // No va a un enlace, lo maneja JS
                                                        }
                                                        return '#';
                                                    },
                                                    'buttons' => [
                                                        'estado' => function ($url, $model, $key) {
                                                            $iconClass = $model->estado ? 'fa fa-unlock' : 'fa fa-lock';
                                                            $btnClass = $model->estado ? 'btn btn-success sep' : 'btn btn-danger';
                                                            return Html::a("<i class='$iconClass'></i>", "#", [
                                                                'class' => $btnClass,
                                                                'title' => 'Bloquear / Desbloquear',
                                                                'onclick' => "cambiarEstadocampania('{$url}'); return false;"
                                                            ]);
                                                        },
                                                        'finalizar' => function ($url, $model, $key) use ($campaniasFinalizables) {
                                                            if ($model->finalizado == 2) {
                                                                // 🔒 Proyecto ya finalizado → Mostrar botón verde deshabilitado
                                                                return Html::a('<i class="fa fa-check"></i>', 'javascript:void(0);', [
                                                                    'class' => 'btn btn-success disabled',
                                                                    'title' => 'Campaña ya finalizado',
                                                                    'style' => 'pointer-events: none; opacity: 0.7;',
                                                                ]);
                                                            }

                                                            if (!empty($campaniasFinalizables[$model->idcampania])) {
                                                                // ✅ Mostrar botón funcional para finalizar
                                                                return Html::a('<i class="fa fa-check"></i>', '#', [
                                                                    'class' => 'btn btn-primary btn-finalizar-campania',
                                                                    'title' => 'Finalizar Campaña',
                                                                    'data-idcampania' => $model->idcampania,
                                                                ]);
                                                            }

                                                            return ''; // No mostrar nada si aún no se puede finalizar
                                                        },
                                                    ],
                                                    'contentOptions' => ['class' => 'col-sm-1'],
                                                ],

                                            ],
                                        ]);  ?>
                                    </div>
                                    <?php Pjax::end(); ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<style>
    div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-confirm) {     background-color: #2879dd!important;     font-size: 1.2em!important}
    div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-cancel){      font-size: 1.2em!important;    }
    div:where(.swal2-container) div:where(.swal2-popup) {      font-size: 1.3rem!important;   }


</style>

<?php

$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    ['position' => \yii\web\View::POS_HEAD]
);
$this->registerJsFile(
    '@web/assets_b/js/estadoscampa.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);

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


