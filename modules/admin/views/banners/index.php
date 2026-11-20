<?php

/**
 * Banners por página: una pestaña por cada una (Banners::UBICACIONES).
 * Orden con ↑ ↓, estado y eliminar: assets_b/js/huk-listado.js (data-listado-*).
 *
 * @var yii\web\View $this
 * @var app\models\BannersSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $extremos [orden mínimo, orden máximo] de esta página
 * @var array $conteos ubicacion => cantidad de banners
 * @var int|null $enUso banner que hoy se ve como encabezado (páginas internas)
 * @var string $tab página de la pestaña abierta
 * @var array $puede ['crear','editar','estado','eliminar' => bool]
 */

use app\models\Banners;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

\app\assets_b\BannersAdminAsset::register($this);

$this->title = 'Banners';
$u = Banners::UBICACIONES[$tab];
$carrusel = !empty($u['carrusel']);
$situaciones = ['activo' => 'Se muestra', 'espera' => 'En espera', 'programado' => 'Programado', 'vencido' => 'Vencido', 'inactivo' => 'Inactivo'];
$etiquetas = ['activo' => 'label-success', 'espera' => 'label-default', 'programado' => 'label-info', 'vencido' => 'label-warning', 'inactivo' => 'label-default'];
// Fuera del carrusel se ve solo el primero vigente; los otros activos quedan "En espera"
$situacion = fn($m) => (!$carrusel && $m->situacion() === 'activo' && (int) $m->idbanner !== $enUso) ? 'espera' : $m->situacion();
$fecha = fn($v) => $v ? date('d/m/Y', strtotime($v)) : null;
?>

<section class="content-header">
    <h1><i class="fa fa-picture-o"></i> Banners</h1>
    <p class="huk-inicio-sub">Las imágenes grandes de cada página. Inicio: carrusel en el orden de la lista. Las demás (y el Prefooter): se ve el primero de la lista que esté vigente.</p>
</section>

<section class="content">
    <ul class="nav nav-tabs huk-tabs">
        <?php foreach (Banners::UBICACIONES as $id => $pestana): ?>
            <li class="<?= $id === $tab ? 'active' : '' ?>">
                <?= Html::a(Html::encode($pestana['pestana']) . ' <span class="badge">' . (int) ($conteos[$id] ?? 0) . '</span>', ['index', 'tab' => $id], ['title' => $pestana['nombre']]) ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="box">
        <div class="box-body">
            <?php Pjax::begin(['id' => 'banners-grid', 'timeout' => 5000]); ?>
            <?php if ($puede['crear']): ?>
                <div class="huk-barra-acciones">
                    <?= Html::a('<i class="fa fa-plus"></i> Nuevo banner de ' . Html::encode($u['pestana']), ['create', 'ubicacion' => $tab], ['class' => 'btn btn-success', 'data-pjax' => '0']) ?>
                </div>
            <?php endif; ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-striped table-hover huk-con-orden'],
                'emptyText' => 'Todavía no hay banners en esta página.',
                'columns' => [
                    [
                        'label' => 'Orden',
                        'format' => 'raw',
                        'contentOptions' => ['class' => 'huk-orden'],
                        'value' => function ($m) use ($puede, $extremos) {
                            if (!$puede['editar']) {
                                return '';
                            }
                            $boton = fn($dir, $icono, $titulo) => Html::button('<i class="fa fa-' . $icono . '"></i>', [
                                'class' => 'btn btn-default btn-xs',
                                'title' => $titulo,
                                'data-listado-mover' => Url::to(['mover', 'id' => $m->idbanner, 'direccion' => $dir]),
                            ]);
                            return ((int) $m->orden > $extremos[0] ? $boton(-1, 'arrow-up', 'Subir') : '<span class="huk-orden-vacio"></span>')
                                . ((int) $m->orden < $extremos[1] ? $boton(1, 'arrow-down', 'Bajar') : '<span class="huk-orden-vacio"></span>');
                        },
                    ],
                    [
                        'label' => 'Imagen',
                        'format' => 'raw',
                        'value' => fn($m) => Html::img(Banners::url($m->imagen), ['class' => 'huk-banner-mini', 'alt' => '', 'loading' => 'lazy'])
                            . ($m->imagen_movil ? '<small class="huk-banner-movil" title="Tiene imagen para celular"><i class="fa fa-mobile"></i> celular</small>' : ''),
                    ],
                    [
                        'attribute' => 'titulo',
                        'label' => 'Título y texto',
                        'format' => 'raw',
                        'filterInputOptions' => ['class' => 'form-control', 'placeholder' => 'Buscar…'],
                        'value' => fn($m) => ($m->titulo ? Html::tag('strong', Html::encode($m->titulo)) : Html::tag('span', '(sin título)', ['class' => 'text-muted']))
                            . ($m->texto ? Html::tag('div', Html::encode(mb_strimwidth($m->texto, 0, 110, '…')), ['class' => 'huk-eco-resumen']) : ''),
                    ],
                    [
                        'label' => 'Botón',
                        'visible' => in_array('boton', $u['campos'], true),
                        'format' => 'raw',
                        'value' => fn($m) => $m->boton_texto
                            ? Html::tag('span', Html::encode($m->boton_texto), ['class' => 'huk-banner-boton']) . Html::tag('div', Html::encode($m->boton_url), ['class' => 'huk-eco-resumen'])
                            : Html::tag('span', '—', ['class' => 'text-muted']),
                    ],
                    [
                        'label' => 'Fechas',
                        'contentOptions' => ['class' => 'text-nowrap'],
                        'value' => function ($m) use ($fecha) {
                            if (!$m->fecha_inicio && !$m->fecha_fin) {
                                return 'Siempre';
                            }
                            return trim(($m->fecha_inicio ? 'Desde ' . $fecha($m->fecha_inicio) : '') . "\n" . ($m->fecha_fin ? 'Hasta ' . $fecha($m->fecha_fin) : ''));
                        },
                        'format' => 'ntext',
                    ],
                    [
                        'attribute' => 'situacion',
                        'label' => 'Estado',
                        'format' => 'raw',
                        'filter' => array_diff_key($situaciones, ['espera' => 1]), // "En espera" no es un filtro: depende del orden
                        'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                        'value' => fn($m) => Html::tag('span', $situaciones[$situacion($m)], ['class' => 'label ' . $etiquetas[$situacion($m)]]),
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'Operaciones',
                        'template' => '<div class="btn-group btn-group-justified" role="group">{view}{update}{estado}{delete}</div>',
                        'buttons' => [
                            'view' => fn($url) => Html::a('<i class="fa fa-eye"></i>', $url, ['class' => 'btn btn-success', 'title' => 'Ver', 'data-pjax' => '0']),
                            'update' => fn($url) => $puede['editar']
                                ? Html::a('<i class="fa fa-pencil"></i>', $url, ['class' => 'btn btn-info', 'title' => 'Editar', 'data-pjax' => '0']) : '',
                            'estado' => fn($url, $m) => $puede['estado']
                                ? Html::a('<i class="fa ' . ($m->estado === '1' ? 'fa-eye-slash' : 'fa-check') . '"></i>', '#', [
                                    'class' => 'btn ' . ($m->estado === '1' ? 'btn-warning' : 'btn-success'),
                                    'title' => $m->estado === '1' ? 'Desactivar' : 'Activar',
                                    'data-listado-estado' => $url,
                                    'data-accion' => $m->estado === '1' ? 'desactivar' : 'activar',
                                    'data-nombre' => $m->nombre(),
                                    'data-aviso' => $m->estado === '1' ? 'Deja de mostrarse en la web. No se borra nada.' : 'Se va a mostrar en la web (si está dentro de sus fechas).',
                                ]) : '',
                            'delete' => fn($url, $m) => $puede['eliminar']
                                ? Html::a('<i class="fa fa-trash"></i>', '#', [
                                    'class' => 'btn btn-danger', 'title' => 'Eliminar',
                                    'data-listado-eliminar' => $url, 'data-nombre' => $m->nombre(),
                                    'data-aviso' => 'Se borran el banner y sus imágenes. No se puede deshacer. Si solo desea ocultarlo, use Desactivar.',
                                ]) : '',
                        ],
                        'urlCreator' => fn($action, $m) => Url::to([$action, 'id' => $m->idbanner]),
                    ],
                ],
            ]) ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</section>
