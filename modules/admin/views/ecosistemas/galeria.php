<?php

/**
 * Galería del ecosistema: arrastrar y soltar varias fotos; cada una se puede borrar.
 * Usa Kartik FileInput (ya está en el proyecto). Al terminar de subir recarga (huk-ecosistemas.js).
 *
 * @var yii\web\View $this
 * @var app\models\Ecosistema $model
 */

use kartik\widgets\FileInput;
use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\EcosistemasAdminAsset::register($this);

$this->title = 'Galería: ' . $model->nombre;
$csrf = [Yii::$app->request->csrfParam => Yii::$app->request->csrfToken];

$preview = [];
$previewConfig = [];
foreach ($model->imagenes as $img) {
    $preview[] = $model->url('galeria/' . $img->archivo);
    $previewConfig[] = [
        'caption' => $img->nombre_original,
        'key' => $img->idimagen,
        'url' => Url::to(['galeria-borrar']),
        'extra' => $csrf,
    ];
}
?>

<section class="content-header">
    <h1><i class="fa fa-picture-o"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body huk-galeria">
            <p class="text-muted">
                <i class="fa fa-info-circle"></i> Arrastrá las fotos o tocá <b>Examinar</b>. JPG, PNG o WEBP de hasta <b>8 MB</b> cada una.
                Esperá a que diga <b>"Hecho"</b> antes de salir. Para borrar una foto usá el tachito de cada imagen.
            </p>
            <?= FileInput::widget([
                'name' => 'imagenes[]',
                'id' => 'galeria-imagenes',
                'language' => 'es',
                'options' => ['multiple' => true, 'accept' => '.jpg,.jpeg,.png,.webp'],
                'pluginOptions' => [
                    'theme' => 'fa4', // íconos Font Awesome 4, como el resto del panel
                    'uploadUrl' => Url::to(['galeria-subir', 'id' => $model->idecosistema]),
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
                    'dropZoneTitle' => 'Arrastrá y soltá aquí las fotos …',
                ],
            ]) ?>
        </div>
        <div class="box-footer">
            <?= Html::a('<i class="fa fa-arrow-left"></i> Volver', ['view', 'id' => $model->idecosistema], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
</section>
