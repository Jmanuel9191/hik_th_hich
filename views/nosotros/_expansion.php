<?php

/**
 * Nosotros → Ejes estratégicos · Visión de expansión (Panel → Páginas → Ejes estratégicos → Expansión 2026–2035).
 * Izquierda: la foto del bloque (o, si no hay, el mapa de Sudamérica con los países pintados) y la cifra.
 * Derecha (panel oscuro montado encima): título y la lista de países con su enfoque.
 * Al pasar el mouse (o el foco) por un país de la lista, se pone dorado y se resalta en el mapa; y al revés:
 * al pasar por el país en el mapa se marca en la lista (huk.js punto 14).
 * Debajo: las fases del Horizonte 2035 (Panel → Páginas → Ejes estratégicos → Expansión → Fases), la última en dorado.
 * Efectos: la foto aparece con zoom, el panel entra de la derecha y las fases suben una detrás de otra.
 *
 * @var yii\web\View $this
 * @var app\models\PaginaSeccion|null $expansion
 * @var app\models\PaginaSeccion|null $fases
 */

use app\models\PaginaSeccion;
use app\widgets\MapaPaises;
use yii\helpers\Html;

$listaFases = $fases ? $fases->itemsActivos : [];
if ($expansion === null && !$listaFases) {
    return;
}
$paises = $expansion ? array_values(array_filter($expansion->itemsActivos, static fn ($p) => $p->codigo)) : [];
$romanos = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
?>
<section class="huk-nos-expansion" aria-label="Expansión" data-expansion>
    <?php if ($expansion): ?>
    <div class="container huk-nos-exp">
        <div class="huk-nos-exp-foto<?= $expansion->imagen ? '' : ' es-mapa' ?>" data-reveal="foto">
            <?php if ($expansion->imagen): ?>
                <?= Html::img(PaginaSeccion::url($expansion->imagen), ['class' => 'huk-nos-exp-bg', 'alt' => '', 'loading' => 'lazy']) ?>
            <?php else: ?>
                <?= MapaPaises::widget([
                    'activos' => array_column(array_map(static fn ($p) => [$p->codigo, (string) $p->titulo], $paises), 1, 0),
                    'options' => ['class' => 'huk-mapa huk-nos-exp-mapa', 'aria-hidden' => 'true'],
                ]) ?>
            <?php endif; ?>
            <div class="huk-nos-exp-cap">
                <?php if ($expansion->firma): ?>
                    <span class="huk-nos-exp-kicker"><?= Html::encode($expansion->firma) ?></span>
                <?php endif; ?>
                <?php if ($expansion->frase): ?>
                    <b class="huk-nos-exp-cifra"><?= Html::encode($expansion->frase) ?></b>
                <?php endif; ?>
            </div>
            <span class="huk-nos-exp-flecha" aria-hidden="true"><i class="fa-solid fa-arrow-right"></i></span>
        </div>

        <div class="huk-nos-exp-lista" data-reveal="right" data-reveal-delay="150">
            <h2><?= Html::encode($expansion->titulo) ?></h2>
            <?php if ($paises): ?>
                <ul class="huk-nos-paises">
                    <?php foreach ($paises as $p): ?>
                        <li class="huk-nos-pais" tabindex="0" data-expansion-pais="<?= Html::encode($p->codigo) ?>">
                            <b><?= Html::encode(MapaPaises::nombre($p->codigo)) ?> <i class="fa-solid fa-caret-right" aria-hidden="true"></i></b>
                            <?php if ($p->titulo): ?><span><?= Html::encode($p->titulo) ?></span><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($listaFases): ?>
        <div class="container huk-nos-fases-bloque">
            <?php if ($fases->titulo): ?>
                <p class="huk-nos-fases-titulo" data-reveal="left"><?= Html::encode($fases->titulo) ?></p>
            <?php endif; ?>
            <div class="huk-nos-fases">
                <?php foreach ($listaFases as $n => $f): ?>
                    <article class="huk-nos-fase" data-reveal data-reveal-delay="<?= min($n, 5) * 130 ?>">
                        <small>Fase <?= $romanos[$n] ?? $n + 1 ?><?= $f->cifra ? ' · ' . Html::encode($f->cifra) : '' ?></small>
                        <h3><?= Html::encode($f->titulo) ?></h3>
                        <?php if ($f->texto): ?>
                            <p><?= nl2br(Html::encode($f->texto)) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
