<?php

/**
 * Pestaña Videos: galería de videos (archivo privado o enlace de YouTube / Vimeo).
 * Subida con barra de progreso: huk-proyectos.js (form[data-subida]). Orden y eliminar: huk-listado.js.
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $model
 * @var bool $puedeEditar
 * @var int $limiteServidor bytes
 */

use app\components\SubidaArchivo;
use app\models\Proyecto;
use app\models\ProyectoVideo;
use yii\helpers\Html;
use yii\helpers\Url;

$videos = $model->videos;
$subidos = count(array_filter($videos, fn($v) => $v->tipo === 'archivo'));
$maxBytes = min(Proyecto::MAX_VIDEO_MB * 1048576, $limiteServidor ?: PHP_INT_MAX);
$nuevo = new ProyectoVideo();
?>

<div class="row">
    <div class="<?= $puedeEditar ? 'col-lg-8' : 'col-lg-12' ?>">
        <?php if (!$videos): ?>
            <div class="box box-primary"><div class="box-body"><p class="text-muted">Todavía no hay videos.</p></div></div>
        <?php endif; ?>
        <div class="huk-videos">
            <?php foreach ($videos as $i => $v): ?>
                <div class="box box-primary huk-video">
                    <div class="huk-video-reproductor">
                        <?php if ($v->tipo === 'archivo'): ?>
                            <video controls preload="metadata" playsinline src="<?= Html::encode($v->src()) ?>"></video>
                        <?php elseif ($v->src()): ?>
                            <iframe src="<?= Html::encode($v->src()) ?>" title="<?= Html::encode($v->titulo) ?>" loading="lazy"
                                allow="fullscreen; picture-in-picture" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        <?php endif; ?>
                    </div>
                    <div class="box-body huk-video-datos">
                        <strong><?= Html::encode($v->titulo) ?></strong>
                        <small>
                            <?= $v->tipo === 'archivo'
                                ? '<i class="fa fa-file-video-o"></i> ' . Html::encode($v->nombre_original) . ' · ' . SubidaArchivo::peso($v->tamano)
                                : '<i class="fa fa-link"></i> Enlace' ?>
                        </small>
                        <?php if ($puedeEditar): ?>
                            <div class="huk-video-acciones">
                                <?= $i > 0 ? Html::button('<i class="fa fa-arrow-up"></i>', ['class' => 'btn btn-default btn-xs', 'title' => 'Subir',
                                    'data-listado-mover' => Url::to(['video-mover', 'id' => $v->idvideo, 'direccion' => -1])]) : '' ?>
                                <?= $i < count($videos) - 1 ? Html::button('<i class="fa fa-arrow-down"></i>', ['class' => 'btn btn-default btn-xs', 'title' => 'Bajar',
                                    'data-listado-mover' => Url::to(['video-mover', 'id' => $v->idvideo, 'direccion' => 1])]) : '' ?>
                                <?= Html::button('<i class="fa fa-trash"></i>', ['class' => 'btn btn-danger btn-xs', 'title' => 'Eliminar',
                                    'data-listado-eliminar' => Url::to(['video-eliminar', 'id' => $v->idvideo]), 'data-nombre' => $v->titulo]) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($puedeEditar): ?>
        <div class="col-lg-4">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-plus"></i> Agregar video</h3></div>
                <?= Html::beginForm(['video-agregar', 'id' => $model->idproyecto], 'post', [
                    'enctype' => 'multipart/form-data',
                    'data-subida' => true,
                    'data-max-bytes' => $maxBytes,
                    'data-max-texto' => round($maxBytes / 1048576) . ' MB',
                ]) ?>
                <div class="box-body">
                    <div class="form-group">
                        <?= Html::activeLabel($nuevo, 'titulo', ['class' => 'control-label']) ?>
                        <?= Html::activeTextInput($nuevo, 'titulo', ['class' => 'form-control', 'maxlength' => 150, 'required' => true, 'placeholder' => 'Ej.: Recorrido por el terreno']) ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="video-archivo">Archivo de video</label>
                        <?= Html::activeFileInput($nuevo, 'archivo', ['id' => 'video-archivo', 'class' => 'form-control', 'accept' => '.mp4,.mov,.m4v,.webm,video/*',
                            'data-subida-archivo' => true, 'disabled' => $subidos >= Proyecto::MAX_VIDEOS_ARCHIVO]) ?>
                        <p class="help-block">
                            MP4, MOV (iPhone) o WEBM · recomendado hasta <?= Proyecto::MAX_VIDEO_MB ?> MB
                            <?php if ($limiteServidor && $limiteServidor < Proyecto::MAX_VIDEO_MB * 1048576): ?>
                                · <span class="text-danger">hoy el servidor acepta hasta <?= round($limiteServidor / 1048576) ?> MB</span>
                            <?php endif; ?>
                            · subidos: <?= $subidos ?> de <?= Proyecto::MAX_VIDEOS_ARCHIVO ?>.
                        </p>
                    </div>
                    <p class="huk-o">— o —</p>
                    <div class="form-group">
                        <?= Html::activeLabel($nuevo, 'url', ['class' => 'control-label']) ?>
                        <?= Html::activeTextInput($nuevo, 'url', ['class' => 'form-control', 'maxlength' => 500, 'placeholder' => 'https://vimeo.com/123456789']) ?>
                        <p class="help-block">Para videos largos. En Vimeo podés dejarlo privado ("solo con el enlace").</p>
                    </div>
                    <div class="huk-subida-progreso" data-subida-barra hidden>
                        <div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar">0%</div></div>
                        <p class="help-block">Subiendo… no cierres esta página.</p>
                    </div>
                    <p class="huk-aviso-contacto"><i class="fa fa-mobile"></i> <strong>iPhone:</strong> para que se vea en todos los navegadores,
                        grabá en <em>Ajustes → Cámara → Formatos → Más compatible</em>.</p>
                </div>
                <div class="box-footer text-center">
                    <?= Html::submitButton('<i class="fa fa-upload"></i> Agregar video', ['class' => 'btn btn-success', 'data-subida-boton' => true]) ?>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    <?php endif; ?>
</div>
