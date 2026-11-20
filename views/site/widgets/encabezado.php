<?php

/**
 * Encabezado de las páginas internas (Nosotros, Ecosistemas, Proyectos…): breadcrumb y, abajo, el título.
 * Imagen de fondo: Panel → Banners → pestaña de la página (el primero vigente). Sin banner: fondo oscuro HUK.
 * Título: el del banner; si está vacío, el nombre de la página.
 * Usa también la clase huk-banner: se mete debajo del menú dorado como el banner del Inicio y las líneas
 * de fondo (huk.js punto 13) empiezan debajo. Estilos: huk.css sección 21.
 *
 * @var yii\web\View $this
 * @var app\models\Banners|null $banner
 * @var string $titulo  nombre de la página (si el banner no tiene título)
 * @var bool   $tituloDelBanner  false = siempre $titulo (ej. una noticia: su título, no el del banner)
 * @var string|null $etiqueta  cuadrito arriba del título (ej. la categoría de la noticia)
 * @var string|null $antetitulo línea dorada arriba del título (el antetítulo del banner, si la página lo usa; si no, este)
 * @var string|null $bajada     texto debajo del título (el texto del banner, si la página lo usa; si no, este)
 * @var array  $migas   [['label' => 'Inicio', 'url' => ['/site/index']], …, ['label' => 'Nosotros']] (el último es la página actual)
 */

use app\models\Banners;
use yii\helpers\Html;

$texto = ($tituloDelBanner ?? true) && $banner && $banner->titulo ? $banner->titulo : $titulo;
$ante = $banner && $banner->antetitulo ? $banner->antetitulo : ($antetitulo ?? null);
$bajada = $banner && $banner->texto ? $banner->texto : ($bajada ?? null);
$ultima = count($migas) - 1;
?>
<section class="huk-banner huk-encabezado<?= $banner ? '' : ' sin-imagen' ?>" aria-labelledby="encabezado-titulo">
    <?php if ($banner): ?>
        <picture class="huk-encabezado-bg">
            <?php if ($banner->imagen_movil): ?>
                <source media="(max-width: 767.98px)" srcset="<?= Html::encode(Banners::url($banner->imagen_movil)) ?>">
            <?php endif; ?>
            <?= Html::img(Banners::url($banner->imagen), ['alt' => '', 'fetchpriority' => 'high']) ?>
        </picture>
    <?php endif; ?>

    <div class="container huk-encabezado-contenido">
        <nav aria-label="Usted está en">
            <ol class="huk-migas">
                <?php foreach ($migas as $i => $miga): ?>
                    <?php if ($i < $ultima && isset($miga['url'])): ?>
                        <li><?= Html::a(Html::encode($miga['label']), $miga['url']) ?></li>
                    <?php else: ?>
                        <li aria-current="page"><?= Html::encode($miga['label']) ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php if (!empty($etiqueta)): ?>
            <span class="huk-encabezado-etiqueta"><?= Html::encode($etiqueta) ?></span>
        <?php endif; ?>
        <?php if ($ante): ?>
            <p class="huk-encabezado-ante"><?= Html::encode($ante) ?></p>
        <?php endif; ?>
        <h1 id="encabezado-titulo" class="huk-encabezado-titulo"><?= Html::encode($texto) ?></h1>
        <?php if ($bajada): ?>
            <p class="huk-encabezado-bajada"><?= Html::encode($bajada) ?></p>
        <?php endif; ?>
    </div>

    <!-- Figura dorada que flota (igual que en el banner del Inicio) -->
    <svg class="huk-banner-shard" viewBox="0 0 90 120" aria-hidden="true" focusable="false">
        <path d="M40 30 90 0v46L40 76z" fill="currentColor"/>
        <path d="M0 84 34 64v30L0 114z" fill="#fff"/>
    </svg>
</section>
