<?php

/**
 * Nosotros · Propósito, Visión y Misión (Panel → Páginas → Nosotros → Propósito, Visión y Misión).
 * Tres columnas sobre fondo oscuro. La visión lleva sus metas numeradas (01, 02, 03…).
 *
 * @var yii\web\View $this
 * @var app\models\PaginaSeccion|null $proposito
 * @var app\models\PaginaSeccion|null $vision
 * @var app\models\PaginaSeccion|null $mision
 */

use yii\helpers\Html;

$columnas = array_filter(['compass' => $proposito, 'eye' => $vision, 'flag' => $mision]);
if (!$columnas) {
    return;
}
?>
<section class="huk-nos-pvm" aria-label="Propósito, visión y misión">
    <div class="container huk-nos-pvm-grid">
        <?php $i = 0; foreach ($columnas as $icono => $bloque): ?>
            <article class="huk-nos-pvm-item" data-reveal data-reveal-delay="<?= $i++ * 150 ?>">
                <span class="huk-nos-pvm-icono" aria-hidden="true"><i class="fa-solid fa-<?= $icono ?>"></i></span>
                <h2><?= Html::encode($bloque->titulo) ?></h2>
                <?php if ($bloque->textoHtml() !== ''): ?>
                    <div class="huk-nos-pvm-texto"><?= $bloque->textoHtml() /* texto escapado, un párrafo por renglón */ ?></div>
                <?php endif; ?>
                <?php if ($bloque->clave === 'vision' && $bloque->itemsActivos): ?>
                    <ol class="huk-nos-metas">
                        <?php foreach ($bloque->itemsActivos as $n => $meta): ?>
                            <li><b><?= sprintf('%02d', $n + 1) ?></b><span><?= Html::encode($meta->titulo) ?></span></li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
