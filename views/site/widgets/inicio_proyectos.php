<?php

/**
 * Inicio · Proyectos destacados de sus áreas (solo inversor aprobado con el acuerdo aceptado; si no, el llamado
 * a registrarse / ingresar). De a 3 (2 en tablet, 1 en celular); si hay más, carrusel que avanza solo de a uno
 * cada 5 s (huk.js punto 12).
 *
 * @var yii\web\View $this
 * @var array|null $proyectos ['proyectos' => Proyecto[], 'total' => int] (ProyectoWebService::destacadosInicio)
 */

use app\widgets\LlamadoProyectos;
use yii\helpers\Html;

if ($proyectos === null) {
    echo LlamadoProyectos::widget();
    return;
}
$lista = $proyectos['proyectos'];
$total = $proyectos['total'];
?>
<section class="huk-proy-carrusel is-inicio" aria-labelledby="inicio-proy-titulo">
    <div class="container">
        <div class="huk-proy-carrusel-cab" data-reveal>
            <div>
                <p class="huk-nos-etiqueta">Proyectos destacados</p>
                <h2 id="inicio-proy-titulo">Oportunidades en sus áreas</h2>
            </div>
            <?= Html::a(($total === 1 ? 'Ver el proyecto' : 'Ver los ' . $total . ' proyectos') . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>',
                ['/proyectos/index'], ['class' => 'huk-proy-carrusel-todos']) ?>
        </div>
        <div class="huk-proy-pista is-tres" data-proy-pista tabindex="0" role="region" aria-roledescription="carrusel"
             aria-label="Proyectos destacados: deslice o use las flechas" data-reveal="zoom">
            <?php foreach ($lista as $i => $p): ?>
                <div class="huk-proy-item" aria-roledescription="diapositiva" aria-label="<?= ($i + 1) . ' de ' . count($lista) ?>">
                    <?= $this->render('proyecto_destacado', ['proyecto' => $p, 'vertical' => true]) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="huk-proy-carrusel-control" data-proy-control hidden>
            <button type="button" class="huk-proy-carrusel-flecha" data-proy-anterior aria-label="Proyecto anterior"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i></button>
            <div class="huk-proy-carrusel-puntos" data-proy-puntos></div>
            <button type="button" class="huk-proy-carrusel-flecha" data-proy-siguiente aria-label="Proyecto siguiente"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
        </div>
    </div>
</section>
