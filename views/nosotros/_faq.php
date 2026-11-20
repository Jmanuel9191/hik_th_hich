<?php

/**
 * Nosotros · Preguntas frecuentes (Panel → Páginas → Preguntas frecuentes): acordeón de Bootstrap 5
 * (data-bs-*, sin JS propio). La primera pregunta abierta.
 *
 * @var yii\web\View $this
 * @var app\models\PaginaSeccion|null $faq
 */

use yii\helpers\Html;

if ($faq === null || !$faq->itemsActivos) {
    return;
}
?>
<section class="huk-nos-faq" aria-labelledby="faq-titulo">
    <div class="container huk-nos-faq-grid">
        <header class="huk-nos-cabecera" data-reveal="left">
            <h2 id="faq-titulo" class="huk-nos-titulo"><?= Html::encode($faq->titulo) ?></h2>
            <?php if ($faq->subtitulo): ?>
                <p class="huk-nos-bajada"><?= Html::encode($faq->subtitulo) ?></p>
            <?php endif; ?>
        </header>
        <div class="accordion huk-faq" id="faq-nosotros" data-reveal="right">
            <?php foreach ($faq->itemsActivos as $i => $p): ?>
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button<?= $i ? ' collapsed' : '' ?>" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq-<?= (int) $p->iditem ?>" aria-expanded="<?= $i ? 'false' : 'true' ?>" aria-controls="faq-<?= (int) $p->iditem ?>">
                            <?= Html::encode($p->titulo) ?>
                        </button>
                    </h3>
                    <div id="faq-<?= (int) $p->iditem ?>" class="accordion-collapse collapse<?= $i ? '' : ' show' ?>" data-bs-parent="#faq-nosotros">
                        <div class="accordion-body"><?= $p->textoHtml() /* HTML limpio (HtmlEditor) */ ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
