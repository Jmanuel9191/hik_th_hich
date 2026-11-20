<?php

/**
 * Bloque Condiciones de inversión: los datos cargados en la ficha del proyecto (solo los que tienen valor).
 * En una plantilla no hay proyecto: se muestra un aviso.
 * @var app\models\bloques\BloqueCondiciones $b
 * @var app\models\Proyecto|null $proyecto
 * @var app\components\MediosProyecto $medios
 */

use app\models\Proyecto;
use yii\helpers\Html;

$p = $proyecto;
$datos = [];
if ($p !== null) {
    $fecha = static fn($f) => $f ? Yii::$app->formatter->asDate($f, 'php:d/m/Y') : null;
    $etiqueta = static fn($campo) => preg_replace('/\s*\([^)]*\)$/', '', $p->getAttributeLabel($campo)); // sin "(opcional)", "(meses)"…
    $datos = array_filter([
        $etiqueta('tipo_participacion') => Proyecto::PARTICIPACIONES[$p->tipo_participacion] ?? null,
        $etiqueta('monto_objetivo') => $p->monto('monto_objetivo'),
        $etiqueta('monto_total') => $p->aplica('monto_total') ? $p->monto('monto_total') : null,
        $etiqueta('inversion_minima') => $p->monto('inversion_minima'),
        $etiqueta('inversion_maxima') => $p->monto('inversion_maxima'),
        $etiqueta('roi') => $p->roi !== null ? rtrim(rtrim(number_format((float) $p->roi, 2, ',', '.'), '0'), ',') . ' % anual' : null,
        $etiqueta('plazo_meses') => $p->plazo_meses ? $p->plazo_meses . ' meses' : null,
        $etiqueta('frecuencia_retorno') => $p->aplica('frecuencia_retorno') ? (Proyecto::FRECUENCIAS[$p->frecuencia_retorno] ?? null) : null,
        $etiqueta('fecha_apertura') => $fecha($p->fecha_apertura),
        $etiqueta('fecha_cierre') => $fecha($p->fecha_cierre),
    ]);
}
if (!$datos && !$medios->esPanel()) {
    return;
}
?>
<div class="huk-pro-cond">
    <?php if ($b->titulo !== ''): ?>
        <h3 class="huk-pro-h3"><?= Html::encode($b->titulo) ?></h3>
    <?php endif; ?>
    <?php if ($datos): ?>
        <dl class="huk-pro-cond-grilla">
            <?php foreach ($datos as $etiqueta => $valor): ?>
                <div>
                    <dt><?= Html::encode($etiqueta) ?></dt>
                    <dd><?= Html::encode($valor) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    <?php else: ?>
        <p class="huk-pro-nota">Vista previa: este bloque toma los montos, el plazo y el ROI de la ficha del proyecto; todavía no hay datos cargados.</p>
    <?php endif; ?>
    <?php if ($b->nota !== ''): ?>
        <p class="huk-pro-nota"><?= nl2br(Html::encode($b->nota)) ?></p>
    <?php endif; ?>
</div>
