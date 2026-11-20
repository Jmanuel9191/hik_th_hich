<?php

namespace app\widgets;

use kartik\widgets\FileInput;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * Campo de UNA imagen con el formato HUK: vista previa, "arrastrá aquí",
 * botones "Elegir imagen" y "Quitar imagen". Para galerías se sigue usando FileInput múltiple.
 *
 * Uso:  $form->field($model, 'imagen')->widget(CampoImagen::class, [
 *           'url' => Banners::url($model->imagen),   // imagen guardada (o null)
 *           'medida' => '1920 × 600 px',
 *       ])
 *
 * "Quitar" sobre la imagen guardada manda Modelo[quitar][campo] = 1; el servicio la borra al guardar
 * (o avisa si es obligatoria). Una imagen nueva siempre gana sobre "quitar".
 * Comportamiento: assets_b/js/huk-imagen.js (CampoImagenAsset).
 */
class CampoImagen extends InputWidget
{
    /** URL de la imagen guardada, o null si no hay. */
    public $url;
    /** Texto de la medida recomendada, ej. "1920 × 600 px". */
    public $medida = '';
    public $extensiones = ['jpg', 'jpeg', 'png', 'webp'];
    public $maxMb = 5;
    /** Vista previa chica (íconos y logos). */
    public $icono = false;

    public function run()
    {
        \app\assets_b\CampoImagenAsset::register($this->view);

        $id = $this->options['id'];
        $formatos = implode(', ', array_unique(array_map('strtoupper', array_diff($this->extensiones, ['jpeg']))));
        $ayuda = trim(($this->medida ? $this->medida . ' · ' : '') . $formatos . ' · máx. ' . $this->maxMb . ' MB');

        $input = FileInput::widget([
            'model' => $this->model,
            'attribute' => $this->attribute,
            'language' => 'es',
            'options' => ['id' => $id, 'accept' => '.' . implode(',.', $this->extensiones)],
            'pluginOptions' => [
                'theme' => 'fa4',
                'showUpload' => false,
                'showCancel' => false,
                'showClose' => false,
                'showRemove' => false,
                'showCaption' => false,
                'showBrowse' => false, // los botones los pone este widget
                'browseOnZoneClick' => true,
                'allowedFileExtensions' => $this->extensiones,
                'maxFileSize' => $this->maxMb * 1024,
                'dropZoneTitle' => 'Arrastre la imagen aquí',
                'dropZoneClickTitle' => '<br>o haga clic para elegirla<br><small>' . Html::encode($ayuda) . '</small>',
                'initialPreview' => $this->url ? [$this->url] : [],
                'initialPreviewAsData' => true,
                'initialPreviewConfig' => $this->url ? [['caption' => 'Imagen guardada', 'showDelete' => false, 'showDrag' => false]] : [],
                'initialPreviewShowDelete' => false,
                'overwriteInitial' => true,
                'fileActionSettings' => ['showUpload' => false, 'showDrag' => false, 'showRemove' => false, 'showZoom' => true],
            ],
        ]);

        $quitar = Html::hiddenInput(Html::getInputName($this->model, 'quitar') . '[' . $this->attribute . ']', '0', ['data-huk-imagen-quitado' => true]);
        $botones = Html::tag('div',
            Html::button('<i class="fa fa-folder-open"></i> <span>Elegir imagen</span>', ['class' => 'btn btn-default', 'data-huk-imagen-elegir' => true])
            . Html::button('<i class="fa fa-trash"></i> Quitar imagen', ['class' => 'btn btn-default huk-imagen-quitar', 'data-huk-imagen-quitar' => true]),
            ['class' => 'huk-imagen-botones']);

        return Html::tag('div', $input . $quitar . $botones, [
            'class' => 'huk-imagen' . ($this->icono ? ' is-icono' : ''),
            'data-huk-imagen' => true,
            'data-actual' => $this->url ? 1 : 0,
            'data-url' => $this->url,
        ]);
    }
}
