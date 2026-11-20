<?php

/**
 * Nosotros → Propósito, Visión y Misión (Panel → Páginas → Propósito, Visión y Misión).
 * Cabecera centrada ("Nuestra razón de ser" + título) y tres tarjetas con borde dorado arriba y número grande:
 * Propósito y Misión claras, la Visión oscura y con sus metas numeradas (01, 02, 03…).
 * Efectos: la cabecera entra con zoom; las tarjetas suben una detrás de otra (izquierda, abajo, derecha) y el número
 * aparece "de la nada". Hover: la tarjeta sube con sombra, la línea dorada se engrosa y el número se corre.
 *
 * @var yii\web\View $this
 * @var string $titulo  nombre de la página (título de la cabecera)
 * @var app\models\PaginaSeccion|null $proposito
 * @var app\models\PaginaSeccion|null $vision
 * @var app\models\PaginaSeccion|null $mision
 */

use yii\helpers\Html;

$columnas = array_values(array_filter([$proposito, $vision, $mision]));
if (!$columnas) {
    return;
}
$efectos = ['left', 'up', 'right'];
?>
<section class="huk-nos-pvm" aria-labelledby="pvm-titulo">
    <div class="container">
        <header class="huk-nos-cabecera es-centrada" data-reveal="zoom">
            <p class="huk-nos-etiqueta">Nuestra razón de ser</p>
            <h2 id="pvm-titulo" class="huk-nos-titulo"><?= Html::encode($titulo) ?></h2>
        </header>
        <div class="huk-nos-pvm-grid">
            <?php foreach ($columnas as $i => $bloque): ?>
                <article class="huk-nos-pvm-item<?= $bloque->clave === 'vision' ? ' is-oscura' : '' ?>"
                         data-reveal="<?= $efectos[$i] === 'up' ? '' : $efectos[$i] ?>" data-reveal-delay="<?= $i * 150 ?>">
                    <span class="huk-nos-pvm-num" data-reveal="nada" data-reveal-delay="<?= 250 + $i * 150 ?>"><?= sprintf('%02d', $i + 1) ?></span>
                    <h3><?= Html::encode($bloque->titulo) ?></h3>
                    <?php if ($bloque->textoHtml() !== ''): ?>
                        <div class="huk-nos-pvm-texto"><?= $bloque->textoHtml() /* texto escapado, un párrafo por renglón */ ?></div>
                    <?php endif; ?>
                    <?php if ($bloque->clave === 'vision' && $bloque->itemsActivos): ?>
                        <ol class="huk-nos-metas">
                            <?php foreach ($bloque->itemsActivos as $n => $meta): ?>
                                <li><b><?= sprintf('%02d', $n + 1) ?></b><span><?= Html::encode($meta->titulo) ?></span></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
