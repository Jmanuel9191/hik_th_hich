<?php

/**
 * Cifras (.pstats / .kv): número grande dorado y su texto. Las marcadas "contar" suben desde 0 (huk.js 2b).
 * @var app\models\bloques\BloqueCifras $b
 */

use yii\helpers\Html;
?>
<dl class="huk-pro-kv is-<?= Html::encode($b->estilo) ?> is-n<?= min(4, count($b->cifras)) ?>">
    <?php foreach ($b->cifras as $i => $c): ?>
        <div style="--i:<?= $i ?>">
            <dt><?= Html::encode($c['texto']) ?></dt>
            <dd<?= $c['contar'] ? ' data-count-texto' : '' ?>><?= Html::encode($c['valor']) ?></dd>
        </div>
    <?php endforeach; ?>
</dl>
