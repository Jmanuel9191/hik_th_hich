<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
$this->title = 'USUARIOS';
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

        <div class="app-content pt-3 p-md-3 p-lg-4">
            <div class="container-xl">

                <div class="row g-3 mb-4 align-items-center justify-content-between">
                    <div class="col-12 optionstable">
                        <h1><?= Html::encode($this->title) ?> </h1>
                        <?= Html::a('Nuevo Usuario', ['create'], ['class' => 'btn btn-success btncreate']) ?>

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
                                                'nombrecompleto',
                                                'usuario',
                                                'email',
                                                [
                                                    'header' => 'Tipo de Usuario',
                                                    'value' => function ($model) {
                                                        return $model->rol->nombre ?? '(Sin rol)';
                                                    },
                                                    'filter' => \kartik\widgets\Select2::widget([
                                                        'model' => $searchModel,
                                                        'attribute' => 'idrol',
                                                        'data' => \yii\helpers\ArrayHelper::map(
                                                            \app\models\Roles::find()->all(),
                                                            'idrol',
                                                            'nombre'
                                                        ),
                                                        'language' => 'es',
                                                        'options' => [
                                                            'placeholder' => 'Tipo de Usuario',
                                                        ],
                                                        'pluginOptions' => [
                                                            'templateResult' => new \yii\web\JsExpression('format'),
                                                            'templateSelection' => new \yii\web\JsExpression('format'),
                                                            'escapeMarkup' => $escape,
                                                            'allowClear' => true,
                                                        ],
                                                    ]),
                                                ],
                                                [
                                                    'attribute' => 'estado',
                                                    'label' => 'Estado',
                                                    'value' => function ($model) {
                                                        return \app\models\Usuarios::obtenerTextoEstado($model->estado);
                                                    },
                                                    'filter' => \kartik\widgets\Select2::widget([
                                                        'model' => $searchModel,
                                                        'attribute' => 'estado',
                                                        'data' => \app\models\Usuarios::getEstadosUsuario(),
                                                        'options' => [
                                                            'placeholder' => 'Tipo de estados',
                                                        ],
                                                        'pluginOptions' => [
                                                            'allowClear' => true,
                                                        ],
                                                    ]),
                                                ],





                                                [
                                                    'class' => 'yii\grid\ActionColumn',
                                                    'header' => 'Operaciones',
                                                    'template' => '<div class="btn-group btn-group-justified" role="group">{view}{historial}{update}</div>',
                                                    'buttons' => [
                                                        'view' => function ($url, $model, $key) {
                                                            return Html::a('<i class="toggle fa fa-eye"></i>', $url, ['class' => 'btn btn-primary',"title"=>"Ver detalles"]);
                                                        },
                                                        'historial' => function ($url, $model, $key) {
                                                            return Html::a('<i class="toggle fa fa-book"></i>', $url, ['class' => 'btn btn-secondary',"title"=>"Historial"]);
                                                        },
                                                        'update' => function ($url, $model, $key) {
                                                            return Html::a('<i class="fa fa-pencil"></i>', $url, ['class' => 'btn btn-info',"title"=>"Editar usuario"]);
                                                        },

                                                    ],
                                                    'contentOptions' => ['class' => 'col-sm-2']
                                                ],
                                                [
                                                    'class' => 'yii\grid\ActionColumn',
                                                    'header' => 'Estado',
                                                    'template' => '<div class="btn-group btn-group-justified" role="group">{estado}{inactividad}{desbloqueo_intentos}</div>',
                                                    'urlCreator' => function ($action, $model, $key, $index) {
                                                        if ($action === 'estado') {
                                                            return \yii\helpers\Url::to(['estado', 'id' => $model->idusuario]);
                                                        }

                                                        if ($action === 'inactividad') {
                                                            return \yii\helpers\Url::to(['cambiar-inactividad', 'id' => $model->idusuario]);
                                                        }

                                                        if ($action === 'desbloqueo_intentos') {
                                                            return \yii\helpers\Url::to(['desbloquear-intentos', 'id' => $model->idusuario]);
                                                        }

                                                        return '#';
                                                    },
                                                    'buttons' => [
                                                        'estado' => function ($url, $model, $key) {
                                                            if ($model->estado == '2') return ''; // ocultamos si está inactivo
                                                            $iconClass = $model->estado ? 'fa fa-unlock' : 'fa fa-lock';
                                                            $btnClass = $model->estado ? 'btn btn-success sep' : 'btn btn-danger';
                                                            return Html::a("<i class='$iconClass'></i>", "#", [
                                                                'class' => $btnClass,
                                                                'title' => 'Bloquear / Desbloquear',
                                                                'onclick' => "cambiarEstadoUsuarioConMotivo('{$url}'); return false;"
                                                            ]);
                                                        },

                                                        'inactividad' => function ($url, $model, $key) {
                                                            $iconClass = $model->estado == '2' ? 'fa fa-user-slash' : 'fa fa-user-check';
                                                            $btnClass = $model->estado == '2' ? 'btn btn-secondary sep' : 'btn btn-dark sep';
                                                            $title = $model->estado == '2' ? 'Reactivar Usuario' : 'Marcar como Inactivo';
                                                            return Html::a("<i class='$iconClass'></i>", "#", [
                                                                'class' => $btnClass,
                                                                'title' => $title,
                                                                'onclick' => "cambiarEstadoInactividadConMotivo('{$url}'); return false;"

                                                            ]);
                                                        },

                                                        'desbloqueo_intentos' => function ($url, $model, $key) {
                                                            if ($model->bloqueado_hasta !== null && strtotime($model->bloqueado_hasta) > time()) {
                                                                return Html::a('<i class="fa fa-user-lock"></i>', '#', [
                                                                    'class' => 'btn btn-warning sep',
                                                                    'title' => 'Desbloquear por intentos fallidos',
                                                                    'onclick' => "desbloquearPorIntentos('{$url}'); return false;"
                                                                ]);
                                                            }
                                                            return '';
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

    <style>
        div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-confirm) {
            background-color: #2879dd!important;
            font-size: 1.2em!important;
        }
        div:where(.swal2-container) button:where(.swal2-styled):where(.swal2-cancel){
            font-size: 1.2em!important;
        }
        div:where(.swal2-container) div:where(.swal2-popup) {
            font-size: 1.3rem!important;
        }
.btn.btn-dark.sep{
    background: #7fd37f;
}

    </style>
<?php

$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    ['position' => \yii\web\View::POS_HEAD]
);
$this->registerJsFile(
    '@web/assets_b/js/appuser.js',
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