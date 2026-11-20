<?php

/**
 * Bloque Recuadro negro (.thesis): antetítulo dorado y la frase en grande.
 * @var app\models\bloques\BloqueRecuadro $b
 */

use yii\helpers\Html;

?>
<div class="huk-pro-tesis">
    <?php if ($b->antetitulo !== ''): ?>
        <p class="huk-pro-kicker"><?= Html::encode($b->antetitulo) ?></p>
    <?php endif; ?>
    <p class="huk-pro-tesis-texto"><?= nl2br(Html::encode($b->texto)) ?></p>
</div>
