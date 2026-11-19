<?php

/**
 * Tabla de pagos (listado general y pestaña Inversiones del proyecto). Los anulados salen tachados con el motivo.
 * Anular: data-inversor-motivo (huk-inversores.js). Subir comprobante: data-pago-comprobante (huk-inversiones.js).
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var bool $conProyecto  muestra la columna Proyecto
 * @var bool $puedeAnular
 * @var bool $puedeRegistrar
 */

use app\models\InversionPago;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$fecha = fn($v) => date('d/m/Y', strtotime($v));
?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-striped table-hover huk-tabla-pagos'],
    'rowOptions' => fn(InversionPago $m) => $m->esAnulado() ? ['class' => 'huk-pago-anulado'] : [],
    'emptyText' => 'Todavía no hay pagos registrados.',
    'layout' => "{summary}\n<div class=\"table-responsive\">{items}</div>\n{pager}",
    'columns' => [
        [
            'label' => 'Fecha',
            'contentOptions' => ['class' => 'text-nowrap'],
            'value' => fn(InversionPago $m) => $fecha($m->fecha_pago),
        ],
        [
            'label' => 'Proyecto',
            'format' => 'raw',
            'visible' => $conProyecto,
            'value' => fn(InversionPago $m) => Html::a(Html::encode($m->proyecto->codigo), ['banco-proyectos/view', 'id' => $m->idproyecto, 'tab' => 'inversiones'], ['data-pjax' => '0'])
                . Html::tag('div', Html::encode($m->proyecto->titulo), ['class' => 'huk-eco-resumen']),
        ],
        [
            'label' => 'Inversor',
            'format' => 'raw',
            'value' => fn(InversionPago $m) => Html::a(Html::encode($m->fichaInversor ? $m->fichaInversor->nombreVisible() : $m->inversor->nombrecompleto),
                ['inversores/view', 'id' => $m->idusuario], ['data-pjax' => '0']),
        ],
        [
            'label' => 'Monto',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-nowrap'],
            'value' => fn(InversionPago $m) => Html::tag('strong', Html::encode($m->montoTexto()), ['class' => 'huk-pago-monto'])
                . ((int) $m->idmoneda !== (int) $m->idmoneda_proyecto ? Html::tag('div', '≈ ' . Html::encode($m->equivalenteTexto()), ['class' => 'huk-eco-resumen']) : ''),
        ],
        [
            'label' => 'Tipo de pago',
            'format' => 'raw',
            'value' => fn(InversionPago $m) => Html::encode($m->tipoPagoTexto())
                . ($m->referencia ? Html::tag('div', Html::encode($m->referencia), ['class' => 'huk-eco-resumen']) : '')
                . ($m->nota ? Html::tag('div', '<i class="fa fa-sticky-note-o"></i> ' . Html::encode($m->nota), ['class' => 'huk-eco-resumen']) : ''),
        ],
        [
            'label' => 'Comprobante',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-nowrap'],
            'value' => function (InversionPago $m) use ($puedeRegistrar) {
                $h = $m->comprobante_archivo
                    ? Html::a('<i class="fa fa-paperclip"></i> Ver', ['inversiones/comprobante', 'id' => $m->idpago], ['target' => '_blank', 'data-pjax' => '0', 'class' => 'btn btn-default btn-xs'])
                    : '<span class="text-muted">—</span>';
                if ($puedeRegistrar && !$m->esAnulado()) {
                    $h .= ' ' . Html::button('<i class="fa fa-upload"></i>', ['class' => 'btn btn-default btn-xs',
                        'title' => $m->comprobante_archivo ? 'Reemplazar comprobante' : 'Subir comprobante',
                        'data-pago-comprobante' => Url::to(['inversiones/comprobante-subir', 'id' => $m->idpago])]);
                }
                return $h;
            },
        ],
        [
            'label' => 'Registró',
            'format' => 'raw',
            'value' => fn(InversionPago $m) => Html::encode($m->registradoPor->nombrecompleto ?? '—')
                . Html::tag('div', date('d/m/Y H:i', strtotime($m->fecha_registro)), ['class' => 'huk-eco-resumen'])
                . ($m->esAnulado() ? Html::tag('div', '<span class="label label-danger">Anulado</span> ' . Html::encode($m->motivo_anulacion)
                    . ' · ' . Html::encode($m->anuladoPor->nombrecompleto ?? '') . ' ' . date('d/m/Y H:i', strtotime($m->fecha_anulacion)), ['class' => 'huk-pago-motivo']) : ''),
        ],
        [
            'label' => '',
            'format' => 'raw',
            'visible' => $puedeAnular,
            'value' => fn(InversionPago $m) => $m->esAnulado() ? '' : Html::button('<i class="fa fa-ban"></i> Anular', [
                'class' => 'btn btn-danger btn-xs',
                'data-inversor-motivo' => Url::to(['inversiones/anular', 'id' => $m->idpago]),
                'data-titulo' => '¿Anular el pago de ' . $m->montoTexto() . '?',
                'data-texto' => 'No se borra: queda tachado con el motivo y deja de sumar en los totales.',
                'data-boton' => 'Anular',
                'data-color' => '#a33a2c',
            ]),
        ],
    ],
]) ?>
