<?php

/**
 * Configuración → Monedas. Estado y eliminar: huk-listado.js.
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $usadas idmoneda => cantidad de proyectos
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

\app\assets_b\MonedasAdminAsset::register($this);

$this->title = 'Monedas';
?>

<section class="content-header">
    <h1><i class="fa fa-money"></i> Monedas</h1>
    <p class="huk-inicio-sub">Las monedas que se eligen en cada proyecto. Las inactivas no se ofrecen en proyectos nuevos.</p>
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
            <?php Pjax::begin(['id' => 'monedas-grid', 'timeout' => 5000]); ?>
            <div class="huk-barra-acciones">
                <?= Html::a('<i class="fa fa-plus"></i> Agregar moneda', ['create'], ['class' => 'btn btn-success', 'data-pjax' => '0']) ?>
            </div>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'layout' => '{items}',
                'columns' => [
                    ['attribute' => 'codigo', 'format' => 'raw', 'value' => fn($m) => Html::tag('strong', Html::encode($m->codigo))],
                    'simbolo',
                    'nombre',
                    ['label' => 'Proyectos', 'value' => fn($m) => $usadas[$m->idmoneda] ?? 0],
                    [
                        'label' => 'Estado',
                        'format' => 'raw',
                        'value' => fn($m) => $m->estado === '1'
                            ? Html::tag('span', 'Activa', ['class' => 'label label-success'])
                            : Html::tag('span', 'Inactiva', ['class' => 'label label-default']),
                    ],
                    [
                        'label' => 'Operaciones',
                        'format' => 'raw',
                        'contentOptions' => ['class' => 'huk-item-ops'],
                        'value' => fn($m) => Html::tag('div',
                            Html::a('<i class="fa fa-pencil"></i>', ['update', 'id' => $m->idmoneda], ['class' => 'btn btn-info', 'title' => 'Editar', 'data-pjax' => '0'])
                            . Html::a('<i class="fa ' . ($m->estado === '1' ? 'fa-eye-slash' : 'fa-check') . '"></i>', '#', [
                                'class' => 'btn ' . ($m->estado === '1' ? 'btn-warning' : 'btn-success'),
                                'title' => $m->estado === '1' ? 'Desactivar' : 'Activar',
                                'data-listado-estado' => Url::to(['estado', 'id' => $m->idmoneda]),
                                'data-accion' => $m->estado === '1' ? 'desactivar' : 'activar',
                                'data-nombre' => $m->codigo,
                                'data-aviso' => $m->estado === '1' ? 'No se ofrece en proyectos nuevos. Los que ya la usan no cambian.' : 'Se vuelve a ofrecer en los proyectos.',
                            ])
                            . Html::a('<i class="fa fa-trash"></i>', '#', [
                                'class' => 'btn btn-danger', 'title' => 'Eliminar',
                                'data-listado-eliminar' => Url::to(['delete', 'id' => $m->idmoneda]),
                                'data-nombre' => $m->codigo,
                                'data-aviso' => 'Solo se puede si ningún proyecto la usa.',
                            ]),
                            ['class' => 'btn-group btn-group-justified', 'role' => 'group']),
                    ],
                ],
            ]) ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</section>
