<?php

/**
 * Nosotros → Ejes estratégicos · Modelo de creación de valor (Panel → Páginas → Ejes estratégicos → Modelo de valor):
 * pasos en círculos unidos por una línea (el último dorado), tarjetas de la propuesta de valor con borde dorado
 * a la izquierda y la fórmula en una franja negra (lo que va después del "=" en dorado).
 * Efectos: la línea se dibuja de izquierda a derecha y los círculos aparecen uno detrás de otro;
 * las tarjetas suben; la fórmula entra con zoom. Hover: el círculo se pone dorado, la tarjeta sube con sombra.
 *
 * @var yii\web\View $this
 * @var app\models\PaginaSeccion|null $modelo
 * @var app\models\PaginaSeccion|null $propuesta
 */

use yii\helpers\Html;

if ($modelo === null && ($propuesta === null || !$propuesta->itemsActivos)) {
    return;
}
$pasos = $modelo ? $modelo->itemsActivos : [];
$tarjetas = $propuesta ? $propuesta->itemsActivos : [];
// "Territorio + … = Desarrollo" → ['Territorio + …', 'Desarrollo']
$formula = $modelo && $modelo->frase ? array_map('trim', explode('=', $modelo->frase, 2)) : [];
?>
<section class="huk-nos-modelo" aria-labelledby="modelo-titulo">
    <div class="container">
        <?php if ($modelo): ?>
            <header class="huk-nos-cabecera es-centrada" data-reveal="zoom">
                <?php if ($modelo->subtitulo): ?>
                    <p class="huk-nos-etiqueta"><?= Html::encode($modelo->subtitulo) ?></p>
                <?php endif; ?>
                <h2 id="modelo-titulo" class="huk-nos-titulo"><?= Html::encode($modelo->titulo) ?></h2>
            </header>
        <?php else: ?>
            <h2 id="modelo-titulo" class="visually-hidden"><?= Html::encode($propuesta->titulo) ?></h2>
        <?php endif; ?>

        <?php if ($pasos): ?>
            <ol class="huk-nos-pasos" data-reveal style="--pasos: <?= count($pasos) ?>">
                <?php foreach ($pasos as $n => $paso): ?>
                    <li class="huk-nos-paso" style="--i: <?= $n ?>">
                        <b><?= sprintf('%02d', $n + 1) ?></b>
                        <span><?= Html::encode($paso->titulo) ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>

        <?php if ($tarjetas): ?>
            <div class="huk-nos-propuestas">
                <?php foreach ($tarjetas as $n => $t): ?>
                    <article class="huk-nos-propuesta" data-reveal="<?= ['left', '', '', 'right'][$n % 4] ?>" data-reveal-delay="<?= ($n % 4) * 120 ?>">
                        <h3><?= Html::encode($t->titulo) ?></h3>
                        <?php if ($t->texto): ?>
                            <p><?= nl2br(Html::encode($t->texto)) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($formula): ?>
            <p class="huk-nos-formula" data-reveal="zoom">
                <?= Html::encode($formula[0]) ?><?php if (isset($formula[1])): ?> = <b><?= Html::encode($formula[1]) ?></b><?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</section>
