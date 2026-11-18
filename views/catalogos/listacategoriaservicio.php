<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'CATEGORÍAS DE SERVICIO';
?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-12 optionstable">
                <h1><?= Html::encode($this->title) ?></h1>
                <?= Html::a('Nueva Categoría', ['categoria-servicio-create'], ['class' => 'btn btn-success btncreate']) ?>
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
                                            'value' => function ($model) {
                                                return $model->estado ? 'Activo' : 'Inactivo';
                                            },
                                        ],
                                        [
                                            'attribute' => 'idpadre',
                                            'label' => 'Categoría Padre',
                                            'filter' => \yii\helpers\ArrayHelper::map(
                                                \app\models\CategoriasServicio::find()
                                                    ->where(['estado' => 1])
                                                    ->andWhere(['idpadre' => null])  // Solo padres
                                                    ->all(),
                                                'idcategoria',
                                                'nombre'
                                            ),
                                            'value' => function ($model) {
                                                return $model->padre ? $model->padre->nombre : '-';
                                            },
                                        ],


                                        [
                                            'class' => 'yii\grid\ActionColumn',
                                            'header' => 'Operaciones',
                                            'template' => '<div class="btn-group btn-group-justified" role="group">{view}{update}{delete}</div>',
                                            'buttons' => [
                                                'view' => function ($url, $model) {
                                                    return Html::a('<i class="fa fa-eye"></i>', ['catalogos/categoria-servicio-view', 'id' => $model->idcategoria], ['class' => 'btn btn-primary', 'title' => 'Ver']);
                                                },
                                                'update' => function ($url, $model) {
                                                    return Html::a('<i class="fa fa-pencil"></i>', ['catalogos/categoria-servicio-update', 'id' => $model->idcategoria], [
                                                        'class' => 'btn btn-info',
                                                        'title' => 'Editar',
                                                    ]);
                                                },

                                                'delete' => function ($url, $model) {
                                                    return Html::a('<i class="fa fa-trash"></i>', ['catalogos/categoria-servicio-delete', 'id' => $model->idcategoria], [
                                                        'class' => 'btn btn-danger',
                                                        'title' => 'Eliminar',
                                                        'data' => [
                                                            'confirm' => '¿Está seguro de eliminar esta categoria?',
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
