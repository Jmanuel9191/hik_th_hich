<?php

/**
 * Blog → Noticias. Publicar/ocultar, destacar y eliminar: assets_b/js/huk-listado.js (data-listado-*).
 *
 * @var yii\web\View $this
 * @var app\models\NoticiaSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $categorias  id => nombre
 * @var array $puede ['crear','editar','estado','galeria','eliminar' => bool]
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;

\app\assets_b\NoticiasAdminAsset::register($this);

$this->title = 'Blog · Noticias';
?>

<section class="content-header">
    <h1><i class="fa fa-newspaper-o"></i> Noticias</h1>
    <p class="huk-inicio-sub">Las entradas del Blog de la web, de la más nueva a la más vieja. Las <i class="fa fa-star"></i> destacadas van en el carrusel del Inicio.</p>
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
            <?php Pjax::begin(['id' => 'noticias-grid', 'timeout' => 5000]); ?>
            <div class="huk-barra-acciones">
                <?php if ($puede['crear']): ?>
                    <?= Html::a('<i class="fa fa-plus"></i> Nueva entrada', ['create'], ['class' => 'btn btn-success', 'data-pjax' => '0']) ?>
                <?php endif; ?>
                <?= Html::a('<i class="fa fa-external-link"></i> Ver el blog', ['/noticias/index'], ['class' => 'btn btn-default', 'target' => '_blank', 'data-pjax' => '0']) ?>
            </div>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'emptyText' => 'Todavía no hay noticias.',
                'columns' => [
                    [
                        'label' => 'Portada',
                        'format' => 'raw',
                        'value' => fn($m) => Html::img($m->url($m->imagen_portada), ['class' => 'huk-noticia-mini', 'alt' => '', 'loading' => 'lazy']),
                    ],
                    [
                        'attribute' => 'titulo',
                        'label' => 'Título',
                        'format' => 'raw',
                        'filterInputOptions' => ['class' => 'form-control', 'placeholder' => 'Buscar…'],
                        'value' => fn($m) => Html::tag('strong', Html::encode($m->titulo))
                            . Html::tag('div', Html::encode(StringHelper::truncate($m->resumen, 110)), ['class' => 'huk-eco-resumen']),
                    ],
                    [
                        'attribute' => 'idcategoria',
                        'label' => 'Categoría',
                        'filter' => $categorias,
                        'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todas'],
                        'value' => fn($m) => $m->categoria->nombre ?? '—',
                    ],
                    [
                        'label' => 'Fecha',
                        'contentOptions' => ['class' => 'text-nowrap'],
                        'value' => fn($m) => date('d/m/Y', strtotime($m->fecha)),
                    ],
                    [
                        'attribute' => 'destacado',
                        'label' => 'Destacada',
                        'format' => 'raw',
                        'filter' => ['1' => 'Destacadas', '0' => 'No destacadas'],
                        'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todas'],
                        'contentOptions' => ['class' => 'text-center'],
                        'value' => fn($m) => $puede['editar']
                            ? Html::button('<i class="fa fa-star' . ($m->destacado === '1' ? '' : '-o') . '"></i>', [
                                'class' => 'btn btn-xs huk-estrella' . ($m->destacado === '1' ? ' is-activa' : ''),
                                'title' => $m->destacado === '1' ? 'Quitar destacada' : 'Destacar (carrusel del Inicio)',
                                'data-listado-destacar' => Url::to(['destacar', 'id' => $m->idnoticia]),
                            ])
                            : ($m->destacado === '1' ? '<i class="fa fa-star huk-estrella is-activa"></i>' : ''),
                    ],
                    [
                        'attribute' => 'estado',
                        'label' => 'Estado',
                        'format' => 'raw',
                        'filter' => ['1' => 'Publicada', '0' => 'Oculta'],
                        'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todas'],
                        'value' => fn($m) => $m->estado === '1'
                            ? Html::tag('span', 'Publicada', ['class' => 'label label-success'])
                            : Html::tag('span', 'Oculta', ['class' => 'label label-default']),
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'Operaciones',
                        'template' => '<div class="btn-group btn-group-justified" role="group">{view}{update}{galeria}{estado}{delete}</div>',
                        'buttons' => [
                            'view' => fn($url) => Html::a('<i class="fa fa-eye"></i>', $url, ['class' => 'btn btn-success', 'title' => 'Ver', 'data-pjax' => '0']),
                            'update' => fn($url) => $puede['editar']
                                ? Html::a('<i class="fa fa-pencil"></i>', $url, ['class' => 'btn btn-info', 'title' => 'Editar', 'data-pjax' => '0']) : '',
                            'galeria' => fn($url) => $puede['galeria']
                                ? Html::a('<i class="fa fa-picture-o"></i>', $url, ['class' => 'btn btn-primary', 'title' => 'Galería de fotos', 'data-pjax' => '0']) : '',
                            'estado' => fn($url, $m) => $puede['estado']
                                ? Html::a('<i class="fa ' . ($m->estado === '1' ? 'fa-eye-slash' : 'fa-check') . '"></i>', '#', [
                                    'class' => 'btn ' . ($m->estado === '1' ? 'btn-warning' : 'btn-success'),
                                    'title' => $m->estado === '1' ? 'Ocultar' : 'Publicar',
                                    'data-listado-estado' => $url,
                                    'data-accion' => $m->estado === '1' ? 'ocultar' : 'publicar',
                                    'data-nombre' => $m->titulo,
                                ]) : '',
                            'delete' => fn($url, $m) => $puede['eliminar']
                                ? Html::a('<i class="fa fa-trash"></i>', '#', [
                                    'class' => 'btn btn-danger', 'title' => 'Eliminar',
                                    'data-listado-eliminar' => $url, 'data-nombre' => $m->titulo,
                                    'data-aviso' => 'Se borran la noticia, sus imágenes y su galería. No se puede deshacer. Si solo desea que no aparezca en la web, use Ocultar.',
                                ]) : '',
                        ],
                        'urlCreator' => fn($action, $m) => Url::to([$action, 'id' => $m->idnoticia]),
                    ],
                ],
            ]) ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</section>
