<?php

/**
 * Prefooter (lo dibuja app\widgets\Prefooter): foto de fondo, logo, título en dos líneas y botón.
 * Panel → Banners → Prefooter: Título = primera línea (blanco) · Texto = segunda línea (dorado) · Botón opcional.
 * Efectos: el logo aparece "de la nada", la primera línea entra de la izquierda, la dorada de la derecha
 * y el botón con zoom; la foto hace un zoom lento. Estilos: huk.css sección 23.
 *
 * @var yii\web\View $this
 * @var app\models\Banners $banner
 * @var string|null $logo  URL del logo del pie (null si no está el archivo)
 */

use app\models\Banners;
use yii\helpers\Html;

$b = $banner;
?>
<section class="huk-prefooter" aria-label="<?= Html::encode($b->titulo ?: 'Contacto') ?>">
    <picture class="huk-prefooter-bg">
        <?php if ($b->imagen_movil): ?>
            <source media="(max-width: 767.98px)" srcset="<?= Html::encode(Banners::url($b->imagen_movil)) ?>">
        <?php endif; ?>
        <?= Html::img(Banners::url($b->imagen), ['alt' => '', 'loading' => 'lazy']) ?>
    </picture>

    <div class="container huk-prefooter-contenido">
        <?php if ($logo): ?>
            <div class="huk-prefooter-logo" data-reveal="nada"><?= Html::img($logo, ['alt' => '', 'loading' => 'lazy']) ?></div>
        <?php endif; ?>
        <?php if ($b->titulo || $b->texto): ?>
            <h2 class="huk-prefooter-titulo">
                <?php if ($b->titulo): ?><span data-reveal="left" data-reveal-delay="150"><?= ($b->titulo) ?></span><?php endif; ?>
                <?php if ($b->texto): ?><span class="is-dorado" data-reveal="right" data-reveal-delay="300"><?= Html::encode($b->texto) ?></span><?php endif; ?>
            </h2>
        <?php endif; ?>
        <?php if ($b->boton_texto && $b->boton_url): ?>
            <div data-reveal="zoom" data-reveal-delay="450">
                <?= Html::a(Html::encode($b->boton_texto) . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>', $b->boton_url, array_merge(
                    ['class' => 'btn btn-primary huk-prefooter-btn huk-shine'],
                    $b->enlaceExterno() ? ['target' => '_blank', 'rel' => 'noopener noreferrer'] : []
                )) ?>
            </div>
        <?php endif; ?>
    </div>

    <svg class="huk-banner-shard" viewBox="0 0 90 120" aria-hidden="true" focusable="false">
        <path d="M40 30 90 0v46L40 76z" fill="currentColor"/>
        <path d="M0 84 34 64v30L0 114z" fill="#fff"/>
    </svg>
</section>
