<?php

/**
 * Banda de hitos (.milestones): franja negra con antetítulo, texto y etiquetas; a la derecha las opciones
 * (la primera en dorado: CONTINUAR · REDISEÑAR · DETENER).
 * @var app\models\bloques\BloqueHitos $b
 */

use app\models\bloques\Bloque;
use yii\helpers\Html;

$opciones = Bloque::lineas($b->opciones);
?>
<div class="huk-pro-hitos<?= $opciones ? '' : ' sin-opciones' ?>">
    <div>
        <?php if ($b->antetitulo !== ''): ?>
            <p class="huk-pro-kicker"><?= Html::encode($b->antetitulo) ?></p>
        <?php endif; ?>
        <?php if ($b->texto !== ''): ?>
            <p class="huk-pro-hitos-texto"><?= nl2br(Html::encode($b->texto)) ?></p>
        <?php endif; ?>
        <?php if ($b->etiquetas !== ''): ?>
            <ul class="huk-pro-etiquetas">
                <?php foreach (Bloque::lineas($b->etiquetas) as $e): ?><li><?= Html::encode($e) ?></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php if ($opciones): ?>
        <ul class="huk-pro-decide">
            <?php foreach ($opciones as $o): ?><li><?= Html::encode($o) ?></li><?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
