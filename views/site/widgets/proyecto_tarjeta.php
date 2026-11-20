<?php

/**
 * Tarjeta de proyecto para el listado /proyectos (vertical): foto, antetítulo, título, resumen, elementos y "Ver proyecto".
 * Toda la tarjeta es un enlace. Hover: sube, la foto hace zoom y la flecha avanza. Estilos: huk.css sección 28.
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $proyecto
 * @var string $efecto   left | right | '' (data-reveal)
 * @var int $retraso     ms
 */

use app\components\MediosProyecto;
use yii\helpers\Html;
use yii\helpers\Url;

$p = $proyecto;
$foto = $p->foto_principal ?: $p->imagen_banner;
$medios = MediosProyecto::web((int) $p->idproyecto);
$elementos = array_slice($p->listaElementos(), 0, 5);
?>
<article class="huk-proy-card" data-reveal="<?= $efecto ?>" data-reveal-delay="<?= (int) $retraso ?>">
    <a class="huk-proy-card-enlace" href="<?= Url::to(['/proyectos/ver', 'slug' => $p->slug]) ?>" aria-label="Ver proyecto: <?= Html::encode($p->titulo) ?>"></a>
    <div class="huk-proy-card-img">
        <?php if ($foto): ?>
            <?= $medios->img('imagenes', $foto, '', '(max-width: 575.98px) 100vw, (max-width: 1199.98px) 50vw, 360px') ?>
        <?php else: ?>
            <span class="huk-proy-card-sinfoto"><i class="fa-regular fa-image" aria-hidden="true"></i></span>
        <?php endif; ?>
        <span class="huk-proy-card-eco"><?= sprintf('%02d', $p->ecosistema->numero ?? 0) ?> · <?= Html::encode($p->ecosistema->nombre ?? '') ?></span>
        <?php if ($p->estado === 'cerrado'): ?>
            <span class="huk-proy-card-estado">Inversión completa</span>
        <?php endif; ?>
    </div>
    <div class="huk-proy-card-txt">
        <p class="huk-proy-card-ante"><?= Html::encode($p->antetituloTarjeta()) ?></p>
        <h3><?= Html::encode($p->titulo) ?></h3>
        <p class="huk-proy-card-resumen"><?= Html::encode($p->resumen) ?></p>
        <?php if ($elementos): ?>
            <?= $this->render('//site/widgets/proyecto_elementos', ['elementos' => $elementos, 'clase' => 'is-chicos']) ?>
        <?php endif; ?>
        <span class="huk-proy-card-mas">Ver proyecto <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
    </div>
</article>
