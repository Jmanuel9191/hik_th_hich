<?php

/**
 * Tabla del historial de un usuario (se usa en Ver usuario y en Mi perfil).
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $historial
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

// Las fechas se guardan en hora de Bolivia: se muestran tal cual (sin conversión de zona).
$fecha = fn($v) => $v ? date('d/m/Y H:i', strtotime($v)) : '—';
?>
<?php Pjax::begin(['id' => 'historial-grid', 'timeout' => 5000]); ?>
<?= GridView::widget([
    'dataProvider' => $historial,
    'tableOptions' => ['class' => 'table table-hover'],
    'emptyText' => 'Todavía no hay movimientos.',
    'summary' => '{totalCount} movimiento(s)',
    'columns' => [
        ['label' => 'Fecha', 'value' => fn($h) => $fecha($h->fecha), 'contentOptions' => ['class' => 'text-nowrap']],
        ['label' => 'Acción', 'format' => 'raw', 'value' => fn($h) => Html::tag('strong', Html::encode($h->accion))],
        ['label' => 'Detalle', 'format' => 'ntext', 'value' => fn($h) => (string) $h->descripcion],
        [
            'label' => 'Hecho por',
            'value' => fn($h) => $h->idusuario_actor === null
                ? 'Sistema'
                : ($h->usuarioActor->usuario ?? 'Usuario #' . $h->idusuario_actor . ' (eliminado)'),
        ],
        ['label' => 'IP', 'value' => fn($h) => $h->ip ?: '—'],
    ],
]) ?>
<?php Pjax::end(); ?>
