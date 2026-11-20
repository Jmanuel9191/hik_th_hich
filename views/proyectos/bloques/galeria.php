<?php

/**
 * Bloque Galería chica (.photos3): fotos cuadradas de a 2, 3 o 4 por fila, cada una con su pie.
 * @var app\models\bloques\BloqueGaleria $b
 * @var app\components\MediosProyecto $medios
 */

use yii\helpers\Html;

$n = (int) $b->por_fila;
?>
<div class="huk-pro-fotos is-<?= $n ?>">
    <?php foreach ($b->fotos as $f): ?>
        <figure>
            <div class="huk-pro-fotos-img"><?= $medios->img('bloques', $f['imagen'], $f['alt'], '(max-width: 575.98px) 50vw, ' . round(100 / max(1, $n)) . 'vw') ?></div>
            <?php if ($f['pie'] !== ''): ?>
                <figcaption><?= Html::encode($f['pie']) ?></figcaption>
            <?php endif; ?>
        </figure>
    <?php endforeach; ?>
</div>
