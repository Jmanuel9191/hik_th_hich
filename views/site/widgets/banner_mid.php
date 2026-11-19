<?php

/**
 * Banner principal del Inicio (Panel → Banners). Estilo de la plantilla con colores HUK:
 * foto con zoom lento + sombra a la izquierda + figura dorada flotante + antetítulo, título, texto y botón.
 * Varios banners → carrusel automático con flechas y puntitos (huk.js, punto 10). Sin banners vigentes → no se muestra nada.
 * Imagen para celular: si está cargada se usa en pantallas chicas; si no, la de computadora.
 *
 * @var yii\web\View $this
 * @var app\models\Banners[] $banners
 * @var bool $conTarjetas  abajo vienen las tarjetas que suben sobre el banner (deja lugar abajo)
 */

use app\models\Banners;
use yii\helpers\Html;

if (empty($banners)) {
    return;
}
$varios = count($banners) > 1;
$conTarjetas = !empty($conTarjetas);
?>
<section class="huk-banner<?= $conTarjetas ? ' con-tarjetas' : '' ?>" aria-label="Destacados"<?= $varios ? ' data-banner aria-roledescription="carrusel"' : '' ?>>
    <?php foreach ($banners as $i => $b): ?>
        <?php
        $conTexto = $b->antetitulo || $b->titulo || $b->texto || $b->boton_texto;
        $clases = 'huk-banner-slide' . ($i === 0 ? ' is-activo' : '') . ($conTexto ? '' : ' is-solo-imagen');
        ?>
        <div class="<?= $clases ?>" data-banner-slide<?= $varios ? ' role="group" aria-roledescription="diapositiva" aria-label="' . ($i + 1) . ' de ' . count($banners) . '"' : '' ?><?= $i > 0 ? ' aria-hidden="true"' : '' ?>>
            <picture class="huk-banner-bg">
                <?php if ($b->imagen_movil): ?>
                    <source media="(max-width: 767.98px)" srcset="<?= Html::encode(Banners::url($b->imagen_movil)) ?>">
                <?php endif; ?>
                <?= Html::img(Banners::url($b->imagen), [
                    'alt' => $b->titulo ?: '',
                    'loading' => $i === 0 ? 'eager' : 'lazy',
                    'fetchpriority' => $i === 0 ? 'high' : null,
                ]) ?>
            </picture>

            <?php if ($conTexto): ?>
                <div class="container huk-banner-contenido">
                    <?php if ($b->antetitulo): ?>
                        <p class="huk-banner-ante"><?= Html::encode($b->antetitulo) ?></p>
                    <?php endif; ?>
                    <?php if ($b->titulo): ?>
                        <?= Html::tag($i === 0 ? 'h1' : 'h2', Html::encode($b->titulo), ['class' => 'huk-banner-titulo']) ?>
                    <?php endif; ?>
                    <?php if ($b->texto): ?>
                        <p class="huk-banner-texto"><?= nl2br(Html::encode($b->texto)) ?></p>
                    <?php endif; ?>
                    <?php if ($b->boton_texto && $b->boton_url): ?>
                        <?= Html::a(
                            Html::encode($b->boton_texto) . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>',
                            $b->boton_url,
                            ['class' => 'btn btn-primary huk-banner-btn'] + ($b->enlaceExterno() ? ['target' => '_blank', 'rel' => 'noopener'] : [])
                        ) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <!-- Figura dorada que flota (en todos los banners) -->
    <svg class="huk-banner-shard" viewBox="0 0 90 120" aria-hidden="true" focusable="false">
        <path d="M40 30 90 0v46L40 76z" fill="currentColor"/>
        <path d="M0 84 34 64v30L0 114z" fill="#fff"/>
    </svg>

    <?php if ($varios): ?>
        <button type="button" class="huk-banner-flecha is-anterior" data-banner-anterior aria-label="Banner anterior">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </button>
        <button type="button" class="huk-banner-flecha is-siguiente" data-banner-siguiente aria-label="Banner siguiente">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
        <div class="container huk-banner-puntos">
            <?php foreach ($banners as $i => $b): ?>
                <button type="button" class="huk-banner-punto<?= $i === 0 ? ' is-activo' : '' ?>" data-banner-ir="<?= $i ?>"
                        aria-label="Ver banner <?= $i + 1 ?>"<?= $i === 0 ? ' aria-current="true"' : '' ?>></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
