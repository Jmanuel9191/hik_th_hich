<?php

/**
 * Chips de elementos: número atómico chico arriba y el símbolo grande (ej. 79 Au). Banner y tarjetas del proyecto.
 *
 * @var yii\web\View $this
 * @var array $elementos [['numero', 'simbolo', 'nombre']]
 * @var string $clase  clase del contenedor (estilos según dónde va)
 */

use yii\helpers\Html;
?>
<ul class="huk-elementos <?= Html::encode($clase) ?>" aria-label="Elementos">
    <?php foreach ($elementos as $i => $el): ?>
        <li style="--i: <?= $i ?>" title="<?= Html::encode($el['nombre'] ?? '') ?>">
            <?php if (($el['numero'] ?? '') !== ''): ?>
                <small><?= Html::encode($el['numero']) ?></small>
            <?php endif; ?>
            <span><?= Html::encode($el['simbolo']) ?></span>
            <?php if (($el['nombre'] ?? '') !== ''): ?>
                <span class="visually-hidden"><?= Html::encode($el['nombre']) ?></span>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
