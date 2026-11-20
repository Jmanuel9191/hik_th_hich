<?php

/**
 * Tabla (table.t): encabezado negro, filas con hover. En celular se desliza de costado.
 * Negrita y chips en las celdas: BloqueTabla::celda (escapa todo antes).
 * @var app\models\bloques\BloqueTabla $b
 */

use app\models\bloques\BloqueTabla;
use yii\helpers\Html;

$t = $b->tabla;
$conTitulos = implode('', $t['columnas']) !== '';
?>
<div class="huk-pro-tabla-caja">
    <?php if ($b->titulo !== ''): ?>
        <h3 class="huk-pro-h4"><?= Html::encode($b->titulo) ?></h3>
    <?php endif; ?>
    <div class="huk-pro-tabla-scroll" tabindex="0" role="region" aria-label="<?= Html::encode($b->titulo ?: 'Tabla') ?>">
        <table class="huk-pro-tabla">
            <?php if ($conTitulos): ?>
                <thead><tr><?php foreach ($t['columnas'] as $c): ?><th scope="col"><?= Html::encode($c) ?></th><?php endforeach; ?></tr></thead>
            <?php endif; ?>
            <tbody>
                <?php foreach ($t['filas'] as $fila): ?>
                    <tr><?php foreach ($fila as $celda): ?><td><?= BloqueTabla::celda($celda) ?></td><?php endforeach; ?></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($b->nota !== ''): ?>
        <p class="huk-pro-nota"><?= nl2br(Html::encode($b->nota)) ?></p>
    <?php endif; ?>
</div>
