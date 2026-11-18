<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
$this->title = 'PROYECTOS';
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
                        <h1><?= Html::encode($this->title);

                            ?> </h1>

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

                                                // Cliente: visible solo si el rol NO es cliente
                                                 [
                                                    'header' => 'Cliente',
                                                    'value' => function ($model) {
                                                        return $model->usuario->nombrecompleto ?? '-';
                                                    },
                                                    'filter' =>
                                                        Yii::$app->user->identity->rol->alias != 'cliente' ?
                                                        \kartik\widgets\Select2::widget([
                                                        'model' => $searchModel,
                                                        'attribute' => 'idcliente',
                                                        'data' => \app\models\Usuarios::getSelectCliente(),
                                                        'language' => 'es',
                                                        'options' => ['placeholder' => 'Clientes'],
                                                        'pluginOptions' => [
                                                            'templateResult' => new \yii\web\JsExpression('format'),
                                                            'templateSelection' => new \yii\web\JsExpression('format'),
                                                            'escapeMarkup' => new \yii\web\JsExpression('function(m) { return m; }'),
                                                            'allowClear' => true,
                                                        ],
                                                    ]): '',
                                                 ],

                                                // Gestor: visible para todos
                                                [
                                                    'header' => 'Gestor',
                                                    'value' => function ($model) {
                                                        return $model->gestor->nombrecompleto ?? '-';
                                                    },
                                                    'filter' =>

                                                            \kartik\widgets\Select2::widget([
                                                                'model' => $searchModel,
                                                                'attribute' => 'idgestor',
                                                                'data' => \app\models\Usuarios::getSelectGestor(),
                                                                'language' => 'es',
                                                                'options' => ['placeholder' => 'Gestor'],
                                                                'pluginOptions' => [
                                                                    'templateResult' => new \yii\web\JsExpression('format'),
                                                                    'templateSelection' => new \yii\web\JsExpression('format'),
                                                                    'escapeMarkup' => new \yii\web\JsExpression('function(m) { return m; }'),
                                                                    'allowClear' => true,
                                                                ],
                                                            ])


                                                ],



                                                // Fecha inicio con filtro
                                                [
                                                    'header' => 'Fecha inicial',
                                                    'filter' =>  \kartik\date\DatePicker::widget([
                                                        'model' => $searchModel,
                                                        'language' => 'es',
                                                        'attribute' => 'fecha_inicio',
                                                        'options' => ['placeholder' => ''],
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

                                                // Fecha fin con filtro
                                                [
                                                    'header' => 'Fecha final',
                                                    'filter' =>  \kartik\date\DatePicker::widget([
                                                        'model' => $searchModel,
                                                        'language' => 'es',
                                                        'attribute' => 'fecha_fin',
                                                        'options' => ['placeholder' => ''],
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
                                                        return \app\models\Proyectos::obtenerTextoEstado($model->estado);
                                                    },
                                                    'filter' => \kartik\widgets\Select2::widget([
                                                        'model' => $searchModel,
                                                        'attribute' => 'estado',
                                                        'data' => \app\models\Proyectos::getEstadosProyectos(),
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
                                                        return \app\models\Proyectos::obtenerTextoFinalizado($model->finalizado);
                                                    },
                                                    'filter' => \kartik\widgets\Select2::widget([
                                                        'model' => $searchModel,
                                                        'attribute' => 'finalizado',
                                                        'data' => \app\models\Proyectos::getFinalizadoProyectos(),
                                                        'options' => [
                                                            'placeholder' => 'Status',
                                                        ],
                                                        'pluginOptions' => [
                                                            'allowClear' => true,
                                                        ],
                                                    ]),
                                                ],



                                                // Operaciones
                                                [
                                                    'class' => 'yii\grid\ActionColumn',
                                                    'header' => 'Operaciones',
                                                    'template' => '<div class="btn-group btn-group-justified" role="group">{viewproyect}</div>',
                                                    'buttons' => [
                                                        'viewproyect' => function ($url, $model, $key) {
                                                            return Html::a('<i class="toggle fa fa-eye"></i>', $url, ['class' => 'btn btn-primary', "title" => "Ver"]);
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
                </div>
            </div>
        </div>
<?php
$this->registerJsFile(
    '@web/assets_b/js/estadosc.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);
?>

