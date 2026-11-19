<?php

/**
 * Pestaña Ficha: cómo está cargado el proyecto (textos, imágenes y números).
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $model
 */

use yii\helpers\Html;
use yii\widgets\DetailView;

$vacio = '—';
$fecha = fn($v, $hora = true) => $v ? date($hora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($v)) : $vacio;
$m = $model;
?>

<div class="row">
    <div class="col-md-8">
        <?php if ($m->imagen_banner): ?>
            <div class="huk-proyecto-banner"><?= Html::img($m->urlImagen('imagen_banner'), ['alt' => '']) ?></div>
        <?php endif; ?>
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text-o"></i> Presentación</h3></div>
            <div class="box-body">
                <p class="huk-proyecto-resumen"><?= Html::encode($m->resumen) ?></p>
                <div class="huk-rich">
                    <?= $m->descripcion ?: '<p class="text-muted">Sin descripción (obligatoria para publicar).</p>' /* HTML limpio (HtmlEditor) */ ?>
                </div>
            </div>
        </div>
        <?php if ($m->riesgos || $m->garantias || $m->impacto): ?>
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-shield"></i> Riesgos, garantías e impacto</h3></div>
                <div class="box-body">
                    <?php if ($m->riesgos): ?>
                        <h4 class="huk-proyecto-sub"><?= Html::encode($m->getAttributeLabel('riesgos')) ?></h4>
                        <div class="huk-rich"><?= $m->riesgos /* HTML limpio */ ?></div>
                    <?php endif; ?>
                    <?php if ($m->garantias): ?>
                        <h4 class="huk-proyecto-sub"><?= Html::encode($m->getAttributeLabel('garantias')) ?></h4>
                        <p><?= nl2br(Html::encode($m->garantias)) ?></p>
                    <?php endif; ?>
                    <?php if ($m->impacto): ?>
                        <h4 class="huk-proyecto-sub"><?= Html::encode($m->getAttributeLabel('impacto')) ?></h4>
                        <p><?= nl2br(Html::encode($m->impacto)) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-body">
                <?= $m->foto_principal
                    ? Html::img($m->urlImagen('foto_principal'), ['alt' => '', 'class' => 'huk-proyecto-foto'])
                    : Html::tag('div', '<i class="fa fa-picture-o"></i> Sin foto principal (obligatoria para publicar)', ['class' => 'huk-eco-vacio huk-proyecto-foto-vacia']) ?>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-line-chart"></i> Números</h3></div>
            <div class="box-body">
                <?= DetailView::widget([
                    'model' => $m,
                    'options' => ['class' => 'table table-striped detail-view huk-proyecto-numeros'],
                    'attributes' => array_values(array_filter([
                        ['attribute' => 'idmoneda', 'value' => $m->moneda ? $m->moneda->codigo . ' · ' . $m->moneda->nombre : $vacio],
                        $m->aplica('monto_total') ? ['attribute' => 'monto_total', 'value' => $m->monto('monto_total') ?? $vacio] : null,
                        ['attribute' => 'monto_objetivo', 'format' => 'raw', 'value' => $m->monto('monto_objetivo') ? '<strong>' . Html::encode($m->monto('monto_objetivo')) . '</strong>' : $vacio],
                        ['attribute' => 'inversion_minima', 'value' => $m->monto('inversion_minima') ?? $vacio],
                        ['attribute' => 'inversion_maxima', 'value' => $m->monto('inversion_maxima') ?? $vacio],
                        ['attribute' => 'roi', 'value' => $m->roi !== null ? rtrim(rtrim(number_format((float) $m->roi, 2, ',', '.'), '0'), ',') . ' %' : $vacio],
                        ['attribute' => 'plazo_meses', 'value' => $m->plazo_meses ? $m->plazo_meses . ' meses' : $vacio],
                        $m->aplica('frecuencia_retorno') ? ['attribute' => 'frecuencia_retorno', 'value' => \app\models\Proyecto::FRECUENCIAS[$m->frecuencia_retorno] ?? $vacio] : null,
                        ['attribute' => 'tipo_participacion', 'value' => \app\models\Proyecto::PARTICIPACIONES[$m->tipo_participacion] ?? $vacio],
                        ['label' => 'Recibe inversiones', 'value' => $m->fecha_apertura || $m->fecha_cierre ? $fecha($m->fecha_apertura, false) . ' – ' . $fecha($m->fecha_cierre, false) : $vacio],
                    ])),
                ]) ?>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-info-circle"></i> Datos</h3></div>
            <div class="box-body">
                <?= DetailView::widget([
                    'model' => $m,
                    'options' => ['class' => 'table table-striped detail-view'],
                    'attributes' => array_values(array_filter([
                        ['attribute' => 'idgerente', 'value' => $m->gerente->nombrecompleto ?? 'Sin asignar'],
                        ['label' => 'Ubicación', 'value' => implode(', ', array_filter([$m->ubicacion, $m->ciudad, $m->pais])) ?: $vacio],
                        $m->tipo === 'inversion'
                            ? ['attribute' => 'etapas_interes', 'value' => implode(', ', $m->etapasInteresNombres()) ?: $vacio]
                            : ['attribute' => 'etapa', 'value' => $m->etapaNombre() ?? $vacio],
                        $m->tipo === 'inversion' ? null : ['attribute' => 'porcentaje_avance', 'format' => 'raw', 'value' => $m->porcentaje_avance === null ? $vacio
                            : '<div class="progress huk-progreso-avance huk-progreso-mini"><div class="progress-bar" data-ancho="' . (int) $m->porcentaje_avance . '">'
                                . (int) $m->porcentaje_avance . ' %</div></div>'],
                        ['attribute' => 'fecha_registro', 'value' => $fecha($m->fecha_registro)],
                        ['attribute' => 'fecha_publicacion', 'value' => $fecha($m->fecha_publicacion)],
                        ['attribute' => 'fecha_actualizacion', 'value' => $fecha($m->fecha_actualizacion)],
                    ])),
                ]) ?>
            </div>
        </div>
    </div>
</div>
