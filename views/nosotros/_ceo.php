<?php

/**
 * Nosotros → Quiénes somos · Mensaje del CEO (Panel → Páginas → Quiénes somos → Mensaje del CEO), estilo carta del brochure:
 *  · Frase con línea dorada a la izquierda: la primera oración en negro y el resto en dorado; abajo el origen ("— FILOSOFÍA…").
 *  · Texto justificado en dos columnas (una en celular).
 *  · Al final de la segunda columna: foto, imagen de la firma, línea dorada, nombre en mayúsculas y cargo en cursiva.
 * Efectos: la frase entra de la izquierda, la carta sube y la firma aparece "de la nada".
 *
 * @var yii\web\View $this
 * @var app\models\PaginaSeccion|null $ceo
 */

use app\models\PaginaSeccion;
use yii\helpers\Html;

if ($ceo === null) {
    return;
}
$texto = $ceo->textoHtml();
$conFirma = $ceo->imagen || $ceo->imagen_firma || $ceo->firma;
// "No administramos empresas. Administramos ecosistemas…" → ['No administramos empresas.', 'Administramos ecosistemas…']
$frase = $ceo->frase ? preg_split('/(?<=[.!?])\s+/u', trim($ceo->frase, " \"“”"), 2) : [];
?>
<section class="huk-nos-ceo" aria-labelledby="ceo-titulo">
    <div class="container huk-nos-ceo-contenido">
        <h2 id="ceo-titulo" class="visually-hidden"><?= Html::encode($ceo->titulo) ?></h2>
        <?php if ($frase): ?>
            <figure class="huk-nos-ceo-cita" data-reveal="left">
                <blockquote>
                    “<?= Html::encode($frase[0]) ?><?php if (isset($frase[1])): ?><br><span><?= Html::encode($frase[1]) ?></span><?php endif; ?>”
                </blockquote>
                <?php if ($ceo->subtitulo): ?>
                    <figcaption>— <?= Html::encode($ceo->subtitulo) ?></figcaption>
                <?php endif; ?>
            </figure>
        <?php endif; ?>

        <div class="huk-nos-ceo-carta" data-reveal>
            <?= $texto /* HTML limpio (HtmlEditor) */ ?>
            <?php if ($conFirma): ?>
                <div class="huk-nos-ceo-firma" data-reveal="nada" data-reveal-delay="200">
                    <?php if ($ceo->imagen): ?>
                        <?= Html::img(PaginaSeccion::url($ceo->imagen), ['class' => 'huk-nos-ceo-foto', 'alt' => $ceo->firma ?: '', 'loading' => 'lazy']) ?>
                    <?php endif; ?>
                    <div class="huk-nos-ceo-firma-txt">
                        <?php if ($ceo->imagen_firma): ?>
                            <?= Html::img(PaginaSeccion::url($ceo->imagen_firma), ['class' => 'huk-nos-ceo-rubrica', 'alt' => 'Firma' . ($ceo->firma ? ' de ' . $ceo->firma : ''), 'loading' => 'lazy']) ?>
                        <?php endif; ?>
                        <?php if ($ceo->firma): ?>
                            <b><?= Html::encode($ceo->firma) ?></b>
                        <?php endif; ?>
                        <?php if ($ceo->firma_detalle): ?>
                            <span><?= Html::encode($ceo->firma_detalle) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
