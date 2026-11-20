<?php

/**
 * Etiquetas (.tags): cápsulas que se pintan de dorado al pasar el mouse.
 * @var app\models\bloques\BloqueEtiquetas $b
 */

use app\models\bloques\Bloque;
use yii\helpers\Html;
?>
<div class="huk-pro-etiquetas-caja">
    <?php if ($b->titulo !== ''): ?>
        <p class="huk-pro-kicker"><?= Html::encode($b->titulo) ?></p>
    <?php endif; ?>
    <ul class="huk-pro-etiquetas">
        <?php foreach (Bloque::lineas($b->etiquetas) as $i => $e): ?>
            <li style="--i:<?= $i ?>"><?= Html::encode($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
