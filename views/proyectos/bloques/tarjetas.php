<?php

/**
 * Tarjetas (.cols3 / .sectors): antetítulo dorado, título y texto o lista. Las marcadas van en negro.
 * @var app\models\bloques\BloqueTarjetas $b
 */

use app\models\bloques\BloqueTarjetas;
use yii\helpers\Html;
?>
<div class="huk-pro-tarjetas is-<?= (int) $b->por_fila ?> is-<?= Html::encode($b->estilo) ?>">
    <?php foreach ($b->tarjetas as $i => $t): ?>
        <article class="huk-pro-tarjeta<?= $t['negra'] ? ' is-negra' : '' ?>" style="--i:<?= $i ?>">
            <?php if ($t['antetitulo'] !== ''): ?>
                <p class="huk-pro-tarjeta-ante"><?= Html::encode($t['antetitulo']) ?></p>
            <?php endif; ?>
            <h3><?= Html::encode($t['titulo']) ?></h3>
            <?php foreach (BloqueTarjetas::partes($t['texto']) as [$tipo, $contenido]): ?>
                <?php if ($tipo === 'ul'): ?>
                    <ul><?php foreach ($contenido as $li): ?><li><?= Html::encode($li) ?></li><?php endforeach; ?></ul>
                <?php else: ?>
                    <p><?= Html::encode($contenido) ?></p>
                <?php endif; ?>
            <?php endforeach; ?>
        </article>
    <?php endforeach; ?>
</div>
