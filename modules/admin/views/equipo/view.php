<?php

/**
 * Detalle de un miembro: vista previa de la tarjeta (como en la web), biografía, datos e historial.
 *
 * @var yii\web\View $this
 * @var app\models\Equipo $model
 * @var yii\data\ActiveDataProvider|null $historial
 * @var array $puede
 */

use app\models\Equipo;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

\app\assets_b\EquipoAdminAsset::register($this);

$this->title = $model->nombre;
$fecha = fn($v) => $v ? date('d/m/Y H:i', strtotime($v)) : '—';
?>

<section class="content-header">
    <h1><i class="fa fa-id-badge"></i> <?= Html::encode($model->nombre) ?></h1>
</section>

<section class="content">
    <div class="huk-barra-acciones">
        <?= Html::a('<i class="fa fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-default']) ?>
        <?php if ($puede['editar']): ?>
            <?= Html::a('<i class="fa fa-pencil"></i> Editar', ['update', 'id' => $model->idmiembro], ['class' => 'btn btn-info']) ?>
        <?php endif; ?>
        <?php if ($puede['estado']): ?>
            <?= Html::a('<i class="fa ' . ($model->estado === '1' ? 'fa-eye-slash' : 'fa-check') . '"></i> ' . ($model->estado === '1' ? 'Desactivar' : 'Activar'), '#', [
                'class' => 'btn ' . ($model->estado === '1' ? 'btn-warning' : 'btn-success'),
                'data-listado-estado' => Url::to(['estado', 'id' => $model->idmiembro]),
                'data-accion' => $model->estado === '1' ? 'desactivar' : 'activar',
                'data-nombre' => $model->nombre,
            ]) ?>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-eye"></i> Así se vería</h3></div>
                <div class="box-body">
                    <div class="huk-tarjeta">
                        <div class="huk-tarjeta-foto">
                            <?= $model->foto
                                ? Html::img(Equipo::url($model->foto), ['alt' => ''])
                                : Html::tag('span', '<i class="fa fa-user"></i>', ['class' => 'huk-tarjeta-sinfoto']) ?>
                            <div class="huk-tarjeta-redes">
                                <?php foreach (Equipo::REDES as $campo => [$nombre, $fa]): ?>
                                    <?php if ($model->$campo): ?>
                                        <span title="<?= Html::encode($nombre) ?>"><i class="fa fa-<?= $fa ?>"></i></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="huk-tarjeta-datos">
                            <h3><?= Html::encode($model->nombre) ?></h3>
                            <p class="huk-tarjeta-puesto"><?= Html::encode($model->puesto) ?></p>
                            <?php if ($model->telefono): ?><p><i class="fa fa-phone"></i> <?= Html::encode($model->telefono) ?></p><?php endif; ?>
                            <?php if ($model->email): ?><p><i class="fa fa-envelope"></i> <?= Html::encode($model->email) ?></p><?php endif; ?>
                            <?php if ($model->biografia): ?><span class="huk-vista-boton">Ver más <i class="fa fa-angle-right"></i></span><?php endif; ?>
                        </div>
                    </div>
                    <p class="huk-ayuda">Vista aproximada: en la web usa la letra y los colores de la página.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text-o"></i> Biografía</h3></div>
                <div class="box-body huk-rich">
                    <?php if ($model->biografia): ?>
                        <?= $model->biografia /* HTML ya limpio (HtmlEditor) */ ?>
                    <?php else: ?>
                        <p class="text-muted">Sin biografía: en la web no aparece el botón "Ver más".</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-5">
                    <div class="box box-primary">
                        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-info-circle"></i> Datos</h3></div>
                        <div class="box-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-striped detail-view'],
                                'attributes' => array_merge([
                                    ['label' => 'Estado', 'format' => 'raw', 'value' => $model->estado === '1'
                                        ? Html::tag('span', 'Activo (se muestra en la web)', ['class' => 'label label-success'])
                                        : Html::tag('span', 'Inactivo', ['class' => 'label label-default'])],
                                    ['attribute' => 'destacado', 'format' => 'raw', 'value' => $model->destacado === '1'
                                        ? '<i class="fa fa-star huk-estrella is-activa"></i> Sí (Inicio y Liderazgo)' : 'No'],
                                    'puesto',
                                    ['attribute' => 'telefono', 'value' => $model->telefono ?: '—'],
                                    ['attribute' => 'email', 'value' => $model->email ?: '—'],
                                ], array_map(fn($campo) => ['attribute' => $campo, 'value' => $model->$campo ?: '—'], array_keys(Equipo::REDES)), [
                                    ['attribute' => 'fecha_registro', 'value' => $fecha($model->fecha_registro)],
                                    ['attribute' => 'fecha_actualizacion', 'value' => $fecha($model->fecha_actualizacion)],
                                ]),
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="box box-primary">
                        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Historial</h3></div>
                        <div class="box-body">
                            <?php if ($historial === null): ?>
                                <p class="text-muted">No tenés permiso para ver el historial.</p>
                            <?php else: ?>
                                <?php Pjax::begin(['id' => 'equipo-historial', 'timeout' => 5000]); ?>
                                <?= GridView::widget([
                                    'dataProvider' => $historial,
                                    'tableOptions' => ['class' => 'table table-hover'],
                                    'emptyText' => 'Todavía no hay movimientos.',
                                    'summary' => '{totalCount} movimiento(s)',
                                    'columns' => [
                                        ['label' => 'Fecha', 'value' => fn($h) => $fecha($h->fecha), 'contentOptions' => ['class' => 'text-nowrap']],
                                        ['label' => 'Acción', 'format' => 'raw', 'value' => fn($h) => Html::tag('strong', Html::encode($h->accion))],
                                        ['label' => 'Detalle', 'format' => 'ntext', 'value' => fn($h) => (string) $h->descripcion],
                                        ['label' => 'Hecho por', 'value' => fn($h) => $h->usuarioActor->usuario ?? ($h->idusuario_actor ? 'Usuario #' . $h->idusuario_actor : 'Sistema')],
                                    ],
                                ]) ?>
                                <?php Pjax::end(); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
