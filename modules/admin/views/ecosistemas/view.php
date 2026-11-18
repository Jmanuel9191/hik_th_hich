<?php

/**
 * Ficha del ecosistema + historial de cambios.
 *
 * @var yii\web\View $this
 * @var app\models\Ecosistema $model
 * @var yii\data\ActiveDataProvider|null $historial
 * @var bool $puedeEditar
 * @var bool $puedeEstado
 */

use app\models\Ecosistema;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

\app\assets_b\EcosistemasAdminAsset::register($this);

$this->title = $model->nombre;
// Las fechas se guardan en hora de Bolivia: se muestran tal cual.
$fecha = fn($v) => $v ? date('d/m/Y H:i', strtotime($v)) : '—';
$gerentes = array_map(fn($u) => $u->nombrecompleto, $model->usuarios);
?>

<section class="content-header">
    <h1><i class="fa fa-th-large"></i> <?= sprintf('%02d', $model->numero) ?> · <?= Html::encode($model->nombre) ?></h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title">Datos</h3></div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped detail-view'],
                        'attributes' => [
                            ['label' => 'Estado', 'format' => 'raw', 'value' => $model->estado === '1'
                                ? Html::tag('span', 'Activo (se ve en la web)', ['class' => 'label label-success'])
                                : Html::tag('span', 'Inactivo', ['class' => 'label label-default'])],
                            'resumen',
                            ['label' => 'URL', 'value' => '/ecosistemas/' . $model->slug],
                            ['label' => 'Gerentes asignados', 'value' => $gerentes ? implode(', ', $gerentes) : 'Ninguno'],
                            ['label' => 'Fecha de registro', 'value' => $fecha($model->fecha_registro)],
                            ['label' => 'Última modificación', 'value' => $fecha($model->fecha_actualizacion)],
                            ['label' => 'Galería', 'value' => count($model->imagenes) . ' foto(s)'],
                        ],
                    ]) ?>

                    <div class="huk-eco-imgs">
                        <?php foreach (Ecosistema::IMAGENES as $campo => $etiqueta): ?>
                            <figure class="<?= $campo === 'icono' ? 'is-icono' : '' ?>">
                                <?= $model->$campo
                                    ? Html::img($model->url($model->$campo), ['alt' => $etiqueta])
                                    : Html::tag('span', 'Sin imagen', ['class' => 'huk-eco-vacio']) ?>
                                <figcaption><?= Html::encode($etiqueta) ?></figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="box-footer">
                    <?php if ($puedeEditar): ?>
                        <?= Html::a('<i class="fa fa-pencil"></i> Editar', ['update', 'id' => $model->idecosistema], ['class' => 'btn btn-info']) ?>
                        <?= Html::a('<i class="fa fa-picture-o"></i> Galería', ['galeria', 'id' => $model->idecosistema], ['class' => 'btn btn-primary']) ?>
                    <?php endif; ?>
                    <?php if ($puedeEstado): ?>
                        <?php $activo = $model->estado === '1'; ?>
                        <?= Html::a(
                            '<i class="fa ' . ($activo ? 'fa-eye-slash' : 'fa-check') . '"></i> ' . ($activo ? 'Desactivar' : 'Activar'),
                            '#',
                            [
                                'class' => 'btn ' . ($activo ? 'btn-danger' : 'btn-success'),
                                'data-eco-estado' => \yii\helpers\Url::to(['estado', 'id' => $model->idecosistema]),
                                'data-accion' => $activo ? 'desactivar' : 'activar',
                                'data-nombre' => $model->nombre,
                            ]
                        ) ?>
                    <?php endif; ?>
                    <?= Html::a('Volver', ['index'], ['class' => 'btn btn-default']) ?>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title">Descripción</h3></div>
                <div class="box-body huk-rich">
                    <?= $model->descripcion /* HTML ya limpiado con HtmlPurifier al guardar */ ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Historial</h3></div>
                <div class="box-body">
                    <?php if ($historial === null): ?>
                        <p class="text-muted">No tenés permiso para ver el historial.</p>
                    <?php else: ?>
                        <?php Pjax::begin(['id' => 'eco-historial', 'timeout' => 5000]); ?>
                        <?= GridView::widget([
                            'dataProvider' => $historial,
                            'tableOptions' => ['class' => 'table table-hover'],
                            'emptyText' => 'Todavía no hay movimientos.',
                            'summary' => '{totalCount} movimiento(s)',
                            'columns' => [
                                ['label' => 'Fecha', 'value' => fn($h) => $fecha($h->fecha), 'contentOptions' => ['class' => 'text-nowrap']],
                                ['label' => 'Acción', 'format' => 'raw', 'value' => fn($h) => Html::tag('strong', Html::encode($h->accion))],
                                ['label' => 'Detalle', 'format' => 'ntext', 'value' => fn($h) => (string) $h->descripcion],
                                ['label' => 'Hecho por', 'value' => fn($h) => $h->idusuario_actor === null
                                    ? 'Sistema'
                                    : ($h->usuarioActor->usuario ?? 'Usuario #' . $h->idusuario_actor . ' (eliminado)')],
                                ['label' => 'IP', 'value' => fn($h) => $h->ip ?: '—'],
                            ],
                        ]) ?>
                        <?php Pjax::end(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
