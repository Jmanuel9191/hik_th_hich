<?php

/**
 * Bloque Lista de fuentes (.srcs).
 * @var app\models\bloques\BloqueFuentes $b
 */

use yii\helpers\Html;

?>
<?php if ($b->titulo !== ''): ?>
    <p class="huk-pro-kicker"><?= Html::encode($b->titulo) ?></p>
<?php endif; ?>
<ol class="huk-pro-fuentes">
    <?php foreach ($b->fuentes as $f): ?>
        <li>
            <?= nl2br(Html::encode($f['texto'])) ?>
            <?php if ($f['enlace'] !== ''): ?>
                <?= Html::a('<i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i><span class="visually-hidden">Abrir</span>', $f['enlace'],
                    preg_match('~^https?://~', $f['enlace']) ? ['target' => '_blank', 'rel' => 'noopener noreferrer'] : []) ?>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ol>
