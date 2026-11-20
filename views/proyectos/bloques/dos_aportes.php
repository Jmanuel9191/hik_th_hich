<?php

/**
 * Dos aportes (.give): caja dorada y caja negra con viñetas.
 * @var app\models\bloques\BloqueDosAportes $b
 */

use app\models\bloques\Bloque;
use yii\helpers\Html;
?>
<div class="huk-pro-aportes">
    <?php foreach (['a' => 'is-dorado', 'b' => 'is-negro'] as $k => $clase): ?>
        <div class="huk-pro-aporte <?= $clase ?>" data-reveal="<?= $k === 'a' ? 'left' : 'right' ?>">
            <p class="huk-pro-aporte-titulo"><?= Html::encode($b->{$k . '_titulo'}) ?></p>
            <ul>
                <?php foreach (Bloque::lineas($b->{$k . '_items'}) as $li): ?>
                    <li><?= Html::encode($li) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>
