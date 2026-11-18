<?php

/**
 * Equipo. Orden ↑ ↓, estado y eliminar: assets_b/js/huk-listado.js (data-listado-*).
 *
 * @var yii\web\View $this
 * @var app\models\EquipoSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $extremos [orden mínimo, orden máximo]
 * @var array $puede ['crear','editar','estado','eliminar' => bool]
 */

use app\models\Equipo;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

\app\assets_b\EquipoAdminAsset::register($this);

$this->title = 'Equipo';
?>

<section class="content-header">
    <h1><i class="fa fa-id-badge"></i> Equipo</h1>
    <p class="huk-inicio-sub">Las personas que se muestran en la página. Salen en el orden de esta lista.</p>
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
            <?php Pjax::begin(['id' => 'equipo-grid', 'timeout' => 5000]); ?>
            <?php if ($puede['crear']): ?>
                <div class="huk-barra-acciones">
                    <?= Html::a('<i class="fa fa-plus"></i> Agregar persona', ['create'], ['class' => 'btn btn-success', 'data-pjax' => '0']) ?>
                </div>
            <?php endif; ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-striped table-hover huk-con-orden'],
                'emptyText' => 'Todavía no hay personas en el equipo.',
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
                                'data-listado-mover' => Url::to(['mover', 'id' => $m->idmiembro, 'direccion' => $dir]),
                            ]);
                            return ((int) $m->orden > $extremos[0] ? $boton(-1, 'arrow-up', 'Subir') : '<span class="huk-orden-vacio"></span>')
                                . ((int) $m->orden < $extremos[1] ? $boton(1, 'arrow-down', 'Bajar') : '<span class="huk-orden-vacio"></span>');
                        },
                    ],
                    [
                        'label' => 'Foto',
                        'format' => 'raw',
                        'value' => fn($m) => $m->foto
                            ? Html::img(Equipo::url($m->foto), ['class' => 'huk-equipo-mini', 'alt' => '', 'loading' => 'lazy'])
                            : Html::tag('span', '<i class="fa fa-user"></i>', ['class' => 'huk-equipo-mini is-vacia', 'title' => 'Sin foto']),
                    ],
                    [
                        'attribute' => 'nombre',
                        'label' => 'Nombre',
                        'format' => 'raw',
                        'filterInputOptions' => ['class' => 'form-control', 'placeholder' => 'Buscar…'],
                        'value' => fn($m) => Html::tag('strong', Html::encode($m->nombre))
                            . ($m->biografia ? Html::tag('div', '<i class="fa fa-file-text-o"></i> con biografía', ['class' => 'huk-eco-resumen']) : ''),
                    ],
                    [
                        'attribute' => 'puesto',
                        'label' => 'Puesto',
                        'filterInputOptions' => ['class' => 'form-control', 'placeholder' => 'Buscar…'],
                    ],
                    [
                        'label' => 'Contacto',
                        'format' => 'raw',
                        'value' => fn($m) => implode('<br>', array_filter([
                            $m->telefono ? '<i class="fa fa-phone"></i> ' . Html::encode($m->telefono) : null,
                            $m->email ? '<i class="fa fa-envelope-o"></i> ' . Html::encode($m->email) : null,
                        ])) ?: Html::tag('span', '—', ['class' => 'text-muted']),
                    ],
                    [
                        'label' => 'Redes',
                        'format' => 'raw',
                        'value' => function ($m) {
                            $iconos = [];
                            foreach (Equipo::REDES as $campo => [$nombre, $icono]) {
                                if ($m->$campo) {
                                    $iconos[] = Html::tag('i', '', ['class' => 'fa fa-' . $icono, 'title' => $nombre]);
                                }
                            }
                            return $iconos ? Html::tag('span', implode(' ', $iconos), ['class' => 'huk-equipo-redes']) : Html::tag('span', '—', ['class' => 'text-muted']);
                        },
                    ],
                    [
                        'attribute' => 'estado',
                        'label' => 'Estado',
                        'format' => 'raw',
                        'filter' => ['1' => 'Activo', '0' => 'Inactivo'],
                        'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                        'value' => fn($m) => $m->estado === '1'
                            ? Html::tag('span', 'Activo', ['class' => 'label label-success'])
                            : Html::tag('span', 'Inactivo', ['class' => 'label label-default']),
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
                                    'data-nombre' => $m->nombre,
                                ]) : '',
                            'delete' => fn($url, $m) => $puede['eliminar']
                                ? Html::a('<i class="fa fa-trash"></i>', '#', [
                                    'class' => 'btn btn-danger', 'title' => 'Eliminar',
                                    'data-listado-eliminar' => $url, 'data-nombre' => $m->nombre,
                                    'data-aviso' => 'Se borran sus datos y su foto. No se puede deshacer. Si solo querés que no aparezca en la web, usá Desactivar.',
                                ]) : '',
                        ],
                        'urlCreator' => fn($action, $m) => Url::to([$action, 'id' => $m->idmiembro]),
                    ],
                ],
            ]) ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</section>
