<?php

/**
 * Página institucional (una por subpágina de Nosotros, el pie y Gobierno y ESG). Pestañas = secciones de esa página.
 * Cada bloque muestra cómo está cargado + su lista (orden ↑ ↓, estado y eliminar: huk-listado.js).
 *
 * @var yii\web\View $this
 * @var string $pagina
 * @var array $config  PaginaSeccion::PAGINAS[$pagina]
 * @var string $tab
 * @var app\models\PaginaSeccion[] $bloques  clave => bloque
 * @var array $listas  clave => ['items' => PaginaItem[], 'extremos' => [min, max]]
 * @var yii\data\ActiveDataProvider|null $historial
 * @var bool $puedeEditar
 */

use app\models\PaginaItem;
use app\models\PaginaSeccion;
use app\widgets\MapaPaises;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;

\app\assets_b\PaginasAdminAsset::register($this);
$this->params['pagina'] = $pagina; // menú lateral

$this->title = $config['nombre'];
$vacio = Html::tag('span', '—', ['class' => 'text-muted']);
$resumen = fn($html) => StringHelper::truncate(trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 110);
?>

<section class="content-header">
    <h1><i class="fa fa-<?= Html::encode($config['icono']) ?>"></i> <?= Html::encode($config['nombre']) ?></h1>
    <p class="huk-inicio-sub"><?= Html::encode($config['descripcion']) ?></p>
</section>

<section class="content">
    <ul class="nav nav-tabs huk-tabs">
        <?php foreach ($config['pestanas'] as $id => $p): ?>
            <li class="<?= $id === $tab ? 'active' : '' ?>">
                <?= Html::a(Html::encode($p['nombre']), ['index', 'pagina' => $pagina, 'tab' => $id]) ?>
            </li>
        <?php endforeach; ?>
        <?php if (!empty($config['web'])): ?>
            <li class="huk-tab-externa">
                <?= Html::a('Ver en la web <i class="fa fa-external-link"></i>', $config['web'], ['target' => '_blank', 'rel' => 'noopener']) ?>
            </li>
        <?php endif; ?>
    </ul>

    <?php foreach ($bloques as $clave => $bloque): ?>
        <?php
        $cfg = $bloque->config();
        $visible = $bloque->estado === '1';
        $campos = $cfg['campos'];
        ?>
        <div class="box box-primary huk-bloque<?= $visible ? '' : ' is-oculto' ?>" id="bloque-<?= Html::encode($clave) ?>">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-<?= Html::encode($cfg['icono'] ?? 'square-o') ?>"></i> <?= Html::encode($cfg['nombre']) ?></h3>
                <?= $visible
                    ? Html::tag('span', 'Visible en la web', ['class' => 'label label-success'])
                    : Html::tag('span', 'Oculto', ['class' => 'label label-default']) ?>
                <?php if ($puedeEditar): ?>
                    <div class="box-tools">
                        <?php if ($campos): ?>
                            <?= Html::a('<i class="fa fa-pencil"></i> Editar', ['seccion', 'id' => $bloque->idseccion], ['class' => 'btn btn-info btn-sm']) ?>
                        <?php endif; ?>
                        <?= Html::a('<i class="fa ' . ($visible ? 'fa-eye-slash' : 'fa-eye') . '"></i> ' . ($visible ? 'Ocultar' : 'Mostrar'), '#', [
                            'class' => 'btn btn-sm ' . ($visible ? 'btn-warning' : 'btn-success'),
                            'data-listado-estado' => Url::to(['seccion-estado', 'id' => $bloque->idseccion]),
                            'data-accion' => $visible ? 'desactivar' : 'activar',
                            'data-nombre' => $cfg['nombre'],
                            'data-aviso' => $visible
                                ? 'El bloque entero deja de mostrarse en la web. No se borra nada.'
                                : 'El bloque vuelve a mostrarse en la web.',
                        ]) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="box-body">
                <?php if (!empty($cfg['ayuda'])): ?>
                    <p class="huk-aviso-contacto"><i class="fa fa-info-circle"></i> <?= Html::encode($cfg['ayuda']) ?></p>
                <?php endif; ?>

                <?php if ($campos): ?>
                    <div class="huk-bloque-contenido<?= isset($campos['imagen']) ? ' con-imagen' : '' ?>">
                        <dl class="huk-bloque-datos">
                            <?php foreach ($campos as $campo => [$etiqueta]): ?>
                                <?php if ($campo === 'imagen') continue; ?>
                                <dt><?= Html::encode($etiqueta) ?></dt>
                                <dd class="<?= $campo === 'texto' ? 'huk-rich' : '' ?>">
                                    <?php if ($bloque->$campo === null || $bloque->$campo === ''): ?>
                                        <?= $vacio ?>
                                    <?php elseif ($campo === 'texto'): ?>
                                        <?= $bloque->textoHtml() /* HTML limpio o texto escapado */ ?>
                                    <?php elseif ($campo === 'frase'): ?>
                                        <blockquote class="huk-bloque-frase"><?= Html::encode($bloque->frase) ?></blockquote>
                                    <?php elseif ($campo === 'imagen_firma'): ?>
                                        <?= Html::img(PaginaSeccion::url($bloque->imagen_firma), ['alt' => 'Firma', 'width' => 160, 'loading' => 'lazy']) ?>
                                    <?php elseif ($campo === 'titulo'): ?>
                                        <strong class="huk-bloque-titulo"><?= Html::encode($bloque->titulo) ?></strong>
                                    <?php else: ?>
                                        <?= Html::encode($bloque->$campo) ?>
                                    <?php endif; ?>
                                </dd>
                            <?php endforeach; ?>
                        </dl>
                        <?php if (!empty($cfg['mapa']) && !$bloque->imagen): ?>
                            <figure class="huk-bloque-imagen huk-bloque-mapa">
                                <?= MapaPaises::widget(['activos' => ArrayHelper::map(
                                    array_filter($listas[$clave]['items'] ?? [], fn($i) => $i->estado === '1' && $i->codigo),
                                    'codigo', 'titulo')]) ?>
                                <figcaption>Mapa dibujado (se pinta con los países activos)</figcaption>
                            </figure>
                        <?php elseif (isset($campos['imagen'])): ?>
                            <figure class="huk-bloque-imagen">
                                <?= $bloque->imagen
                                    ? Html::img(PaginaSeccion::url($bloque->imagen), ['alt' => '', 'loading' => 'lazy'])
                                    : Html::tag('span', '<i class="fa fa-picture-o"></i> Sin ' . mb_strtolower($campos['imagen'][0]), ['class' => 'huk-eco-vacio']) ?>
                                <figcaption><?= Html::encode($campos['imagen'][0]) ?></figcaption>
                            </figure>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($listas[$clave])): ?>
                    <?php
                    $lista = $cfg['lista'];
                    [$min, $max] = $listas[$clave]['extremos'];
                    $imgCampo = isset($lista['campos']['icono']) ? 'icono' : (isset($lista['campos']['imagen']) ? 'imagen' : null);
                    ?>
                    <div class="huk-bloque-lista">
                        <?php Pjax::begin(['id' => 'lista-' . $clave, 'timeout' => 5000]); ?>
                        <div class="huk-barra-acciones">
                            <h4><?= Html::encode($lista['nombre']) ?></h4>
                            <?php if ($puedeEditar): ?>
                                <?= Html::a('<i class="fa fa-plus"></i> Agregar ' . Html::encode($lista['singular']), ['item-crear', 'seccion' => $bloque->idseccion], ['class' => 'btn btn-success btn-sm', 'data-pjax' => '0']) ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($lista['ayuda'])): ?>
                            <p class="huk-ayuda"><i class="fa fa-info-circle"></i> <?= Html::encode($lista['ayuda']) ?></p>
                        <?php endif; ?>
                        <?= GridView::widget([
                            'dataProvider' => new \yii\data\ArrayDataProvider(['allModels' => $listas[$clave]['items'], 'pagination' => false]),
                            'tableOptions' => ['class' => 'table table-striped table-hover huk-con-orden'],
                            'layout' => '{items}',
                            'emptyText' => 'Todavía no hay nada cargado.',
                            'columns' => array_values(array_filter([
                                [
                                    'label' => 'Orden',
                                    'format' => 'raw',
                                    'contentOptions' => ['class' => 'huk-orden'],
                                    'value' => function (PaginaItem $m) use ($puedeEditar, $min, $max) {
                                        if (!$puedeEditar) {
                                            return '';
                                        }
                                        $boton = fn($dir, $icono, $titulo) => Html::button('<i class="fa fa-' . $icono . '"></i>', [
                                            'class' => 'btn btn-default btn-xs',
                                            'title' => $titulo,
                                            'data-listado-mover' => Url::to(['item-mover', 'id' => $m->iditem, 'direccion' => $dir]),
                                        ]);
                                        return ((int) $m->orden > $min ? $boton(-1, 'arrow-up', 'Subir') : '<span class="huk-orden-vacio"></span>')
                                            . ((int) $m->orden < $max ? $boton(1, 'arrow-down', 'Bajar') : '<span class="huk-orden-vacio"></span>');
                                    },
                                ],
                                ['label' => 'N°', 'contentOptions' => ['class' => 'huk-eco-num'], 'value' => fn($m, $k, $i) => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)],
                                $imgCampo ? [
                                    'label' => $imgCampo === 'icono' ? 'Ícono' : 'Imagen',
                                    'format' => 'raw',
                                    'value' => fn(PaginaItem $m) => $m->$imgCampo
                                        ? Html::img(PaginaItem::url($m->$imgCampo), ['class' => 'huk-item-mini' . ($imgCampo === 'icono' ? ' is-icono' : ''), 'alt' => '', 'loading' => 'lazy'])
                                        : $vacio,
                                ] : null,
                                isset($lista['campos']['codigo']) ? [
                                    'label' => $lista['campos']['codigo'],
                                    'format' => 'raw',
                                    'value' => fn(PaginaItem $m) => Html::tag('strong', Html::encode($m->pais())),
                                ] : null,
                                isset($lista['campos']['cifra']) ? [
                                    'label' => $lista['campos']['cifra'],
                                    'format' => 'raw',
                                    'value' => fn(PaginaItem $m) => $m->cifra !== null ? Html::tag('span', Html::encode($m->cifra), ['class' => 'huk-item-cifra']) : $vacio,
                                ] : null,
                                [
                                    'label' => $lista['campos']['titulo'],
                                    'format' => 'raw',
                                    'value' => fn(PaginaItem $m) => Html::tag(isset($lista['campos']['codigo']) ? 'span' : 'strong', Html::encode($m->titulo))
                                        . (isset($lista['campos']['texto']) && $m->texto ? Html::tag('div', Html::encode($resumen($m->texto)), ['class' => 'huk-eco-resumen']) : ''),
                                ],
                                [
                                    'label' => 'Estado',
                                    'format' => 'raw',
                                    'value' => fn(PaginaItem $m) => $m->estado === '1'
                                        ? Html::tag('span', 'Activo', ['class' => 'label label-success'])
                                        : Html::tag('span', 'Inactivo', ['class' => 'label label-default']),
                                ],
                                $puedeEditar ? [
                                    'label' => 'Operaciones',
                                    'format' => 'raw',
                                    'contentOptions' => ['class' => 'huk-item-ops'],
                                    'value' => fn(PaginaItem $m) => Html::tag('div',
                                        Html::a('<i class="fa fa-pencil"></i>', ['item-editar', 'id' => $m->iditem], ['class' => 'btn btn-info', 'title' => 'Editar', 'data-pjax' => '0'])
                                        . Html::a('<i class="fa ' . ($m->estado === '1' ? 'fa-eye-slash' : 'fa-check') . '"></i>', '#', [
                                            'class' => 'btn ' . ($m->estado === '1' ? 'btn-warning' : 'btn-success'),
                                            'title' => $m->estado === '1' ? 'Desactivar' : 'Activar',
                                            'data-listado-estado' => Url::to(['item-estado', 'id' => $m->iditem]),
                                            'data-accion' => $m->estado === '1' ? 'desactivar' : 'activar',
                                            'data-nombre' => $m->titulo,
                                        ])
                                        . Html::a('<i class="fa fa-trash"></i>', '#', [
                                            'class' => 'btn btn-danger', 'title' => 'Eliminar',
                                            'data-listado-eliminar' => Url::to(['item-eliminar', 'id' => $m->iditem]),
                                            'data-nombre' => $m->titulo,
                                        ]),
                                        ['class' => 'btn-group btn-group-justified', 'role' => 'group']),
                                ] : null,
                            ])),
                        ]) ?>
                        <?php Pjax::end(); ?>
                    </div>
                <?php endif; ?>

                <?php if ($bloque->fecha_actualizacion): ?>
                    <p class="huk-ayuda">Última modificación del bloque: <?= date('d/m/Y H:i', strtotime($bloque->fecha_actualizacion)) ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Historial de cambios (<?= Html::encode($config['nombre']) ?>)</h3></div>
        <div class="box-body">
            <?php if ($historial === null): ?>
                <p class="text-muted">No tiene permiso para ver el historial.</p>
            <?php else: ?>
                <?php Pjax::begin(['id' => 'paginas-historial', 'timeout' => 5000]); ?>
                <?= GridView::widget([
                    'dataProvider' => $historial,
                    'tableOptions' => ['class' => 'table table-hover'],
                    'emptyText' => 'Todavía no hay cambios.',
                    'summary' => '{totalCount} cambio(s)',
                    'columns' => [
                        ['label' => 'Fecha', 'value' => fn($h) => date('d/m/Y H:i', strtotime($h->fecha)), 'contentOptions' => ['class' => 'text-nowrap']],
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
