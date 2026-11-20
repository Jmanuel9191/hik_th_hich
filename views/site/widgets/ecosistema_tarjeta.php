<?php

/**
 * Tarjeta de un ecosistema (reutilizable: página Ecosistemas y, más adelante, destacados del Inicio).
 * Foto de portada arriba con el número, ícono, título, subtítulo (opcional), línea dorada, resumen y "Explorar".
 * Las tarjetas de una fila miden lo mismo y "Explorar" queda abajo. Estilos: huk.css sección 26.
 * Hover: la tarjeta se pone dorada, sube, la foto se apaga, el ícono y los textos pasan a negro y la flecha se corre.
 *
 * @var yii\web\View $this
 * @var app\models\Ecosistema $ecosistema
 * @var string $efecto   variante de data-reveal ('' = desde abajo)
 * @var int    $retraso  ms
 */

use yii\helpers\Html;
use yii\helpers\Url;

$e = $ecosistema;
$url = Url::to(['/ecosistemas/ver', 'slug' => $e->slug]);
?>
<article class="huk-eco-card" data-reveal="<?= Html::encode($efecto ?? '') ?>" data-reveal-delay="<?= (int) ($retraso ?? 0) ?>">
    <a class="huk-eco-card-enlace" href="<?= $url ?>" aria-label="<?= Html::encode($e->nombre) ?>: explorar"></a>
    <div class="huk-eco-card-foto" aria-hidden="true">
        <?php if ($e->foto_portada): ?>
            <?= Html::img($e->url($e->foto_portada), ['alt' => '', 'loading' => 'lazy']) ?>
        <?php endif; ?>
        <span class="huk-eco-card-num"><?= sprintf('%02d', $e->numero) ?></span>
    </div>
    <div class="huk-eco-card-txt">
        <?php if ($e->icono): ?>
            <?= Html::img($e->url($e->icono), ['class' => 'huk-eco-card-icono', 'alt' => '', 'width' => 54, 'height' => 54, 'loading' => 'lazy']) ?>
        <?php endif; ?>
        <h3><?= Html::encode($e->nombre) ?></h3>
        <?php if ($e->subtitulo): ?>
            <p class="huk-eco-card-sub"><?= Html::encode($e->subtitulo) ?></p>
        <?php endif; ?>
        <p class="huk-eco-card-resumen"><?= Html::encode($e->resumen) ?></p>
        <span class="huk-eco-card-mas">Explorar <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
    </div>
</article>
