<?php

/**
 * Nosotros → Gobierno y socios · ¿Por qué asociarse? (Panel → Páginas → Gobierno y socios → Propuesta para socios → ¿Por qué asociarse?).
 * Título centrado y las razones en tarjetas grises de 2 en 2 (1 en celular), numeradas 01, 02… en dorado.
 * Las tarjetas de una fila miden lo mismo. Efectos: entran de la izquierda y de la derecha; el número aparece "de la nada".
 * Hover: la tarjeta sube con sombra, se dibuja la línea dorada de arriba y el número se corre.
 *
 * @var yii\web\View $this
 * @var app\models\PaginaSeccion|null $razones
 */

use yii\helpers\Html;

if ($razones === null || !$razones->itemsActivos) {
    return;
}
?>
<section class="huk-nos-razones" aria-labelledby="razones-titulo">
    <div class="container">
        <header class="huk-nos-cabecera es-centrada" data-reveal="zoom">
            <h2 id="razones-titulo" class="huk-nos-titulo"><?= Html::encode($razones->titulo) ?></h2>
        </header>
        <div class="huk-nos-razones-grid">
            <?php foreach ($razones->itemsActivos as $n => $r): ?>
                <article class="huk-nos-razon" data-reveal="<?= $n % 2 ? 'right' : 'left' ?>" data-reveal-delay="<?= ($n % 2) * 130 ?>">
                    <span class="huk-nos-razon-num" data-reveal="nada" data-reveal-delay="<?= 200 + ($n % 2) * 130 ?>"><?= sprintf('%02d', $n + 1) ?></span>
                    <p><?= Html::encode($r->titulo) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
