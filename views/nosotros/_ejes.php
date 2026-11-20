<?php

/**
 * Nosotros → Ejes estratégicos · Ejes de crecimiento: un eje por Ecosistema destacado (Panel → Ecosistemas),
 * numerados 01, 02… en orden de número, con su nombre y su resumen. Grilla de 4 columnas con líneas finas;
 * el primero va oscuro. Textos de arriba: Panel → Páginas → Ejes estratégicos → Ejes estratégicos ({n} = cantidad de ejes).
 * Efectos: la cabecera entra con zoom; los ejes suben uno detrás de otro y el número aparece "de la nada".
 * Hover: el eje se pone oscuro (como el primero) y el número se corre.
 *
 * @var yii\web\View $this
 * @var app\models\PaginaSeccion|null $ejes
 * @var app\models\Ecosistema[] $ecosistemas
 */

use yii\helpers\Html;

if ($ejes === null || !$ecosistemas) {
    return;
}
// {n} → la cantidad en palabras ("Ocho"); de 21 en adelante, en números.
$palabras = ['cero', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve', 'diez', 'once', 'doce', 'trece',
    'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve', 'veinte'];
$cantidad = count($ecosistemas);
$etiqueta = preg_replace_callback('/\{n\}/', static function ($m) use ($palabras, $cantidad) {
    return $palabras[$cantidad] ?? (string) $cantidad;
}, (string) $ejes->titulo);
$etiqueta = mb_strtoupper(mb_substr($etiqueta, 0, 1)) . mb_substr($etiqueta, 1);
?>
<section class="huk-nos-ejes" aria-labelledby="ejes-titulo">
    <div class="container">
        <header class="huk-nos-cabecera es-centrada" data-reveal="zoom">
            <?php if ($etiqueta !== ''): ?>
                <p class="huk-nos-etiqueta"><?= Html::encode($etiqueta) ?></p>
            <?php endif; ?>
            <h2 id="ejes-titulo" class="huk-nos-titulo"><?= Html::encode($ejes->subtitulo ?: 'Ejes estratégicos') ?></h2>
        </header>
        <div class="huk-nos-ejes-grid">
            <?php foreach ($ecosistemas as $i => $e): ?>
                <article class="huk-nos-eje" data-reveal data-reveal-delay="<?= ($i % 4) * 110 ?>">
                    <span class="huk-nos-eje-num" data-reveal="nada" data-reveal-delay="<?= 200 + ($i % 4) * 110 ?>"><?= sprintf('%02d', $i + 1) ?></span>
                    <h3><?= Html::encode($e->nombre) ?></h3>
                    <?php if ($e->resumen): ?>
                        <p><?= Html::encode($e->resumen) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
