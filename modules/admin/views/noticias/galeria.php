<?php

/**
 * Galería de fotos de la noticia: arrastrar y soltar varias; cada una se puede borrar.
 * En la web, si hay fotos acá, reemplazan a la imagen de la noticia por un carrusel de fotos.
 * Kartik FileInput; al terminar de subir recarga la página (huk-noticias.js).
 *
 * @var yii\web\View $this
 * @var app\models\Noticia $model
 */

use kartik\widgets\FileInput;
use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\NoticiasAdminAsset::register($this);

$this->title = 'Galería: ' . $model->titulo;
$csrf = [Yii::$app->request->csrfParam => Yii::$app->request->csrfToken];
$preview = [];
$previewConfig = [];
foreach ($model->imagenes as $img) {
    $preview[] = $model->url('galeria/' . $img->archivo);
    $previewConfig[] = ['caption' => $img->nombre_original, 'key' => $img->idimagen, 'url' => Url::to(['galeria-borrar']), 'extra' => $csrf];
}
?>

<section class="content-header">
    <h1><i class="fa fa-picture-o"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body huk-galeria">
            <p class="text-muted">
                <i class="fa fa-info-circle"></i> Arrastre las fotos o toque <b>Examinar</b>. JPG, PNG o WEBP de hasta <b>8 MB</b> cada una.
                En la web, las fotos de la galería <b>reemplazan a la imagen de la noticia</b> por un carrusel.
                Espere a que diga <b>"Hecho"</b> antes de salir.
            </p>
            <?= FileInput::widget([
                'name' => 'imagenes[]',
                'id' => 'galeria-imagenes',
                'language' => 'es',
                'options' => ['multiple' => true, 'accept' => '.jpg,.jpeg,.png,.webp'],
                'pluginOptions' => [
                    'theme' => 'fa4',
                    'uploadUrl' => Url::to(['galeria-subir', 'id' => $model->idnoticia]),
                    'uploadExtraData' => $csrf,
                    'deleteExtraData' => $csrf,
                    'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'webp'],
                    'maxFileSize' => 8192,
                    'initialPreview' => $preview,
                    'initialPreviewAsData' => true,
                    'initialPreviewFileType' => 'image',
                    'initialPreviewConfig' => $previewConfig,
                    'overwriteInitial' => false,
                    'showRemove' => false,
                    'browseClass' => 'btn btn-primary',
                    'uploadClass' => 'btn btn-success',
                    'browseLabel' => 'Examinar …',
                    'uploadLabel' => 'Subir',
                    'dropZoneTitle' => 'Arrastre y suelte aquí las fotos …',
                ],
            ]) ?>
        </div>
        <div class="box-footer">
            <?= Html::a('<i class="fa fa-arrow-left"></i> Volver a la noticia', ['view', 'id' => $model->idnoticia], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
</section>
