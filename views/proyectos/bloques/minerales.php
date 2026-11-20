<?php

/**
 * Minerales (.minerals / .mineral): foto cuadrada, número atómico, símbolo dorado y nombre.
 * @var app\models\bloques\BloqueMinerales $b
 * @var app\components\MediosProyecto $medios
 */

use yii\helpers\Html;
?>
<div class="huk-pro-minerales is-<?= min(4, count($b->minerales)) ?>">
    <?php foreach ($b->minerales as $i => $m): ?>
        <figure class="huk-pro-mineral" style="--i:<?= $i ?>">
            <div class="huk-pro-mineral-img"><?= $medios->img('bloques', $m['imagen'], $m['nombre'], '(max-width: 767.98px) 100vw, (max-width: 1199.98px) 50vw, 285px') ?></div>
            <div class="huk-pro-mineral-txt">
                <span class="huk-pro-mineral-sim"><?php if ($m['numero'] !== ''): ?><sup><?= Html::encode($m['numero']) ?></sup><?php endif; ?><?= Html::encode($m['simbolo']) ?></span>
                <span class="huk-pro-mineral-nom"><?= Html::encode($m['nombre']) ?></span>
            </div>
            <?php if ($m['pie'] !== ''): ?>
                <figcaption><?= Html::encode($m['pie']) ?></figcaption>
            <?php endif; ?>
        </figure>
    <?php endforeach; ?>
</div>
