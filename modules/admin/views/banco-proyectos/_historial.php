<?php

/**
 * Pestaña Historial: todo lo que se hizo en el proyecto.
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $historial
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
?>

<div class="box box-primary">
    <div class="box-body">
        <?php Pjax::begin(['id' => 'proyecto-historial', 'timeout' => 5000]); ?>
        <?= GridView::widget([
            'dataProvider' => $historial,
            'tableOptions' => ['class' => 'table table-hover'],
            'emptyText' => 'Todavía no hay movimientos.',
            'summary' => '{totalCount} movimiento(s)',
            'columns' => [
                ['label' => 'Fecha', 'value' => fn($h) => date('d/m/Y H:i', strtotime($h->fecha)), 'contentOptions' => ['class' => 'text-nowrap']],
                ['label' => 'Acción', 'format' => 'raw', 'value' => fn($h) => Html::tag('strong', Html::encode($h->accion))],
                ['label' => 'Detalle', 'format' => 'ntext', 'value' => fn($h) => (string) $h->descripcion],
                ['label' => 'Hecho por', 'value' => fn($h) => $h->usuarioActor->usuario ?? ($h->idusuario_actor ? 'Usuario #' . $h->idusuario_actor : 'Sistema')],
                ['label' => 'IP', 'value' => fn($h) => $h->ip ?: '—'],
            ],
        ]) ?>
        <?php Pjax::end(); ?>
    </div>
</div>
