<?php

/**
 * Galería del proyecto: las fotos de la pestaña Fotos (todas o las elegidas) con foto grande, flechas,
 * miniaturas, contador, teclado y dedo (huk.js punto 15). Sin fotos: en la web no se muestra.
 * @var app\models\bloques\BloqueGaleriaProyecto $b
 * @var app\models\Proyecto $proyecto
 * @var app\components\MediosProyecto $medios
 * @var array $biblioteca
 * @var string $sizes
 */

use yii\helpers\Html;

$fotos = $b->fotos($biblioteca['fotos']);
if (!$fotos) {
    if ($medios->esPanel()) {
        echo '<p class="huk-pro-nota">Vista previa: el proyecto todavía no tiene fotos para esta galería (pestaña Fotos). En la web este bloque no se muestra.</p>';
    }
    return;
}
$varias = count($fotos) > 1;
?>
<div class="huk-galeria-web huk-pro-galeria" data-galeria aria-roledescription="carrusel" aria-label="<?= Html::encode($b->titulo ?: 'Galería de fotos') ?>">
    <p class="huk-galeria-web-titulo"><i class="fa-regular fa-images" aria-hidden="true"></i> <?= Html::encode($b->titulo ?: 'Galería de fotos') ?>
        <?php if ($varias): ?><span data-galeria-contador>1 / <?= count($fotos) ?></span><?php endif; ?></p>
    <div class="huk-galeria-web-escenario">
        <?php foreach ($fotos as $i => $f): ?>
            <?= $medios->img('galeria', $f->archivo, $proyecto->titulo . ' — foto ' . ($i + 1), $sizes, [
                'class' => $i ? '' : 'is-activa', 'data-galeria-foto' => $i, 'loading' => $i ? 'lazy' : 'eager',
            ]) ?>
        <?php endforeach; ?>
        <?php if ($varias): ?>
            <button type="button" class="huk-galeria-web-flecha is-anterior" data-galeria-anterior aria-label="Foto anterior"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
            <button type="button" class="huk-galeria-web-flecha is-siguiente" data-galeria-siguiente aria-label="Foto siguiente"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
        <?php endif; ?>
    </div>
    <?php if ($varias): ?>
        <div class="huk-galeria-web-miniaturas">
            <?php foreach ($fotos as $i => $f): ?>
                <button type="button" class="<?= $i ? '' : 'is-activa' ?>" data-galeria-ir="<?= $i ?>" aria-label="Ver foto <?= $i + 1 ?>">
                    <?= Html::img($medios->imagen('galeria', $f->archivo, 480), ['alt' => '', 'loading' => 'lazy', 'decoding' => 'async']) ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
