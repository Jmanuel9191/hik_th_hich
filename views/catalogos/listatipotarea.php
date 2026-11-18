<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'TIPOS DE TAREA';
?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-12 optionstable">
                <h1><?= Html::encode($this->title) ?></h1>
                <?= Html::a('Nuevo Tipo de Tarea', ['tipo-tarea-create'], ['class' => 'btn btn-success btncreate']) ?>
            </div>

            <div class="tab-content">
                <div class="app-card app-card-orders-table shadow-sm mb-5">
                    <div class="app-card-body">
                        <div class="box-body">
                            <?php Pjax::begin(['id' => 'table']); ?>
                            <div class="table-responsive">
                                <?= GridView::widget([
                                    'dataProvider' => $dataProvider,
                                    'filterModel' => $searchModel,
                                    'columns' => [
                                        ['class' => 'yii\grid\SerialColumn'],
                                        'nombre',
                                        [
                                            'attribute' => 'estado',
                                            'filter' => ['1' => 'Activo', '0' => 'Inactivo'],
                                            'value' => fn($model) => $model->estado ? 'Activo' : 'Inactivo',
                                        ],

                                        [
                                            'class' => 'yii\grid\ActionColumn',
                                            'header' => 'Operaciones',
                                            'template' => '<div class="btn-group btn-group-justified" role="group">{view}{update}{delete}</div>',

                                            'buttons' => [
                                                'view' => function ($url, $model) {
                                                    return Html::a('<i class="fa fa-eye"></i>', ['catalogos/tipo-tarea-view', 'id' => $model->idtipo_tarea], ['class' => 'btn btn-primary', 'title' => 'Ver']);
                                                },
                                                'update' => function ($url, $model) {
                                                    return Html::a('<i class="fa fa-pencil"></i>', ['catalogos/tipo-tarea-update', 'id' => $model->idtipo_tarea], [
                                                        'class' => 'btn btn-info',
                                                        'title' => 'Editar',
                                                    ]);
                                                },

                                                'delete' => function ($url, $model) {
                                                    return Html::a('<i class="fa fa-trash"></i>', ['catalogos/tipo-tarea-delete', 'id' => $model->idtipo_tarea], [
                                                        'class' => 'btn btn-danger',
                                                        'title' => 'Eliminar',
                                                        'data' => [
                                                            'confirm' => '¿Está seguro de eliminar este tipo de tarea?',
                                                            'method' => 'post',
                                                        ],
                                                    ]);
                                                },
                                            ],
                                            'contentOptions' => ['class' => 'col-sm-2'],
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
