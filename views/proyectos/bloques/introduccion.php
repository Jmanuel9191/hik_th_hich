<?php

/**
 * Bloque Texto de introducción (.lead): cada línea en blanco separa un párrafo.
 * @var app\models\bloques\BloqueIntroduccion $b
 */

use yii\helpers\Html;

?>
<div class="huk-pro-lead">
    <?php foreach (preg_split('/\n\s*\n/', $b->texto) as $parrafo): ?>
        <p><?= nl2br(Html::encode(trim($parrafo))) ?></p>
    <?php endforeach; ?>
</div>
