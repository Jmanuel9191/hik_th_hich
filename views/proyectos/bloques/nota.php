<?php

/**
 * Bloque Nota (.note): aclaración en letra chica.
 * @var app\models\bloques\BloqueNota $b
 */

use yii\helpers\Html;

?>
<p class="huk-pro-nota"><?= nl2br(Html::encode($b->texto)) ?></p>
