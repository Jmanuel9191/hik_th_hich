<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use kartik\widgets\Select2;
use kartik\daterange\DateRangePicker;

$this->title = 'Historial de Asignación';
?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-12 optionstable">
                <h1>HISTORIAL DE ASIGNACIONES PARA EL PROYECTO: <?= Html::encode($model->titulo ?? '(Sin título)') ?></h1>
            </div>

            <div class="tab-content" id="orders-table-tab-content">
                <div class="app-card app-card-orders-table shadow-sm mb-5">
                    <div class="app-card-body">
                        <div class="box-body">
                            <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'emptyText' => 'No hay historial de asignaciones para este proyecto.',

                                'columns' => [

                                    // Acción: creación, edición, cambio de estado, etc.
                                    [
                                        'attribute' => 'accion',
                                        'filter' => [
                                            'Creación' => 'Creación',
                                            'Edición' => 'Edición',
                                            'Estado' => 'Estado',
                                            'Asignación' => 'Asignación',
                                            'Eliminación' => 'Eliminación',
                                            'Otro' => 'Otro',
                                        ],

                                        'value' => 'accion',
                                    ],

                                    // Tarea afectada
                                    [
                                        'attribute' => 'idtarea',
                                        'label' => 'Tarea',
                                        'value' => function ($model) {
                                            return $model->tarea->titulo ?? '(Sin tarea)';
                                        }
                                    ],

                                    // Usuario que realizó la acción
                                    [
                                        'attribute' => 'idusuario',
                                        'label' => 'Hecho por',
                                        'value' => function ($model) {
                                            return $model->usuario->nombrecompleto ?? 'Sistema';
                                        },
                                        'filter' => \kartik\select2\Select2::widget([
                                            'model' => $searchModel,
                                            'attribute' => 'idusuario',
                                            'data' => \yii\helpers\ArrayHelper::map(
                                                \app\models\Usuarios::find()->all(), 'idusuario', 'nombrecompleto'
                                            ),
                                            'options' => ['placeholder' => 'Seleccionar...'],
                                            'pluginOptions' => ['allowClear' => true],
                                        ]),
                                    ],

                                    // Usuario afectado por la acción (si aplica, como al asignar)
                                    [
                                        'label' => 'Asignado a',
                                        'value' => function ($model) {
                                            return $model->usuarioAsignado->nombrecompleto ?? '-';
                                        }
                                    ],

                                    // Fecha con conversión de zona horaria
                                    [
                                        'attribute' => 'fecha',
                                        'label' => 'Fecha',
                                        'format' => 'raw',
                                        'value' => function ($model) {
                                            $zonaHoraria = Yii::$app->user->identity->zona_horaria ?? 'America/La_Paz';
                                            $dt = new \DateTime($model->fecha);
                                            $dt->setTimezone(new \DateTimeZone($zonaHoraria));
                                            return $dt->format('d/m/Y H:i:s');
                                        }
                                    ],

                                    // Descripción libre del cambio
                                    'descripcion:ntext',
                                ],
                            ]); ?>

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


?>


<?php
$this->registerJs('', \yii\web\View::POS_HEAD); // Podés dejar JS extra si lo necesitás
?>
