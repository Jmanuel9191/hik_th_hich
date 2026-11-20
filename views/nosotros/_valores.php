<?php

/**
 * Nosotros → Propósito, Visión y Misión · Valores y Principios (Panel → Páginas → Propósito, Visión y Misión → Valores y Principios). Fondo oscuro, dos columnas:
 *  · Izquierda, valores: etiqueta dorada, título grande (campo "Título grande"; vacío = la etiqueta), línea dorada,
 *    bajada y grilla de cajas (número dorado + nombre). Hover: borde dorado.
 *  · Derecha, principios: "10 PRINCIPIOS ESTRATÉGICOS" (cantidad + título) y una fila por principio
 *    (número gris + título). Hover: la fila se pone dorada y se corre a la derecha.
 * Efectos: la columna de valores entra de la izquierda, las cajas con zoom; los principios entran de la derecha.
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
?>
<section class="huk-nos-valores<?= $valores && $principios ? '' : ' es-una-columna' ?>" aria-label="Valores y principios">
    <div class="container huk-nos-valores-grid">
        <?php if ($valores): ?>
            <div class="huk-nos-valores-col">
                <header class="huk-nos-cabecera" data-reveal="left">
                    <p class="huk-nos-etiqueta"><?= Html::encode($valores->titulo) ?></p>
                    <h2 class="huk-nos-titulo"><?= Html::encode($valores->frase ?: $valores->titulo) ?></h2>
                    <?php if ($valores->subtitulo): ?>
                        <p class="huk-nos-bajada"><?= Html::encode($valores->subtitulo) ?></p>
                    <?php endif; ?>
                </header>
                <?php if ($valores->itemsActivos): ?>
                    <ul class="huk-nos-valores-cajas">
                        <?php foreach ($valores->itemsActivos as $n => $v): ?>
                            <li class="huk-nos-valor" data-reveal="zoom" data-reveal-delay="<?= ($n % 6) * 90 ?>">
                                <span class="huk-nos-valor-num"><?= sprintf('%02d', $n + 1) ?></span>
                                <?php if ($v->icono): ?>
                                    <?= Html::img(PaginaItem::url($v->icono), ['class' => 'huk-nos-valor-icono', 'alt' => '', 'width' => 40, 'height' => 40, 'loading' => 'lazy']) ?>
                                <?php endif; ?>
                                <h3><?= Html::encode($v->titulo) ?></h3>
                                <?php if ($v->texto): ?>
                                    <p><?= nl2br(Html::encode($v->texto)) ?></p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($principios): ?>
            <div class="huk-nos-principios-col">
                <p class="huk-nos-principios-eyebrow" data-reveal="right">
                    <?= Html::encode(trim(($principios->itemsActivos ? count($principios->itemsActivos) . ' ' : '') . $principios->titulo)) ?>
                </p>
                <?php if ($principios->subtitulo): ?>
                    <p class="huk-nos-bajada" data-reveal="right"><?= Html::encode($principios->subtitulo) ?></p>
                <?php endif; ?>
                <?php if ($principios->itemsActivos): ?>
                    <ol class="huk-nos-principios">
                        <?php foreach ($principios->itemsActivos as $n => $p): ?>
                            <li data-reveal="right" data-reveal-delay="<?= min($n, 6) * 80 ?>">
                                <span class="huk-nos-principio-num"><?= sprintf('%02d', $n + 1) ?></span>
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
