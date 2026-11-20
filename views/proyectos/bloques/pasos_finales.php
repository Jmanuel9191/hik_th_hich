<?php

/**
 * Pasos finales (.vdc): de 2 a 4 pasos grandes en fila; el último en dorado.
 * @var app\models\bloques\BloquePasosFinales $b
 */

use yii\helpers\Html;
?>
<ol class="huk-pro-finales is-<?= count($b->pasos) ?>">
    <?php foreach ($b->pasos as $i => $p): ?>
        <li style="--i:<?= $i ?>">
            <b><?= sprintf('%02d', $i + 1) ?></b>
            <h3><?= Html::encode($p['titulo']) ?></h3>
            <?php if ($p['texto'] !== ''): ?><p><?= nl2br(Html::encode($p['texto'])) ?></p><?php endif; ?>
        </li>
    <?php endforeach; ?>
</ol>
