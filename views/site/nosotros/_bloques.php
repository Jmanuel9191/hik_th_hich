<?php

/**
 * Nosotros · bloques adicionales (Panel → Páginas → Nosotros → Quiénes somos → Bloques adicionales).
 * Título, texto con formato e imagen opcional; las filas alternan la imagen a izquierda y derecha.
 *
 * @var yii\web\View $this
 * @var app\models\PaginaItem[] $bloques
 */

use app\models\PaginaItem;
use yii\helpers\Html;

if (empty($bloques)) {
    return;
}
?>
<section class="huk-nos-bloques" aria-label="Más sobre nosotros">
    <div class="container">
        <?php foreach ($bloques as $i => $b): ?>
            <article class="huk-nos-bloque<?= $i % 2 ? ' is-invertido' : '' ?><?= $b->imagen ? '' : ' sin-imagen' ?>">
                <?php if ($b->imagen): ?>
                    <figure class="huk-nos-bloque-foto" data-reveal="foto">
                        <?= Html::img(PaginaItem::url($b->imagen), ['alt' => '', 'loading' => 'lazy']) ?>
                    </figure>
                <?php endif; ?>
                <div class="huk-nos-bloque-txt" data-reveal="<?= $i % 2 ? 'right' : 'left' ?>">
                    <h2 class="huk-nos-titulo"><?= Html::encode($b->titulo) ?></h2>
                    <?php if ($b->textoHtml() !== ''): ?>
                        <div class="huk-nos-texto"><?= $b->textoHtml() /* HTML limpio (HtmlEditor) o texto escapado */ ?></div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
