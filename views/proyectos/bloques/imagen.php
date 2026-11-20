<?php

/**
 * Bloque Imagen con pie (figure / .mapfig). Mapa: se ve completa, con borde fino y sin recorte.
 * @var app\models\bloques\BloqueImagen $b
 * @var app\components\MediosProyecto $medios
 * @var string $sizes
 */

use yii\helpers\Html;

$clases = ['huk-pro-figura', $b->mapa ? 'is-mapa' : 'is-' . $b->formato];
?>
<figure class="<?= implode(' ', $clases) ?>">
    <div class="huk-pro-figura-img"><?= $medios->img('bloques', $b->imagen, $b->alt, $sizes) ?></div>
    <?php if ($b->pie !== ''): ?>
        <figcaption><?= nl2br(Html::encode($b->pie)) ?></figcaption>
    <?php endif; ?>
</figure>
