<?php

/**
 * Ficha del proyecto: primera sección de la página y primera del menú. Sale sola de la pestaña Ficha del panel
 * (Proyecto::datosFicha): datos de la inversión en una grilla y, a la derecha, las barras de avance del proyecto
 * y de la ronda (crecen al aparecer). Con la inversión completa, sello dorado.
 *
 * @var yii\web\View $this
 * @var array $ficha ['datos' => [rótulo => valor], 'avance' => ?int, 'ronda' => ?float, 'completa' => bool]
 */

use yii\helpers\Html;

$barras = array_filter([
    'Avance del proyecto' => $ficha['avance'],
    'Ronda de inversión cubierta' => $ficha['ronda'],
], fn($v) => $v !== null);
$pct = static fn($v) => rtrim(rtrim(number_format((float) $v, 1, ',', '.'), '0'), ',') . ' %';
?>
<section id="ficha" class="huk-pro-sec huk-pro-ficha is-gris" data-pro-seccion>
    <div class="container">
        <div class="huk-pro-ficha-cab" data-reveal>
            <p class="huk-pro-ante">Ficha</p>
            <h2 class="huk-pro-ficha-titulo">El proyecto en datos</h2>
            <?php if ($ficha['completa']): ?>
                <span class="huk-pro-sello is-completo"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Inversión completa</span>
            <?php endif; ?>
        </div>
        <div class="huk-pro-ficha-cuerpo<?= $barras ? '' : ' sin-barras' ?>">
            <?php if ($ficha['datos']): ?>
                <dl class="huk-pro-ficha-datos huk-pro-b" data-reveal="left">
                    <?php foreach ($ficha['datos'] as $rotulo => $valor): ?>
                        <div>
                            <dt><?= Html::encode($rotulo) ?></dt>
                            <dd><?= Html::encode($valor) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            <?php endif; ?>
            <?php if ($barras): ?>
                <div class="huk-pro-ficha-barras huk-pro-b" data-reveal="right">
                    <?php foreach ($barras as $rotulo => $v): ?>
                        <div class="huk-pro-progreso<?= $v >= 100 ? ' is-lleno' : '' ?>" style="--p:<?= (float) $v ?>%">
                            <div class="huk-pro-progreso-cab">
                                <span><?= Html::encode($rotulo) ?></span>
                                <b data-count-texto><?= $pct($v) ?></b>
                            </div>
                            <div class="huk-pro-progreso-barra" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                 aria-valuenow="<?= (float) $v ?>" aria-label="<?= Html::encode($rotulo) ?>"><i></i></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
