<?php

/**
 * Pestaña Fotos: galería con arrastrar y soltar (Kartik FileInput). Al terminar de subir recarga (huk-proyectos.js).
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $model
 * @var bool $puedeEditar
 */

use app\models\Proyecto;
use kartik\widgets\FileInput;
use yii\helpers\Html;
use yii\helpers\Url;

$csrf = [Yii::$app->request->csrfParam => Yii::$app->request->csrfToken];
$preview = [];
$config = [];
foreach ($model->imagenes as $img) {
    $preview[] = $img->url();
    $config[] = ['caption' => $img->nombre_original, 'key' => $img->idimagen, 'url' => Url::to(['galeria-borrar']), 'extra' => $csrf];
}
?>

<div class="box box-primary">
    <div class="box-body huk-galeria">
        <?php if ($puedeEditar): ?>
            <p class="text-muted">
                <i class="fa fa-info-circle"></i> Arrastrá las fotos o tocá <b>Examinar</b>. JPG, PNG o WEBP de hasta <b><?= Proyecto::MAX_IMAGEN_MB ?> MB</b> cada una.
                Esperá a que diga <b>"Hecho"</b> antes de salir. Para borrar una foto usá el tachito.
            </p>
            <?= FileInput::widget([
                'name' => 'imagenes[]',
                'id' => 'proyecto-galeria',
                'language' => 'es',
                'options' => ['multiple' => true, 'accept' => '.jpg,.jpeg,.png,.webp'],
                'pluginOptions' => [
                    'theme' => 'fa4',
                    'uploadUrl' => Url::to(['galeria-subir', 'id' => $model->idproyecto]),
                    'uploadExtraData' => $csrf,
                    'deleteExtraData' => $csrf,
                    'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'webp'],
                    'maxFileSize' => Proyecto::MAX_IMAGEN_MB * 1024,
                    'initialPreview' => $preview,
                    'initialPreviewAsData' => true,
                    'initialPreviewFileType' => 'image',
                    'initialPreviewConfig' => $config,
                    'overwriteInitial' => false,
                    'showRemove' => false,
                    'browseClass' => 'btn btn-primary',
                    'uploadClass' => 'btn btn-success',
                    'browseLabel' => 'Examinar …',
                    'uploadLabel' => 'Subir',
                    'dropZoneTitle' => 'Arrastrá y soltá aquí las fotos …',
                ],
            ]) ?>
        <?php elseif ($preview): ?>
            <div class="huk-eco-imgs">
                <?php foreach ($model->imagenes as $img): ?>
                    <figure><?= Html::img($img->url(), ['alt' => '', 'loading' => 'lazy']) ?></figure>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">Todavía no hay fotos.</p>
        <?php endif; ?>
    </div>
</div>
