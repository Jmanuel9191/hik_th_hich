<?php

/**
 * Tarjeta grande de "Proyecto destacado" (la .proj de la referencia): foto a la izquierda; a la derecha
 * antetítulo, nombre, resumen, elementos y "Ver proyecto". La usa el carrusel de la página del ecosistema.
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $proyecto
 * @var bool $vertical  foto arriba y texto abajo (en el carrusel de a 2)
 */

use app\components\MediosProyecto;
use yii\helpers\Html;
use yii\helpers\Url;

$p = $proyecto;
$vertical = $vertical ?? false;
$foto = $p->foto_principal ?: $p->imagen_banner;
$medios = MediosProyecto::web((int) $p->idproyecto);
$elementos = $p->listaElementos();
?>
<a class="huk-proy-dest<?= $vertical ? ' is-vertical' : '' ?>" href="<?= Url::to(['/proyectos/ver', 'slug' => $p->slug]) ?>">
    <div class="huk-proy-dest-img">
        <?php if ($foto): ?>
            <?= $medios->img('imagenes', $foto, '', $vertical ? '(max-width: 767.98px) 88vw, 560px' : '(max-width: 991.98px) 100vw, 570px') ?>
        <?php endif; ?>
        <?php if ($p->estado === 'cerrado'): ?>
            <span class="huk-proy-card-estado">Inversión completa</span>
        <?php endif; ?>
    </div>
    <div class="huk-proy-dest-txt">
        <p class="huk-proy-card-ante"><?= Html::encode($p->antetituloTarjeta()) ?></p>
        <h3><?= Html::encode($p->titulo) ?></h3>
        <p class="huk-proy-dest-resumen"><?= Html::encode($p->resumen) ?></p>
        <?php if ($elementos): ?>
            <?= $this->render('//site/widgets/proyecto_elementos', ['elementos' => $elementos, 'clase' => 'is-oscuros']) ?>
        <?php endif; ?>
        <span class="huk-proy-dest-btn">Ver proyecto <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
    </div>
</a>
