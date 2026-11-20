<?php

/**
 * Bloque Aviso (.notice): título en mayúsculas y el texto, sobre gris con borde dorado.
 * @var app\models\bloques\BloqueAviso $b
 */

use yii\helpers\Html;

?>
<div class="huk-pro-aviso" role="note">
    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
    <div>
        <?php if ($b->titulo !== ''): ?>
            <p class="huk-pro-aviso-titulo"><?= Html::encode($b->titulo) ?></p>
        <?php endif; ?>
        <p><?= nl2br(Html::encode($b->texto)) ?></p>
    </div>
</div>
