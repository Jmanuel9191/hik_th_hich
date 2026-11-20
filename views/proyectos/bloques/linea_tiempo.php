<?php

/**
 * Línea de tiempo (.timeline): etapas en fila; hechas en negro, decisión en dorado, pendientes en gris.
 * Entran de a una (--i).
 * @var app\models\bloques\BloqueLineaTiempo $b
 */

use yii\helpers\Html;
?>
<ol class="huk-pro-tiempo is-<?= min(4, count($b->pasos)) ?>">
    <?php foreach ($b->pasos as $i => $p): ?>
        <li class="is-<?= Html::encode($p['estado']) ?>" style="--i:<?= $i ?>">
            <?php if ($p['rotulo'] !== ''): ?>
                <small><?= Html::encode($p['rotulo']) ?></small>
            <?php endif; ?>
            <h3><?= Html::encode($p['titulo']) ?></h3>
            <?php if ($p['texto'] !== ''): ?>
                <p><?= nl2br(Html::encode($p['texto'])) ?></p>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ol>
