<?php

/**
 * Nosotros → Gobierno y socios · Gobierno corporativo y ESG (Panel → Páginas → Gobierno y socios → Gobierno corporativo y ESG).
 * Cabecera centrada (texto chico + título de la sección) y dos tarjetas:
 *  · clara: principios con check dorado y el lema abajo;
 *  · negra: desarrollo sostenible e impacto (texto grande, indicadores como etiquetas y frase final).
 * Efectos: cabecera con zoom; tarjetas desde la izquierda y la derecha; principios e indicadores aparecen de a uno.
 * Hover: la tarjeta sube con sombra; el check crece; la etiqueta se pone dorada.
 *
 * @var yii\web\View $this
 * @var app\models\PaginaSeccion|null $gobierno
 * @var app\models\PaginaSeccion|null $impacto
 */

use yii\helpers\Html;

if ($gobierno === null && $impacto === null) {
    return;
}
?>
<section class="huk-nos-gob" aria-labelledby="gob-titulo">
    <div class="container">
        <?php if ($gobierno && ($gobierno->firma || $gobierno->subtitulo)): ?>
            <header class="huk-nos-cabecera es-centrada" data-reveal="zoom">
                <?php if ($gobierno->firma): ?>
                    <p class="huk-nos-etiqueta"><?= Html::encode($gobierno->firma) ?></p>
                <?php endif; ?>
                <h2 id="gob-titulo" class="huk-nos-titulo"><?= Html::encode($gobierno->subtitulo ?: $gobierno->firma) ?></h2>
            </header>
        <?php else: ?>
            <h2 id="gob-titulo" class="visually-hidden">Gobierno corporativo</h2>
        <?php endif; ?>

        <div class="huk-nos-gob-grid<?= $gobierno && $impacto ? '' : ' es-una' ?>">
            <?php if ($gobierno): ?>
                <article class="huk-nos-gob-card" data-reveal="left">
                    <h3 class="huk-nos-gob-label"><?= Html::encode($gobierno->titulo) ?></h3>
                    <?php if ($gobierno->itemsActivos): ?>
                        <ul class="huk-nos-gob-lista">
                            <?php foreach ($gobierno->itemsActivos as $n => $p): ?>
                                <li data-reveal="left" data-reveal-delay="<?= 150 + min($n, 8) * 90 ?>">
                                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i><span><?= Html::encode($p->titulo) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if ($gobierno->frase): ?>
                        <p class="huk-nos-gob-lema"><?= Html::encode($gobierno->frase) ?></p>
                    <?php endif; ?>
                </article>
            <?php endif; ?>

            <?php if ($impacto): ?>
                <article class="huk-nos-gob-card is-oscura" data-reveal="right" data-reveal-delay="120">
                    <h3 class="huk-nos-gob-label"><?= Html::encode($impacto->titulo) ?></h3>
                    <?php if ($impacto->texto): ?>
                        <p class="huk-nos-gob-grande"><?= nl2br(Html::encode($impacto->texto)) ?></p>
                    <?php endif; ?>
                    <?php if ($impacto->itemsActivos): ?>
                        <ul class="huk-nos-chips">
                            <?php foreach ($impacto->itemsActivos as $n => $ind): ?>
                                <li data-reveal="nada" data-reveal-delay="<?= 250 + min($n, 12) * 60 ?>"><?= Html::encode($ind->titulo) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if ($impacto->frase): ?>
                        <p class="huk-nos-gob-cierre"><?= Html::encode($impacto->frase) ?></p>
                    <?php endif; ?>
                </article>
            <?php endif; ?>
        </div>
    </div>
</section>
