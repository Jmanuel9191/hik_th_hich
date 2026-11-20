<?php

/**
 * Nosotros · Mensaje del CEO (Panel → Páginas → Nosotros → Mensaje del CEO): foto, frase, mensaje completo y firma.
 *
 * @var yii\web\View $this
 * @var app\models\PaginaSeccion|null $ceo
 */

use app\models\PaginaSeccion;
use yii\helpers\Html;

if ($ceo === null) {
    return;
}
$texto = $ceo->textoHtml();
?>
<section class="huk-nos-ceo" aria-labelledby="ceo-titulo">
    <div class="container huk-nos-ceo-grid<?= $ceo->imagen ? '' : ' sin-foto' ?>">
        <?php if ($ceo->imagen): ?>
            <figure class="huk-nos-ceo-foto" data-reveal="foto">
                <?= Html::img(PaginaSeccion::url($ceo->imagen), ['alt' => $ceo->firma ?: '', 'loading' => 'lazy']) ?>
            </figure>
        <?php endif; ?>
        <div class="huk-nos-ceo-txt" data-reveal="right">
            <p id="ceo-titulo" class="huk-nos-etiqueta"><?= Html::encode($ceo->titulo) ?></p>
            <?php if ($ceo->frase): ?>
                <blockquote class="huk-nos-ceo-frase">“<?= Html::encode(trim($ceo->frase, " \"“”")) ?>”</blockquote>
            <?php endif; ?>
            <?php if ($ceo->subtitulo): ?>
                <p class="huk-nos-ceo-origen"><?= Html::encode($ceo->subtitulo) ?></p>
            <?php endif; ?>
            <?php if ($texto !== ''): ?>
                <div class="huk-nos-texto"><?= $texto /* HTML limpio (HtmlEditor) */ ?></div>
            <?php endif; ?>
            <?php if ($ceo->firma): ?>
                <p class="huk-nos-ceo-firma">
                    <b><?= Html::encode($ceo->firma) ?></b>
                    <?php if ($ceo->firma_detalle): ?><span><?= Html::encode($ceo->firma_detalle) ?></span><?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>
