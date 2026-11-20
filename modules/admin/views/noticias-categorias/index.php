<?php

/**
 * Blog → Categorías: alta rápida arriba, lista (orden ↑ ↓, estado, editar, eliminar: huk-listado.js) e historial.
 *
 * @var yii\web\View $this
 * @var app\models\NoticiaCategoria $model  nueva
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $cantidades  idcategoria => n° de noticias
 * @var array $extremos [orden mínimo, orden máximo]
 * @var yii\data\ActiveDataProvider|null $historial
 */

use kartik\form\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

\app\assets_b\NoticiasAdminAsset::register($this);

$this->title = 'Blog · Categorías';
$fecha = fn($v) => $v ? date('d/m/Y H:i', strtotime($v)) : '—';
?>

<section class="content-header">
    <h1><i class="fa fa-tags"></i> Categorías del blog</h1>
    <p class="huk-inicio-sub">Salen en la barra lateral del Blog (con la cantidad de noticias) y en el cuadrito de cada noticia. El orden de esta lista es el de la web.</p>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-plus"></i> Nueva categoría</h3></div>
                <div class="box-body">
                    <?php $form = ActiveForm::begin(['id' => 'categoria-form']); ?>
                    <?= $form->field($model, 'nombre')->textInput(['maxlength' => 100, 'placeholder' => 'Ej.: Minería']) ?>
                    <?= Html::submitButton('<i class="fa fa-save"></i> Crear categoría', ['class' => 'btn btn-success btn-block']) ?>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="box">
                <div class="box-body">
                    <?php Pjax::begin(['id' => 'categorias-grid', 'timeout' => 5000]); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => ['class' => 'table table-striped table-hover huk-con-orden'],
                        'emptyText' => 'Todavía no hay categorías: cree la primera a la izquierda.',
                        'summary' => '',
                        'columns' => [
                            [
                                'label' => 'Orden',
                                'format' => 'raw',
                                'contentOptions' => ['class' => 'huk-orden'],
                                'value' => function ($m) use ($extremos) {
                                    $boton = fn($dir, $icono, $titulo) => Html::button('<i class="fa fa-' . $icono . '"></i>', [
                                        'class' => 'btn btn-default btn-xs', 'title' => $titulo,
                                        'data-listado-mover' => Url::to(['mover', 'id' => $m->idcategoria, 'direccion' => $dir]),
                                    ]);
                                    return ((int) $m->orden > $extremos[0] ? $boton(-1, 'arrow-up', 'Subir') : '<span class="huk-orden-vacio"></span>')
                                        . ((int) $m->orden < $extremos[1] ? $boton(1, 'arrow-down', 'Bajar') : '<span class="huk-orden-vacio"></span>');
                                },
                            ],
                            [
                                'label' => 'Categoría',
                                'format' => 'raw',
                                'value' => fn($m) => Html::tag('strong', Html::encode($m->nombre)) . Html::tag('div', '/noticias/categoria/' . Html::encode($m->slug), ['class' => 'huk-eco-resumen']),
                            ],
                            ['label' => 'Noticias', 'contentOptions' => ['class' => 'text-center'], 'value' => fn($m) => (int) ($cantidades[$m->idcategoria] ?? 0)],
                            [
                                'label' => 'Estado',
                                'format' => 'raw',
                                'value' => fn($m) => $m->estado === '1'
                                    ? Html::tag('span', 'Activa', ['class' => 'label label-success'])
                                    : Html::tag('span', 'Inactiva', ['class' => 'label label-default']),
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'Operaciones',
                                'template' => '<div class="btn-group btn-group-justified" role="group">{update}{estado}{delete}</div>',
                                'buttons' => [
                                    'update' => fn($url) => Html::a('<i class="fa fa-pencil"></i>', $url, ['class' => 'btn btn-info', 'title' => 'Cambiar nombre', 'data-pjax' => '0']),
                                    'estado' => fn($url, $m) => Html::a('<i class="fa ' . ($m->estado === '1' ? 'fa-eye-slash' : 'fa-check') . '"></i>', '#', [
                                        'class' => 'btn ' . ($m->estado === '1' ? 'btn-warning' : 'btn-success'),
                                        'title' => $m->estado === '1' ? 'Desactivar (no sale en la barra lateral)' : 'Activar',
                                        'data-listado-estado' => $url, 'data-accion' => $m->estado === '1' ? 'desactivar' : 'activar', 'data-nombre' => $m->nombre,
                                    ]),
                                    'delete' => fn($url, $m) => Html::a('<i class="fa fa-trash"></i>', '#', [
                                        'class' => 'btn btn-danger', 'title' => 'Eliminar',
                                        'data-listado-eliminar' => $url, 'data-nombre' => $m->nombre,
                                        'data-aviso' => 'Solo se puede eliminar si no tiene noticias.',
                                    ]),
                                ],
                                'urlCreator' => fn($action, $m) => Url::to([$action, 'id' => $m->idcategoria]),
                            ],
                        ],
                    ]) ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Historial de categorías</h3></div>
        <div class="box-body">
            <?php if ($historial === null): ?>
                <p class="text-muted">No tiene permiso para ver el historial.</p>
            <?php else: ?>
                <?php Pjax::begin(['id' => 'categorias-historial', 'timeout' => 5000]); ?>
                <?= GridView::widget([
                    'dataProvider' => $historial,
                    'tableOptions' => ['class' => 'table table-hover'],
                    'emptyText' => 'Todavía no hay movimientos.',
                    'summary' => '{totalCount} movimiento(s)',
                    'columns' => [
                        ['label' => 'Fecha', 'value' => fn($h) => $fecha($h->fecha), 'contentOptions' => ['class' => 'text-nowrap']],
                        ['label' => 'Acción', 'format' => 'raw', 'value' => fn($h) => Html::tag('strong', Html::encode($h->accion))],
                        ['label' => 'Detalle', 'format' => 'ntext', 'value' => fn($h) => (string) $h->descripcion],
                        ['label' => 'Hecho por', 'value' => fn($h) => $h->usuarioActor->usuario ?? ($h->idusuario_actor ? 'Usuario #' . $h->idusuario_actor : 'Sistema')],
                        ['label' => 'IP', 'value' => fn($h) => $h->ip ?: '—'],
                    ],
                ]) ?>
                <?php Pjax::end(); ?>
            <?php endif; ?>
        </div>
    </div>
</section>
