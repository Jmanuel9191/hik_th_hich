<?php
use yii\helpers\Url;
use yii\grid\GridView;
$this->title = 'TAREA';
$format = <<< SCRIPT
function format(state) {
if (!state.id) return state.text; // optgroup
return state.text;
}
SCRIPT;
$escape = new \yii\web\JsExpression("function(m) { return m; }");
$this->registerJs($format, \yii\web\View::POS_HEAD);

$originalDatefechan = $model->fecha_registro ?? null;
$newDatefn = date("d/m/Y", strtotime($originalDatefechan));

?>


<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">


        <div class="col-12 col-sm-6">
            <h1 class="app-page-title">TAREAS</h1>

        </div>


        <div class="col-12 col-sm-6">
            <div class="app-card-footer p-4 mt-auto">
                <a class="btn app-btn-secondary" href="<?= Url::to(['tareas/create']) ?>">Crar nueva tarea</a>
                <a class="btn app-btn-secondary" href="<?= Url::to(['tareas/update?id='.$model['idtarea']]) ?>">Editar Tarea</a>
            </div><!--//app-card-footer-->
        </div>



        <div class="row gy-4">
            <div class="col-12">
                <div class="app-card-account shadow-sm d-flex flex-column align-items-start">
                    <div class="app-card-body px-4 w-100">
                        <?= $this->render('widgets/appcuenta', ['label' => 'Titulo de la tarea', 'datos' =>  $model['titulo']]) ?>
                        <?= $this->render('widgets/appcuenta', ['label' => 'Descripcion', 'datos' =>   $model['descripcion']]) ?>
                        <?= $this->render('widgets/appcuenta', ['label' => 'Comentario sobre el estado', 'datos' =>    $model['motivobloqueo']]) ?>
                        <?= $this->render('widgets/appcuenta', ['label' => 'Modulo', 'datos' =>    $model->modulos['nombre']]) ?>
                        <?= $this->render('widgets/appcuenta', ['label' => 'Tipo de Tarea', 'datos' =>    $model->tipoTarea['nombre']]) ?>
                        <?= $this->render('widgets/appcuenta', ['label' => 'Quien registro', 'datos' =>  $model->usuarioCreador['nombrecompleto']]) ?>
                        <?= $this->render('widgets/appcuenta', ['label' => 'Fecha registro', 'datos' =>  $newDatefn]) ?>


                    </div><!--//app-card-body-->


                </div><!--//app-card-->
            </div><!--//col-->

            <div class="col-12 col-sm-12">
                <div class="app-card-account shadow-sm d-flex flex-column align-items-start">
                    <div class="app-card-body px-4 w-100 parafiltradoreshostirla">
                <h1 class="app-page-title">HISTORIAL DE MODIFICACIONES</h1>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        'accion' => [
                            'attribute' => 'accion',

                            'filter' => [
                                'Edición' => 'Edición',
                                'Bloqueo' => 'Bloqueo',
                                'Desbloqueo' => 'Desbloqueo',
                                // podés agregar más si usás otros
                            ],
                            'options' => ['placeholder' => 'Seleccionar...'],
                            'value' => 'accion',

                        ],
                        [
                            'attribute' => 'idusuario',
                            'label' => 'Usuario',
                            'filter' => \kartik\widgets\Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'idusuario',
                                'data' => \yii\helpers\ArrayHelper::map(
                                    \app\models\Usuarios::find()->all(),
                                    'idusuario',
                                    'nombrecompleto'
                                ),
                                'options' => ['placeholder' => 'Seleccionar...'],
                                'pluginOptions' => ['allowClear' => true],
                            ]),
                            'value' => function ($model) {
                                return $model->usuario->nombrecompleto ?? 'Sistema';
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
                    ],
                ]); ?>




            </div>
            </div>
            </div>

        </div><!--//row-->

    </div><!--//container-fluid-->
</div><!--//app-content-->


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