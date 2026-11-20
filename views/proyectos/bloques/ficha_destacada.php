<?php

/**
 * Ficha destacada (.target): panel negro (título y cifras) y el texto a la derecha.
 * @var app\models\bloques\BloqueFichaDestacada $b
 */

use yii\helpers\Html;
?>
<div class="huk-pro-blanco">
    <div class="huk-pro-blanco-panel">
        <?php if ($b->antetitulo !== ''): ?>
            <p class="huk-pro-blanco-ante"><?= Html::encode($b->antetitulo) ?></p>
        <?php endif; ?>
        <h3><?= Html::encode($b->titulo) ?></h3>
        <?php if ($b->cifras): ?>
            <dl class="huk-pro-blanco-cifras">
                <?php foreach ($b->cifras as $c): ?>
                    <div><dt><?= Html::encode($c['texto']) ?></dt><dd data-count-texto><?= Html::encode($c['valor']) ?></dd></div>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>
    </div>
    <div class="huk-pro-blanco-texto">
        <?php if ($b->subtitulo !== ''): ?>
            <h4><?= Html::encode($b->subtitulo) ?></h4>
        <?php endif; ?>
        <div class="huk-pro-texto"><?= $b->texto ?></div>
    </div>
</div>
