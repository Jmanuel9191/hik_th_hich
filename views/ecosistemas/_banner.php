<?php

/**
 * Banner de la página de un ecosistema: foto de banner del ecosistema (si no tiene, la de Panel → Banners → Ecosistemas),
 * breadcrumb, ícono, "Ecosistema 01 de 10", título, subtítulo, resumen y etiquetas (la primera en dorado).
 * A la derecha, el número gigante en contorno. Lleva .huk-banner: se mete debajo del menú dorado (como el resto).
 * Efectos: la foto hace un zoom lento; los textos suben uno detrás de otro; las etiquetas aparecen de a una.
 *
 * @var yii\web\View $this
 * @var app\models\Ecosistema $ecosistema
 * @var app\models\Banners|null $encabezado
 * @var int $posicion
 * @var int $total
 */

use app\models\Banners;
use yii\helpers\Html;

$e = $ecosistema;
$fondo = $e->foto_banner ? $e->url($e->foto_banner) : ($encabezado ? Banners::url($encabezado->imagen) : null);
$fondoMovil = !$e->foto_banner && $encabezado && $encabezado->imagen_movil ? Banners::url($encabezado->imagen_movil) : null;
$etiquetas = $e->listaEtiquetas();
?>
<section class="huk-banner huk-eco-hero<?= $fondo ? '' : ' sin-imagen' ?>" aria-labelledby="eco-titulo">
    <?php if ($fondo): ?>
        <picture class="huk-encabezado-bg">
            <?php if ($fondoMovil): ?>
                <source media="(max-width: 767.98px)" srcset="<?= Html::encode($fondoMovil) ?>">
            <?php endif; ?>
            <?= Html::img($fondo, ['alt' => '', 'fetchpriority' => 'high']) ?>
        </picture>
    <?php endif; ?>
    <span class="huk-eco-hero-num" aria-hidden="true"><?= sprintf('%02d', $e->numero) ?></span>

    <div class="container huk-eco-hero-contenido">
        <nav aria-label="Usted está en">
            <ol class="huk-migas">
                <li><?= Html::a('Inicio', ['/site/index']) ?></li>
                <li><?= Html::a('Ecosistemas', ['/ecosistemas/index']) ?></li>
                <li aria-current="page"><?= Html::encode($e->nombre) ?></li>
            </ol>
        </nav>
        <?php if ($e->icono): ?>
            <?= Html::img($e->url($e->icono), ['class' => 'huk-eco-hero-icono', 'alt' => '', 'width' => 72, 'height' => 72]) ?>
        <?php endif; ?>
        <p class="huk-eco-hero-kicker">Ecosistema <?= sprintf('%02d', $posicion) ?> de <?= sprintf('%02d', $total) ?></p>
        <h1 id="eco-titulo"><?= Html::encode($e->nombre) ?></h1>
        <?php if ($e->subtitulo): ?>
            <p class="huk-eco-hero-sub"><?= Html::encode($e->subtitulo) ?></p>
        <?php endif; ?>
        <?php if ($e->resumen): ?>
            <p class="huk-eco-hero-resumen"><?= Html::encode($e->resumen) ?></p>
        <?php endif; ?>
        <?php if ($etiquetas): ?>
            <ul class="huk-eco-etiquetas" aria-label="Áreas del ecosistema">
                <?php foreach ($etiquetas as $i => $etiqueta): ?>
                    <li style="--i: <?= $i ?>"><?= Html::encode($etiqueta) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <svg class="huk-banner-shard" viewBox="0 0 90 120" aria-hidden="true" focusable="false">
        <path d="M40 30 90 0v46L40 76z" fill="currentColor"/>
        <path d="M0 84 34 64v30L0 114z" fill="#fff"/>
    </svg>
</section>
