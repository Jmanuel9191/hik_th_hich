<?php

/**
 * Nosotros · Valores y Principios estratégicos (Panel → Páginas → Nosotros → Valores y Principios).
 * Valores: grilla con número (y el ícono si lo cargaste). Principios: lista numerada en dos columnas.
 *
 * @var yii\web\View $this
 * @var app\models\PaginaSeccion|null $valores
 * @var app\models\PaginaSeccion|null $principios
 */

use app\models\PaginaItem;
use yii\helpers\Html;

if (!$valores && !$principios) {
    return;
}
$cabecera = static function ($bloque): string {
    return Html::tag('header',
        Html::tag('h2', Html::encode($bloque->titulo), ['class' => 'huk-nos-titulo'])
        . ($bloque->subtitulo ? Html::tag('p', Html::encode($bloque->subtitulo), ['class' => 'huk-nos-bajada']) : ''),
        ['class' => 'huk-nos-cabecera', 'data-reveal' => '']);
};
?>
<section class="huk-nos-valores" aria-label="Valores y principios">
    <div class="container">
        <?php if ($valores): ?>
            <?= $cabecera($valores) ?>
            <?php if ($valores->itemsActivos): ?>
                <ul class="huk-nos-valores-grid">
                    <?php foreach ($valores->itemsActivos as $n => $v): ?>
                        <li class="huk-nos-valor" data-reveal data-reveal-delay="<?= ($n % 4) * 100 ?>">
                            <span class="huk-nos-num"><?= sprintf('%02d', $n + 1) ?></span>
                            <?php if ($v->icono): ?>
                                <?= Html::img(PaginaItem::url($v->icono), ['class' => 'huk-nos-valor-icono', 'alt' => '', 'width' => 56, 'height' => 56, 'loading' => 'lazy']) ?>
                            <?php endif; ?>
                            <h3><?= Html::encode($v->titulo) ?></h3>
                            <?php if ($v->texto): ?>
                                <p><?= nl2br(Html::encode($v->texto)) ?></p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($principios): ?>
            <div class="huk-nos-principios-bloque">
                <?= $cabecera($principios) ?>
                <?php if ($principios->itemsActivos): ?>
                    <ol class="huk-nos-principios">
                        <?php foreach ($principios->itemsActivos as $n => $p): ?>
                            <li data-reveal data-reveal-delay="<?= ($n % 2) * 120 ?>">
                                <span class="huk-nos-num"><?= sprintf('%02d', $n + 1) ?></span>
                                <div>
                                    <h3><?= Html::encode($p->titulo) ?></h3>
                                    <?php if ($p->texto): ?>
                                        <p><?= nl2br(Html::encode($p->texto)) ?></p>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
