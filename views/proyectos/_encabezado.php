<?php

/**
 * Encabezado de una sección: "01 · Síntesis ejecutiva" en dorado y el título con su raya dorada.
 *
 * @var yii\web\View $this
 * @var app\models\ProyectoSeccion $seccion
 */

use yii\helpers\Html;

$ante = $seccion->antetituloWeb();
?>
<header class="huk-pro-head" data-reveal="left">
    <?php if ($ante !== null): ?>
        <p class="huk-pro-ante"><?= Html::encode($ante) ?></p>
    <?php endif; ?>
    <?php if ($seccion->titulo !== null): ?>
        <h2 class="huk-pro-h2"><?= Html::encode($seccion->titulo) ?></h2>
    <?php endif; ?>
</header>
