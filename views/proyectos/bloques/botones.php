<?php

/**
 * Bloque Botones (.btn): uno o dos. Los enlaces a otra web abren en otra pestaña.
 * @var app\models\bloques\BloqueBotones $b
 */

use yii\helpers\Html;

?>
<div class="huk-pro-botones">
    <?php foreach ($b->botones as $boton): ?>
        <?php $externo = (bool) preg_match('~^https?://~', $boton['enlace']); ?>
        <?= Html::a(Html::encode($boton['texto']) . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>', $boton['enlace'], array_filter([
            'class' => 'huk-pro-btn is-' . $boton['estilo'],
            'target' => $externo ? '_blank' : null,
            'rel' => $externo ? 'noopener noreferrer' : null,
        ])) ?>
    <?php endforeach; ?>
</div>
