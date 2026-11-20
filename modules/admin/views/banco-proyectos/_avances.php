<?php

/**
 * Pestaña Avances: bitácora del proyecto (del más nuevo al más viejo) + % de avance actual.
 * Ocultar / eliminar: huk-listado.js. Agregar archivo con barra de progreso: huk-proyectos.js (form[data-subida]).
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $model
 * @var bool $puedeEditar
 * @var app\models\ProyectoAvance[] $avances
 * @var int $limiteServidor bytes
 */

use app\components\SubidaArchivo;
use app\models\Proyecto;
use app\models\ProyectoAvance;
use app\models\ProyectoAvanceArchivo;
use yii\helpers\Html;
use yii\helpers\Url;

$maxBytes = min(Proyecto::MAX_VIDEO_MB * 1048576, $limiteServidor ?: PHP_INT_MAX);
$conObra = $model->tipo === 'financiamiento';

/** Descripción del archivo + lápiz para cambiarla (modal: huk-proyectos.js, data-leyenda-editar). */
$leyenda = function (ProyectoAvanceArchivo $x) use ($puedeEditar) {
    $texto = $x->leyenda !== null ? Html::encode($x->leyenda) : ($puedeEditar ? '<span class="text-muted">Sin descripción</span>' : '');
    $lapiz = $puedeEditar ? Html::button('<i class="fa fa-pencil"></i>', [
        'class' => 'btn btn-link btn-xs huk-leyenda-editar',
        'title' => 'Escribir o cambiar la descripción',
        'data-leyenda-editar' => Url::to(['avance-archivo-leyenda', 'id' => $x->idarchivo]),
        'data-leyenda' => (string) $x->leyenda,
        'data-max' => ProyectoAvanceArchivo::MAX_LEYENDA,
    ]) : '';
    return $texto === '' ? '' : Html::tag('div', $texto . ' ' . $lapiz, ['class' => 'huk-leyenda']);
};
?>

<div class="row">
    <div class="col-lg-8">
        <?php if (!$avances): ?>
            <div class="box box-primary"><div class="box-body">
                <p class="text-muted">Todavía no hay avances. Cada vez que pase algo importante (un permiso aprobado, inicio de obra,
                    una firma), cargue un avance: el inversor ve la evolución sin que se pierda lo anterior.</p>
            </div></div>
        <?php endif; ?>

        <div class="huk-avances">
            <?php foreach ($avances as $a): ?>
                <?php $visible = $a->estado === '1'; ?>
                <div class="box box-primary huk-avance<?= $visible ? '' : ' is-oculto' ?>" id="avance-<?= $a->idavance ?>">
                    <div class="box-header with-border">
                        <span class="huk-avance-fecha"><?= date('d/m/Y', strtotime($a->fecha)) ?></span>
                        <h3 class="box-title"><?= Html::encode($a->titulo) ?></h3>
                        <?php if ($a->etapa): ?><span class="label label-default"><?= Html::encode($a->etapaNombre()) ?></span><?php endif; ?>
                        <?php if ($a->porcentaje !== null): ?><span class="label huk-label-porcentaje"><?= (int) $a->porcentaje ?> %</span><?php endif; ?>
                        <?php if (!$visible): ?><span class="label label-warning">Oculto</span><?php endif; ?>
                        <?php if ($puedeEditar): ?>
                            <div class="box-tools">
                                <?= Html::a('<i class="fa fa-pencil"></i>', ['avance-editar', 'id' => $a->idavance], ['class' => 'btn btn-info btn-xs', 'title' => 'Editar']) ?>
                                <?= Html::button('<i class="fa ' . ($visible ? 'fa-eye-slash' : 'fa-eye') . '"></i>', [
                                    'class' => 'btn btn-xs ' . ($visible ? 'btn-warning' : 'btn-success'),
                                    'title' => $visible ? 'Ocultar' : 'Mostrar',
                                    'data-listado-estado' => Url::to(['avance-estado', 'id' => $a->idavance]),
                                    'data-accion' => $visible ? 'desactivar' : 'activar',
                                    'data-nombre' => $a->titulo,
                                    'data-aviso' => $visible ? 'Los inversores dejan de verlo. Si tenía etapa o %, la ficha toma la del avance anterior.' : 'Los inversores lo vuelven a ver.',
                                ]) ?>
                                <?= Html::button('<i class="fa fa-trash"></i>', [
                                    'class' => 'btn btn-danger btn-xs', 'title' => 'Eliminar',
                                    'data-listado-eliminar' => Url::to(['avance-eliminar', 'id' => $a->idavance]),
                                    'data-nombre' => $a->titulo,
                                    'data-aviso' => 'Se borran el texto y sus archivos. No se puede deshacer. Si solo desea que no se vea, use Ocultar.',
                                ]) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="box-body">
                        <?php if ($a->texto): ?>
                            <div class="huk-rich"><?= $a->texto /* HTML limpio (HtmlEditor) */ ?></div>
                        <?php endif; ?>

                        <?php if ($fotos = $a->archivosDe('foto')): ?>
                            <div class="huk-avance-fotos">
                                <?php foreach ($fotos as $x): ?>
                                    <figure>
                                        <?= Html::a(Html::img($x->src(), ['alt' => (string) $x->leyenda, 'loading' => 'lazy']), $x->src(), ['target' => '_blank', 'rel' => 'noopener']) ?>
                                        <?= $leyenda($x) ?>
                                        <?php if ($puedeEditar): ?>
                                            <?= Html::button('<i class="fa fa-times"></i>', ['class' => 'huk-avance-quitar', 'title' => 'Quitar foto',
                                                'data-listado-eliminar' => Url::to(['avance-archivo-eliminar', 'id' => $x->idarchivo]), 'data-nombre' => $x->nombre_original]) ?>
                                        <?php endif; ?>
                                    </figure>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php $videos = array_merge($a->archivosDe('video'), $a->archivosDe('enlace')); ?>
                        <?php if ($videos): ?>
                            <div class="huk-avance-videos">
                                <?php foreach ($videos as $x): ?>
                                    <div class="huk-avance-video">
                                        <div class="huk-video-reproductor">
                                            <?php if ($x->tipo === 'video'): ?>
                                                <video controls preload="metadata" playsinline src="<?= Html::encode($x->src()) ?>"></video>
                                            <?php else: ?>
                                                <iframe src="<?= Html::encode($x->src()) ?>" title="Video" loading="lazy" allow="fullscreen; picture-in-picture" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                            <?php endif; ?>
                                        </div>
                                        <?= $leyenda($x) ?>
                                        <?php if ($puedeEditar): ?>
                                            <?= Html::button('<i class="fa fa-trash"></i> Quitar', ['class' => 'btn btn-default btn-xs',
                                                'data-listado-eliminar' => Url::to(['avance-archivo-eliminar', 'id' => $x->idarchivo]), 'data-nombre' => $x->nombre_original ?: 'el video']) ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($docs = $a->archivosDe('documento')): ?>
                            <ul class="huk-avance-docs">
                                <?php foreach ($docs as $x): ?>
                                    <li>
                                        <?= Html::a('<i class="fa fa-download"></i> ' . Html::encode($x->nombre_original) . ' <small>(' . SubidaArchivo::peso($x->tamano) . ')</small>', $x->src()) ?>
                                        <?php if ($puedeEditar): ?>
                                            <?= Html::button('<i class="fa fa-times"></i>', ['class' => 'btn btn-link btn-xs text-danger', 'title' => 'Quitar',
                                                'data-listado-eliminar' => Url::to(['avance-archivo-eliminar', 'id' => $x->idarchivo]), 'data-nombre' => $x->nombre_original]) ?>
                                        <?php endif; ?>
                                        <?= $leyenda($x) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if ($puedeEditar): ?>
                            <details class="huk-avance-agregar">
                                <summary><i class="fa fa-paperclip"></i> Agregar fotos, videos o documentos</summary>
                                <?= Html::beginForm(['avance-archivo-agregar', 'id' => $a->idavance], 'post', [
                                    'enctype' => 'multipart/form-data',
                                    'data-subida-varios' => true,
                                    'data-max-bytes' => $maxBytes,
                                    'data-max-texto' => round($maxBytes / 1048576) . ' MB',
                                    'data-max-leyenda' => ProyectoAvanceArchivo::MAX_LEYENDA,
                                ]) ?>
                                <?= Html::fileInput('archivos[]', null, ['class' => 'form-control', 'multiple' => true, 'data-subida-archivos' => true,
                                    'accept' => '.jpg,.jpeg,.png,.webp,.mp4,.mov,.m4v,.webm,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx']) ?>
                                <p class="help-block">Puede elegir varios a la vez. Cada uno puede llevar su descripción.
                                    Máximo por avance: <?= ProyectoAvance::MAXIMOS['foto'] ?> fotos, <?= ProyectoAvance::MAXIMOS['video'] ?> videos
                                    (<?= Proyecto::MAX_VIDEO_MB ?> MB c/u) y <?= ProyectoAvance::MAXIMOS['documento'] ?> documentos.</p>
                                <ul class="huk-subida-lista" data-subida-lista></ul>
                                <div class="huk-avance-enlace">
                                    <?= Html::textInput('url', '', ['class' => 'form-control', 'placeholder' => 'o pegue un enlace de YouTube / Vimeo', 'maxlength' => 500]) ?>
                                    <?= Html::textInput('leyenda', '', ['class' => 'form-control', 'placeholder' => 'Descripción del enlace (opcional)',
                                        'maxlength' => ProyectoAvanceArchivo::MAX_LEYENDA]) ?>
                                </div>
                                <div class="huk-subida-progreso" data-subida-barra hidden>
                                    <div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar">0%</div></div>
                                    <p class="help-block" data-subida-texto></p>
                                </div>
                                <?= Html::submitButton('<i class="fa fa-upload"></i> Subir', ['class' => 'btn btn-success btn-sm', 'data-subida-boton' => true]) ?>
                                <?= Html::endForm() ?>
                            </details>
                        <?php endif; ?>

                        <p class="huk-ayuda">Cargado por <?= Html::encode($a->usuario->usuario ?? '—') ?>
                            el <?= date('d/m/Y H:i', strtotime($a->fecha_registro)) ?><?= $a->fecha_actualizacion ? ' · editado ' . date('d/m/Y H:i', strtotime($a->fecha_actualizacion)) : '' ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-tasks"></i> Cómo va</h3></div>
            <div class="box-body">
                <?php if ($conObra): ?>
                    <p class="huk-avance-etapa">Etapa: <strong><?= Html::encode($model->etapaNombre() ?? 'Sin definir') ?></strong></p>
                    <?php $pct = $model->porcentaje_avance; ?>
                    <?php if ($pct !== null): ?>
                        <div class="progress huk-progreso-avance">
                            <div class="progress-bar" role="progressbar" data-ancho="<?= (int) $pct ?>"><?= (int) $pct ?> %</div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Todavía sin % de avance (se completa al elegir una etapa).</p>
                    <?php endif; ?>
                    <p class="huk-ayuda">El % sale del último avance visible que lo informó. Si ninguno lo hizo, se usa el de la etapa.</p>
                <?php else: ?>
                    <p class="text-muted">Es un "busca inversión": los avances sirven para contar novedades (no hay etapa ni %).</p>
                <?php endif; ?>
                <?php if ($puedeEditar): ?>
                    <?= Html::a('<i class="fa fa-plus"></i> Agregar avance', ['avance-crear', 'id' => $model->idproyecto], ['class' => 'btn btn-success btn-block']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
