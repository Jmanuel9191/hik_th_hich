<?php

/**
 * Tarjeta de una noticia del Blog (reutilizable: listado del Blog y, más adelante, carrusel del Inicio).
 * Portada con la fecha en un cuadrito (fecha de la noticia; si no la pusieron, la de creación), categoría, título,
 * resumen (máximo 3 renglones) y "Leer más". Todas las tarjetas de una fila miden lo mismo y el "Leer más" queda abajo,
 * aunque una tenga el título o el resumen más largo. Estilos: huk.css sección 25.
 * Hover: la tarjeta sube con sombra, la foto hace zoom y la flecha se corre.
 *
 * @var yii\web\View $this
 * @var app\models\Noticia $noticia
 * @var string $efecto   variante de data-reveal ('' = desde abajo)
 * @var int    $retraso  ms
 */

use yii\helpers\Html;
use yii\helpers\Url;

$meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
$f = strtotime($noticia->fecha);
$url = Url::to(['/noticias/ver', 'slug' => $noticia->slug]);
?>
<article class="huk-noticia-card" data-reveal="<?= Html::encode($efecto ?? '') ?>" data-reveal-delay="<?= (int) ($retraso ?? 0) ?>">
    <a class="huk-noticia-card-img" href="<?= $url ?>" tabindex="-1" aria-hidden="true">
        <?= Html::img($noticia->url($noticia->imagen_portada), ['alt' => '', 'loading' => 'lazy']) ?>
        <time class="huk-noticia-fecha" datetime="<?= Html::encode($noticia->fecha) ?>"><b><?= date('d', $f) ?></b><?= $meses[(int) date('n', $f) - 1] ?></time>
    </a>
    <div class="huk-noticia-card-txt">
        <?php if ($noticia->categoria): ?>
            <?= Html::a(Html::encode($noticia->categoria->nombre), ['/noticias/index', 'categoria' => $noticia->categoria->slug], ['class' => 'huk-noticia-cat']) ?>
        <?php endif; ?>
        <h3><?= Html::a(Html::encode($noticia->titulo), $url) ?></h3>
        <p><?= Html::encode($noticia->resumen) ?></p>
        <?= Html::a('Leer más <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>', $url, ['class' => 'huk-noticia-mas', 'aria-label' => 'Leer más: ' . $noticia->titulo]) ?>
    </div>
</article>
