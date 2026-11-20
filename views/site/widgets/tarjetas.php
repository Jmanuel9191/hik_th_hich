<?php

/**
 * Tarjetas del Inicio (Conectar · Transformar · Multiplicar): Panel → Páginas → Quiénes somos → Quiénes somos.
 * Carrusel: 3 por vista en computadora, 2 en tablet, 1 en celular (CSS --tarjetas-por-vista).
 * Flechas y cambio automático (cada 4 s) solo si no entran todas: huk.js punto 11.
 * Cada tarjeta aparece "de la nada" al entrar en pantalla (data-reveal="nada").
 *
 * @var yii\web\View $this
 * @var app\models\PaginaItem[] $tarjetas
 * @var bool $conBanner  arriba hay banner: las tarjetas suben y se apoyan sobre él
 */

use app\models\PaginaItem;
use yii\helpers\Html;

if (empty($tarjetas)) {
    return;
}
?>
<section class="huk-tarjetas<?= !empty($conBanner) ? ' sobre-banner' : '' ?>" data-tarjetas aria-label="Lo que hacemos">
    <div class="container">
        <div class="huk-tarjetas-carrusel">
            <div class="huk-tarjetas-marco">
                <div class="huk-tarjetas-pista" data-tarjetas-pista>
                    <?php foreach ($tarjetas as $i => $t): ?>
                        <div class="huk-tarjeta-celda" data-tarjeta data-reveal="nada" data-reveal-delay="<?= min($i, 3) * 150 ?>">
                            <article class="huk-tarjeta">
                                <?php if ($t->icono): ?>
                                    <?= Html::img(PaginaItem::url($t->icono), ['class' => 'huk-tarjeta-icono', 'alt' => '', 'width' => 72, 'height' => 72, 'loading' => 'lazy']) ?>
                                <?php endif; ?>
                                <h3><?= Html::encode($t->titulo) ?></h3>
                                <?php if ($t->texto): ?>
                                    <p><?= nl2br(Html::encode($t->texto)) ?></p>
                                <?php endif; ?>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="button" class="huk-tarjetas-flecha is-anterior" data-tarjetas-anterior aria-label="Tarjeta anterior" hidden>
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
            </button>
            <button type="button" class="huk-tarjetas-flecha is-siguiente" data-tarjetas-siguiente aria-label="Tarjeta siguiente" hidden>
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</section>
