<?php

/**
 * Tarjetas con foto (.geo3 / .las): la foto hace zoom al pasar el mouse y la tarjeta sube.
 * @var app\models\bloques\BloqueTarjetasFoto $b
 * @var app\components\MediosProyecto $medios
 */

use yii\helpers\Html;

$n = (int) $b->por_fila;
$sizes = '(max-width: 767.98px) 100vw, (max-width: 1199.98px) 50vw, ' . round(1140 / $n) . 'px';
?>
<div class="huk-pro-tarjetas-foto is-<?= $n ?>">
    <?php foreach ($b->tarjetas as $i => $t): ?>
        <article class="huk-pro-tfoto" style="--i:<?= $i ?>">
            <?php if ($t['imagen'] !== ''): ?>
                <div class="huk-pro-tfoto-img"><?= $medios->img('bloques', $t['imagen'], $t['alt'] ?: $t['titulo'], $sizes) ?></div>
            <?php endif; ?>
            <div class="huk-pro-tfoto-txt">
                <h3><?php if ($t['numero'] !== ''): ?><span class="huk-pro-tfoto-num"><?= Html::encode($t['numero']) ?></span><?php endif; ?><?= Html::encode($t['titulo']) ?></h3>
                <?php if ($t['texto'] !== ''): ?>
                    <p><?= nl2br(Html::encode($t['texto'])) ?></p>
                <?php endif; ?>
            </div>
            <?php if ($t['pie'] !== ''): ?>
                <p class="huk-pro-tfoto-pie"><?= Html::encode($t['pie']) ?></p>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>
