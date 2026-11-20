<?php

/**
 * Inicio · Noticias destacadas (Panel → Blog → destacadas): de a 3 (2 en tablet, 1 en celular); si hay más,
 * carrusel que avanza solo de a una cada 5 s (huk.js punto 12). Sin destacadas no sale.
 *
 * @var yii\web\View $this
 * @var app\models\Noticia[] $noticias
 */

use yii\helpers\Html;

if (!$noticias) {
    return;
}
?>
<section class="huk-proy-carrusel is-noticias" aria-labelledby="inicio-noticias-titulo">
    <div class="container">
        <div class="huk-proy-carrusel-cab" data-reveal>
            <div>
                <p class="huk-nos-etiqueta">Blog</p>
                <h2 id="inicio-noticias-titulo">Noticias destacadas</h2>
            </div>
            <?= Html::a('Ver todas las noticias <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>', ['/noticias/index'], ['class' => 'huk-proy-carrusel-todos']) ?>
        </div>
        <div class="huk-proy-pista is-tres" data-proy-pista tabindex="0" role="region" aria-roledescription="carrusel"
             aria-label="Noticias destacadas: deslice o use las flechas" data-reveal="zoom">
            <?php foreach ($noticias as $i => $n): ?>
                <div class="huk-proy-item" aria-roledescription="diapositiva" aria-label="<?= ($i + 1) . ' de ' . count($noticias) ?>">
                    <?= $this->render('noticia_tarjeta', ['noticia' => $n, 'efecto' => '', 'retraso' => 0]) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="huk-proy-carrusel-control" data-proy-control hidden>
            <button type="button" class="huk-proy-carrusel-flecha" data-proy-anterior aria-label="Noticia anterior"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i></button>
            <div class="huk-proy-carrusel-puntos" data-proy-puntos></div>
            <button type="button" class="huk-proy-carrusel-flecha" data-proy-siguiente aria-label="Noticia siguiente"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
        </div>
    </div>
</section>
