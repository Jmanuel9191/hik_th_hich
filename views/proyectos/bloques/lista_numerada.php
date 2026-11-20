<?php

/**
 * Lista numerada (.work / .acts): número dorado, comienzo en negrita y texto. Al pasar el mouse el ítem se corre.
 * @var app\models\bloques\BloqueListaNumerada $b
 */

use yii\helpers\Html;

$inicio = max(0, (int) $b->inicio);
?>
<div class="huk-pro-numerada-caja">
    <?php if ($b->titulo !== ''): ?>
        <h3 class="huk-pro-h4"><?= Html::encode($b->titulo) ?></h3>
    <?php endif; ?>
    <ol class="huk-pro-numerada is-<?= Html::encode($b->estilo) ?>" start="<?= $inicio ?>">
        <?php foreach ($b->items as $i => $it): ?>
            <?php $n = $inicio + $i; ?>
            <li>
                <b class="huk-pro-numerada-n" aria-hidden="true"><?= $b->estilo === 'simple' ? sprintf('%02d', $n) : $n ?></b>
                <span><?php if ($it['destacado'] !== ''): ?><strong><?= Html::encode($it['destacado']) ?></strong> <?php endif; ?><?= nl2br(Html::encode($it['texto'])) ?></span>
            </li>
        <?php endforeach; ?>
    </ol>
</div>
