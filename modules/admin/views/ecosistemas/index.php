<?php

/**
 * Listado de ecosistemas. El gerente solo ve los suyos (lo filtra EcosistemaService).
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $filtro
 * @var bool $puedeCrear
 * @var bool $puedeEstado
 * @var bool $puedeGaleria
 * @var bool $puedeEliminar
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\EcosistemasAdminAsset::register($this);

$this->title = 'Ecosistemas';
?>

<section class="content-header">
    <h1><i class="fa fa-th-large"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
    <div class="box">
        <div class="box-header">
            <?php if ($puedeCrear): ?>
                <?= Html::a('<i class="fa fa-plus"></i> Nuevo ecosistema', ['create'], ['class' => 'btn btn-success']) ?>
            <?php endif; ?>

            <?= Html::beginForm(['index'], 'get', ['class' => 'form-inline huk-filtros']) ?>
                <?= Html::textInput('f[nombre]', $filtro['nombre'] ?? '', ['class' => 'form-control', 'placeholder' => 'Buscar por título']) ?>
                <?= Html::dropDownList('f[estado]', $filtro['estado'] ?? '', ['1' => 'Activos', '0' => 'Inactivos'], ['class' => 'form-control', 'prompt' => 'Todos']) ?>
                <?= Html::submitButton('<i class="fa fa-search"></i> Filtrar', ['class' => 'btn btn-default']) ?>
            <?= Html::endForm() ?>
        </div>

        <div class="box-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'emptyText' => 'No hay ecosistemas para mostrar.',
                'columns' => [
                    [
                        'attribute' => 'numero',
                        'label' => 'Nº',
                        'value' => fn($m) => sprintf('%02d', $m->numero),
                        'contentOptions' => ['class' => 'huk-eco-num'],
                    ],
                    [
                        'label' => 'Ícono',
                        'format' => 'raw',
                        'value' => fn($m) => $m->icono
                            ? Html::img($m->url($m->icono), ['class' => 'huk-eco-icono', 'alt' => ''])
                            : Html::tag('span', '—', ['class' => 'text-muted']),
                    ],
                    [
                        'attribute' => 'nombre',
                        'label' => 'Título',
                        'format' => 'raw',
                        'value' => fn($m) => Html::tag('strong', Html::encode($m->nombre))
                            . Html::tag('div', Html::encode($m->resumen), ['class' => 'huk-eco-resumen']),
                    ],
                    [
                        'label' => 'Estado',
                        'format' => 'raw',
                        'value' => fn($m) => $m->estado === '1'
                            ? Html::tag('span', 'Activo', ['class' => 'label label-success'])
                            : Html::tag('span', 'Inactivo', ['class' => 'label label-default']),
                    ],
                    [
                        'label' => 'Galería',
                        'value' => fn($m) => count($m->imagenes) . ' foto(s)',
                        'contentOptions' => ['class' => 'text-nowrap'],
                    ],
                    [
                        'label' => 'Últ. cambio',
                        'value' => fn($m) => date('d/m/Y H:i', strtotime($m->fecha_actualizacion ?: $m->fecha_registro)),
                        'contentOptions' => ['class' => 'text-nowrap'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'Operaciones',
                        'template' => '<div class="btn-group btn-group-justified" role="group">{view}{update}{galeria}{estado}{delete}</div>',
                        'buttons' => [
                            'view' => fn($url) => Html::a('<i class="fa fa-eye"></i>', $url, ['class' => 'btn btn-success', 'title' => 'Ver e historial']),
                            'update' => fn($url) => Html::a('<i class="fa fa-pencil"></i>', $url, ['class' => 'btn btn-info', 'title' => 'Editar']),
                            'galeria' => fn($url) => $puedeGaleria
                                ? Html::a('<i class="fa fa-picture-o"></i>', $url, ['class' => 'btn btn-primary', 'title' => 'Galería de imágenes'])
                                : '',
                            'estado' => fn($url, $m) => $puedeEstado
                                ? Html::a('<i class="fa ' . ($m->estado === '1' ? 'fa-eye-slash' : 'fa-check') . '"></i>', '#', [
                                    'class' => 'btn ' . ($m->estado === '1' ? 'btn-danger' : 'btn-success'),
                                    'title' => $m->estado === '1' ? 'Desactivar' : 'Activar',
                                    'data-eco-estado' => $url,
                                    'data-accion' => $m->estado === '1' ? 'desactivar' : 'activar',
                                    'data-nombre' => $m->nombre,
                                ])
                                : '',
                            'delete' => fn($url, $m) => Html::a('<i class="fa fa-trash"></i>', '#', [
                                'class' => 'btn btn-danger',
                                'title' => 'Eliminar',
                                'data-eco-eliminar' => $url,
                                'data-nombre' => $m->nombre,
                            ]),
                        ],
                        'urlCreator' => fn($action, $m) => Url::to([$action, 'id' => $m->idecosistema]),
                        'visibleButtons' => ['delete' => $puedeEliminar],
                    ],
                ],
            ]) ?>
        </div>
    </div>
</section>
