<?php

/**
 * Proyectos de un ecosistema (lo dibuja app\widgets\ProyectosEcosistema, solo para el inversor con acceso):
 * uno solo → tarjeta grande horizontal; dos o más → de a 2 por vista (1 en celular). Si hay más, es carrusel que avanza
 * de a uno (solo, cada 5 s): flechas, puntitos, deslizando el dedo o con ← → del teclado (huk.js punto 12).
 * Debajo, "Ver los N proyectos" lleva al listado filtrado. Estilos: huk.css sección 28.
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto[] $proyectos
 * @var int $total
 * @var app\models\Ecosistema $ecosistema
 */

use yii\helpers\Html;

$varios = count($proyectos) > 1;
?>
<section class="huk-proy-carrusel" aria-label="Proyectos de <?= Html::encode($ecosistema->nombre) ?>">
    <div class="container">
        <div class="huk-proy-carrusel-cab" data-reveal>
            <div>
                <p class="huk-nos-etiqueta"><?= $varios ? 'Proyectos destacados' : 'Proyecto destacado' ?></p>
                <h2>Oportunidades en <?= Html::encode($ecosistema->nombre) ?></h2>
            </div>
            <?= Html::a(($total === 1 ? 'Ver el proyecto' : 'Ver los ' . $total . ' proyectos') . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>',
                ['/proyectos/index', 'ecosistema' => $ecosistema->slug], ['class' => 'huk-proy-carrusel-todos']) ?>
        </div>

        <?php if (!$varios): ?>
            <div data-reveal="zoom"><?= $this->render('proyecto_destacado', ['proyecto' => $proyectos[0]]) ?></div>
        <?php else: ?>
            <div class="huk-proy-pista" data-proy-pista tabindex="0" role="region" aria-roledescription="carrusel"
                 aria-label="Proyectos destacados: deslice o use las flechas" data-reveal="zoom">
                <?php foreach ($proyectos as $i => $p): ?>
                    <div class="huk-proy-item" aria-roledescription="diapositiva" aria-label="<?= ($i + 1) . ' de ' . count($proyectos) ?>">
                        <?= $this->render('proyecto_destacado', ['proyecto' => $p, 'vertical' => true]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="huk-proy-carrusel-control" data-proy-control hidden>
                <button type="button" class="huk-proy-carrusel-flecha" data-proy-anterior aria-label="Proyecto anterior"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i></button>
                <div class="huk-proy-carrusel-puntos" data-proy-puntos></div>
                <button type="button" class="huk-proy-carrusel-flecha" data-proy-siguiente aria-label="Proyecto siguiente"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
            </div>
        <?php endif; ?>
    </div>
</section>
