<?php

/**
 * Galería de fotos con flechas, miniaturas y contador (huk.js punto 15). Reutilizable: noticias y ecosistemas.
 * Estilos: huk.css sección 25 (.huk-galeria-web).
 *
 * @var yii\web\View $this
 * @var string[] $fotos  URLs de las fotos, en orden
 * @var string   $alt    texto base para el alt de cada foto (ej. el título de la página)
 */

use yii\helpers\Html;

if (empty($fotos)) {
    return;
}
$varias = count($fotos) > 1;
?>
<div class="huk-galeria-web" data-galeria data-reveal="zoom" aria-roledescription="carrusel" aria-label="Galería de fotos">
    <p class="huk-galeria-web-titulo"><i class="fa-regular fa-images" aria-hidden="true"></i> Galería de fotos <span data-galeria-contador>1 / <?= count($fotos) ?></span></p>
    <div class="huk-galeria-web-escenario">
        <?php foreach ($fotos as $i => $url): ?>
            <?= Html::img($url, ['alt' => $alt . ' — foto ' . ($i + 1), 'loading' => $i ? 'lazy' : 'eager', 'class' => $i ? '' : 'is-activa', 'data-galeria-foto' => $i]) ?>
        <?php endforeach; ?>
        <?php if ($varias): ?>
            <button type="button" class="huk-galeria-web-flecha is-anterior" data-galeria-anterior aria-label="Foto anterior"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
            <button type="button" class="huk-galeria-web-flecha is-siguiente" data-galeria-siguiente aria-label="Foto siguiente"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
        <?php endif; ?>
    </div>
    <?php if ($varias): ?>
        <div class="huk-galeria-web-miniaturas">
            <?php foreach ($fotos as $i => $url): ?>
                <button type="button" class="<?= $i ? '' : 'is-activa' ?>" data-galeria-ir="<?= $i ?>" aria-label="Ver foto <?= $i + 1 ?>">
                    <?= Html::img($url, ['alt' => '', 'loading' => 'lazy']) ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
