<?php

/**
 * Inicio · resumen de Quiénes somos (Panel → Páginas → Nosotros → Quiénes somos, Destacados y Cifras).
 * Formato de la plantilla: palabra de fondo, etiqueta, título, texto, frase con línea dorada, destacados y
 * "Leer más" a la izquierda; a la derecha las cifras (todas en una fila, con contador) y abajo la foto con su pie. Lo que esté vacío (o el bloque oculto) no sale.
 * Efectos: el texto entra desde la izquierda; la foto aparece con zoom y hace zoom al pasar el mouse;
 *          la palabra de fondo se corre de costado con el scroll (data-parallax, huk.js punto 12).
 *
 * @var yii\web\View $this
 * @var app\models\PaginaSeccion|null $historia
 * @var app\models\PaginaItem[] $destacados
 * @var app\models\PaginaItem[] $cifras
 */

use app\models\PaginaItem;
use app\models\PaginaSeccion;
use yii\helpers\Html;

if ($historia === null) {
    return;
}
$h = $historia;
$texto = $h->textoHtml();

/** Cifra con contador: cada número del texto sube desde 0 ("10", "+1.000", "USD 5-10 mil M") — huk.js punto 2b. */
$cifra = static fn (string $valor): string => Html::tag('b', Html::encode($valor), preg_match('/\d/', $valor) ? ['data-count-texto' => ''] : []);
$conLado = $h->imagen || $cifras;
?>
<section class="huk-quienes<?= $conLado ? '' : ' sin-foto' ?>" aria-labelledby="quienes-titulo">
    <div class="container huk-quienes-grid">
        <div class="huk-quienes-txt" data-reveal="left">
            <?php if ($h->palabra_fondo): ?>
                <span class="huk-quienes-fondo" data-parallax="0.12" aria-hidden="true"><?= Html::encode($h->palabra_fondo) ?></span>
            <?php endif; ?>
            <?php if ($h->titulo): ?>
                <p class="huk-quienes-etiqueta"><?= Html::encode($h->titulo) ?></p>
            <?php endif; ?>
            <?php if ($h->subtitulo): ?>
                <h2 id="quienes-titulo" class="huk-quienes-titulo"><?= Html::encode($h->subtitulo) ?></h2>
            <?php endif; ?>
            <?php if ($texto !== ''): ?>
                <div class="huk-quienes-texto"><?= $texto /* HTML limpio (HtmlEditor) */ ?></div>
            <?php endif; ?>
            <?php if ($h->frase): ?>
                <blockquote class="huk-quienes-tesis">
                    <?php if ($h->firma): ?><span><?= Html::encode($h->firma) ?></span><?php endif; ?>
                    “<?= Html::encode(trim($h->frase, " \"“”")) ?>”
                </blockquote>
            <?php endif; ?>

            <?php if ($destacados): ?>
                <ul class="huk-quienes-destacados">
                    <?php foreach ($destacados as $d): ?>
                        <li>
                            <?php if ($d->icono): ?>
                                <?= Html::img(PaginaItem::url($d->icono), ['alt' => '', 'width' => 56, 'height' => 56, 'loading' => 'lazy']) ?>
                            <?php endif; ?>
                            <span><?= Html::encode($d->titulo) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="huk-quienes-pie">
                <?= Html::a('Leer más <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>', ['site/nosotros'], ['class' => 'btn btn-primary huk-quienes-btn']) ?>
            </div>
        </div>

        <?php if ($conLado): ?>
            <div class="huk-quienes-lado">
                <?php if ($cifras): ?>
                    <div class="huk-quienes-cifras" data-reveal data-reveal-delay="150">
                        <?php foreach ($cifras as $c): ?>
                            <div><?= $cifra((string) $c->cifra) ?><span><?= Html::encode($c->titulo) ?></span></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($h->imagen): ?>
                    <figure class="huk-quienes-foto" data-reveal="foto">
                        <div class="huk-quienes-marco">
                            <?= Html::img(PaginaSeccion::url($h->imagen), ['alt' => $h->firma_detalle ?: ($h->titulo ?: ''), 'loading' => 'lazy']) ?>
                        </div>
                        <?php if ($h->firma_detalle): ?>
                            <figcaption><?= Html::encode($h->firma_detalle) ?></figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
