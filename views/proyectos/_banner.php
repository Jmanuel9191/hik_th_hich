<?php

/**
 * Banner del proyecto: foto de fondo (Imagen de banner), migas, sello "CONFIDENCIAL…", título, texto,
 * elementos (número atómico + símbolo) y hasta 4 cifras grandes que cuentan solas al aparecer.
 * Lleva .huk-banner: se mete debajo del menú dorado como el resto de las páginas.
 * Efectos: la foto hace un zoom lento; los textos suben uno detrás de otro; los elementos y las cifras aparecen de a uno.
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $proyecto
 * @var app\components\MediosProyecto $medios
 * @var array|null $interes  "Quiero invertir" (null en la vista previa del panel)
 */

use yii\helpers\Html;

$p = $proyecto;
$eco = $p->ecosistema;
$elementos = $p->listaElementos();
$cifras = $p->listaCifras();
?>
<section class="huk-banner huk-pro-hero<?= $p->imagen_banner ? '' : ' sin-imagen' ?>" aria-labelledby="pro-titulo">
    <?php if ($p->imagen_banner): ?>
        <div class="huk-encabezado-bg">
            <?= $medios->img('imagenes', $p->imagen_banner, '', '100vw', ['loading' => 'eager', 'fetchpriority' => 'high']) ?>
        </div>
    <?php endif; ?>

    <div class="container huk-pro-hero-contenido">
        <nav aria-label="Usted está en">
            <ol class="huk-migas">
                <li><?= Html::a('Inicio', ['/site/index']) ?></li>
                <li><?= Html::a('Proyectos', ['/proyectos/index']) ?></li>
                <?php if ($eco): ?>
                    <li><?= Html::a(sprintf('%02d', $eco->numero) . ' ' . Html::encode($eco->nombre), ['/proyectos/index', 'ecosistema' => $eco->slug]) ?></li>
                <?php endif; ?>
                <li aria-current="page"><?= Html::encode($p->titulo) ?></li>
            </ol>
        </nav>
        <div class="huk-pro-hero-sellos">
            <?php if ($p->sello): ?>
                <span class="huk-pro-sello"><?= Html::encode($p->sello) ?></span>
            <?php endif; ?>
            <?php if ($p->estado === 'cerrado'): ?>
                <span class="huk-pro-sello is-completo"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Inversión completa</span>
            <?php endif; ?>
        </div>
        <h1 id="pro-titulo"><?= Html::encode($p->titulo) ?></h1>
        <?php if ($p->textoBanner() !== ''): ?>
            <p class="huk-pro-hero-texto"><?= Html::encode($p->textoBanner()) ?></p>
        <?php endif; ?>
        <?php if ($elementos): ?>
            <?= $this->render('//site/widgets/proyecto_elementos', ['elementos' => $elementos, 'clase' => 'huk-pro-elementos']) ?>
        <?php endif; ?>
        <?php if (!empty($interes)): ?>
            <?= $this->render('_interes', ['proyecto' => $p, 'previo' => $interes['previo'], 'puede' => $interes['puede'], 'lugar' => 'hero']) ?>
        <?php endif; ?>
        <?php if ($cifras): ?>
            <dl class="huk-pro-cifras is-<?= count($cifras) ?>">
                <?php foreach ($cifras as $i => $c): ?>
                    <div style="--i: <?= $i ?>">
                        <dt><?= Html::encode($c['texto']) ?></dt>
                        <dd<?= !empty($c['contar']) ? ' data-count-texto' : '' ?>><?= Html::encode($c['valor']) ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>
    </div>

    <svg class="huk-banner-shard" viewBox="0 0 90 120" aria-hidden="true" focusable="false">
        <path d="M40 30 90 0v46L40 76z" fill="currentColor"/>
        <path d="M0 84 34 64v30L0 114z" fill="#fff"/>
    </svg>
</section>
