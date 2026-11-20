<?php

/**
 * Datos clave (.facts): recuadros con rótulo dorado y valor.
 * @var app\models\bloques\BloqueDatos $b
 */

use yii\helpers\Html;
?>
<dl class="huk-pro-datos is-<?= min(4, count($b->datos)) ?>">
    <?php foreach ($b->datos as $i => $d): ?>
        <div style="--i:<?= $i ?>">
            <dt><?= Html::encode($d['rotulo']) ?></dt>
            <dd><?= Html::encode($d['valor']) ?></dd>
        </div>
    <?php endforeach; ?>
</dl>
