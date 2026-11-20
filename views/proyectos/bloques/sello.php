<?php

/**
 * Sello (.std): rótulo dorado grande y texto, en un recuadro de línea fina.
 * @var app\models\bloques\BloqueSello $b
 */

use yii\helpers\Html;
?>
<div class="huk-pro-sello-std">
    <b><?= Html::encode($b->rotulo) ?></b>
    <p><?= nl2br(Html::encode($b->texto)) ?></p>
</div>
