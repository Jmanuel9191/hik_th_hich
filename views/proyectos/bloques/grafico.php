<?php

/**
 * Gráfico de barras (.chart / .bars). Las barras crecen cuando el bloque aparece (.is-in, huk.js 1);
 * la más alta va en dorado. Accesible: la tabla de datos va oculta para lectores de pantalla.
 * @var app\models\bloques\BloqueGrafico $b
 */

use yii\helpers\Html;

$max = $b->maximo() ?: 1;
$horizontal = $b->orientacion === 'horizontal';
?>
<figure class="huk-pro-grafico<?= $horizontal ? ' is-horizontal' : '' ?>">
    <?php if ($b->titulo !== ''): ?>
        <figcaption class="huk-pro-grafico-titulo"><?= Html::encode($b->titulo) ?></figcaption>
    <?php endif; ?>
    <div class="huk-pro-barras" style="--n:<?= count($b->barras) ?>" aria-hidden="true">
        <?php foreach ($b->barras as $i => $barra): ?>
            <?php $v = (float) $barra['valor']; ?>
            <div class="<?= $v == $max ? 'is-max' : '' ?>" style="--i:<?= $i ?>;--h:<?= round($v / $max * 100, 2) ?>%">
                <b><?= Html::encode($b->formato($v)) ?></b>
                <i></i>
                <span><?= Html::encode($barra['etiqueta']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <table class="visually-hidden">
        <caption><?= Html::encode($b->titulo ?: 'Datos del gráfico') ?></caption>
        <?php foreach ($b->barras as $barra): ?>
            <tr><th scope="row"><?= Html::encode($barra['etiqueta']) ?></th><td><?= Html::encode($b->formato((float) $barra['valor'])) ?></td></tr>
        <?php endforeach; ?>
    </table>
    <?php if ($b->total || $b->nota !== ''): ?>
        <div class="huk-pro-grafico-pie">
            <?php if ($b->total): ?>
                <p class="huk-pro-grafico-total"><b data-count-texto><?= Html::encode($b->formato($b->suma())) ?></b> <?= Html::encode($b->total_texto) ?></p>
            <?php endif; ?>
            <?php if ($b->nota !== ''): ?>
                <p class="huk-pro-nota"><?= nl2br(Html::encode($b->nota)) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</figure>
