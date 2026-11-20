<?php

/**
 * Proceso (.proc): franjas de grupo arriba (negra, dorada, negra…) y los pasos numerados abajo.
 * En celular se apila: cada franja va antes de sus pasos.
 * @var app\models\bloques\BloqueProceso $b
 */

use yii\helpers\Html;

$grupos = $b->grupos();
$n = count($b->pasos);
?>
<div class="huk-pro-proceso" style="--n:<?= $n ?>">
    <?php if ($grupos): ?>
        <?php $k = 0; ?>
        <?php foreach ($grupos as $g => $grupo): ?>
            <div class="huk-pro-proceso-grupo<?= $g % 2 ? ' is-dorado' : '' ?><?= $grupo['nombre'] === '' ? ' is-vacio' : '' ?>" style="grid-column:<?= $k + 1 ?> / span <?= $grupo['pasos'] ?>;grid-row:1">
                <?= Html::encode($grupo['nombre']) ?>
            </div>
            <?php for ($j = 0; $j < $grupo['pasos']; $j++, $k++): ?>
                <?php $p = $b->pasos[$k]; ?>
                <div class="huk-pro-paso" style="--i:<?= $k ?>;grid-column:<?= $k + 1 ?>;grid-row:2">
                    <b><?= $k + 1 ?></b>
                    <h3><?= Html::encode($p['titulo']) ?></h3>
                    <?php if ($p['texto'] !== ''): ?><p><?= Html::encode($p['texto']) ?></p><?php endif; ?>
                </div>
            <?php endfor; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <?php foreach ($b->pasos as $k => $p): ?>
            <div class="huk-pro-paso" style="--i:<?= $k ?>">
                <b><?= $k + 1 ?></b>
                <h3><?= Html::encode($p['titulo']) ?></h3>
                <?php if ($p['texto'] !== ''): ?><p><?= Html::encode($p['texto']) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
