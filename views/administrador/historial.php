<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
$this->title = 'HISTORIAL';
?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">

        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-12 optionstable">
                <h1>HISTORIAL DE MOVIMIENTOS DE: <?= Html::encode($model->nombrecompleto) ?>





                </h1>
            </div>

            <div class="tab-content" id="orders-table-tab-content">
                <div class="app-card app-card-orders-table shadow-sm mb-5">
                    <div class="app-card-body">
                        <div class="box-body">

                            <?php Pjax::begin(['id' => 'table']); ?>

                            <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'columns' => [
                                    [
                                        'attribute' => 'accion',
                                        'filter' => [
                                            'Edición' => 'Edición',
                                            'Bloqueo' => 'Bloqueo',
                                            'Desbloqueo' => 'Desbloqueo',
                                            'Edición personal' => 'Edición personal',
                                            'Bloqueo automático por intentos fallidos' => 'Bloqueo automático por intentos fallidos',
                                            'Desbloqueo automático' => 'Desbloqueo automático',
                                        ],
                                        'options' => ['placeholder' => 'Seleccionar...'],
                                    ],
                                    [
                                        'attribute' => 'idusuario_actor',
                                        'label' => 'Usuario que realizó la acción',
                                        'filter' => \kartik\widgets\Select2::widget([
                                            'model' => $searchModel,
                                            'attribute' => 'idusuario_actor',
                                            'data' => \yii\helpers\ArrayHelper::map(
                                                \app\models\Usuarios::find()->all(),
                                                'idusuario',
                                                'nombrecompleto'
                                            ),
                                            'options' => ['placeholder' => 'Seleccionar...'],
                                            'pluginOptions' => ['allowClear' => true],
                                        ]),
                                        'value' => function ($model) {
                                            return $model->usuarioActor->nombrecompleto ?? 'Sistema';
                                        }
                                    ],
                                    [
                                        'attribute' => 'fecha',
                                        'label' => 'Fecha',
                                        'filter' => \kartik\daterange\DateRangePicker::widget([
                                            'model' => $searchModel,
                                            'attribute' => 'fecha',
                                            'convertFormat' => true,
                                            'pluginOptions' => [
                                                'locale' => ['format' => 'Y-m-d'],
                                            ],
                                        ]),
                                        'format' => 'raw',
                                        'value' => function ($model) {
                                            $zonaHoraria = Yii::$app->user->identity->zona_horaria ?? 'America/La_Paz';
                                            $dt = new \DateTime($model->fecha); // ✅ ESTA LÍNEA es la clave
                                            $dt->setTimezone(new \DateTimeZone($zonaHoraria));
                                            return $dt->format('d M Y, h:i:s A');
                                        },
                                    ],


                                    'descripcion:ntext',
                                    //'fecha',
                                ],
                            ]); ?>

                            <?php Pjax::end(); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
function action(url)
{
    $.get(url, function(data) {
        $.pjax.reload({container:"#table"});
    });
}
JS, \yii\web\View::POS_HEAD);
?>
