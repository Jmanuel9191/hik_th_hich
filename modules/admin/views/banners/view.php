<?php

/**
 * Detalle del banner con vista previa (como se vería en la portada) e historial.
 *
 * @var yii\web\View $this
 * @var app\models\Banners $model
 * @var yii\data\ActiveDataProvider|null $historial
 * @var array $puede
 */

use app\models\Banners;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

\app\assets_b\BannersAdminAsset::register($this);

$this->title = $model->nombre();
$situaciones = ['activo' => 'Se muestra en la web', 'programado' => 'Programado (todavía no empieza)', 'vencido' => 'Vencido (ya terminó)', 'inactivo' => 'Inactivo'];
$etiquetas = ['activo' => 'label-success', 'programado' => 'label-info', 'vencido' => 'label-warning', 'inactivo' => 'label-default'];
$fecha = fn($v, $hora = false) => $v ? date($hora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($v)) : '—';

// Vista previa: misma estructura para computadora y celular
$capa = function () use ($model) {
    $html = '';
    if ($model->titulo) {
        $html .= Html::tag('h2', Html::encode($model->titulo));
    }
    if ($model->texto) {
        $html .= Html::tag('p', Html::encode($model->texto));
    }
    if ($model->boton_texto) {
        $html .= Html::tag('span', Html::encode($model->boton_texto) . ' <i class="fa fa-angle-right"></i>', ['class' => 'huk-vista-boton']);
    }
    return $html ? Html::tag('div', $html, ['class' => 'huk-vista-capa']) : '';
};
?>

<section class="content-header">
    <h1><i class="fa fa-picture-o"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
    <div class="huk-barra-acciones">
        <?= Html::a('<i class="fa fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-default']) ?>
        <?php if ($puede['editar']): ?>
            <?= Html::a('<i class="fa fa-pencil"></i> Editar', ['update', 'id' => $model->idbanner], ['class' => 'btn btn-info']) ?>
        <?php endif; ?>
        <?php if ($puede['estado']): ?>
            <?= Html::a('<i class="fa ' . ($model->estado === '1' ? 'fa-eye-slash' : 'fa-check') . '"></i> ' . ($model->estado === '1' ? 'Desactivar' : 'Activar'), '#', [
                'class' => 'btn ' . ($model->estado === '1' ? 'btn-warning' : 'btn-success'),
                'data-listado-estado' => Url::to(['estado', 'id' => $model->idbanner]),
                'data-accion' => $model->estado === '1' ? 'desactivar' : 'activar',
                'data-nombre' => $model->nombre(),
            ]) ?>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-desktop"></i> Así se vería en la portada</h3></div>
                <div class="box-body">
                    <div class="huk-vista huk-vista-pc">
                        <?= Html::img(Banners::url($model->imagen), ['alt' => '']) ?>
                        <?= $capa() ?>
                    </div>
                    <p class="huk-ayuda">Vista aproximada: en la página real el texto usa la letra y los colores de la web.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-mobile"></i> En el celular</h3></div>
                <div class="box-body">
                    <div class="huk-vista huk-vista-movil">
                        <?= Html::img(Banners::url($model->imagen_movil ?: $model->imagen), ['alt' => '']) ?>
                        <?= $capa() ?>
                    </div>
                    <?php if (!$model->imagen_movil): ?>
                        <p class="huk-ayuda">No tiene imagen para celular: se recorta la de computadora.</p>
                    <?php endif; ?>
                </div>
            </div>
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
                        'attributes' => [
                            ['label' => 'Estado', 'format' => 'raw', 'value' => Html::tag('span', $situaciones[$model->situacion()], ['class' => 'label ' . $etiquetas[$model->situacion()]])],
                            ['attribute' => 'titulo', 'value' => $model->titulo ?: '—'],
                            ['attribute' => 'texto', 'format' => 'ntext', 'value' => $model->texto ?: '—'],
                            ['attribute' => 'boton_texto', 'value' => $model->boton_texto ?: '—'],
                            [
                                'attribute' => 'boton_url',
                                'format' => 'raw',
                                'value' => $model->boton_url
                                    ? Html::encode($model->boton_url) . ($model->enlaceExterno() ? ' <small class="text-muted">(se abre en otra pestaña)</small>' : '')
                                    : '—',
                            ],
                            ['attribute' => 'fecha_inicio', 'value' => $model->fecha_inicio ? $fecha($model->fecha_inicio) : 'Sin fecha (desde ya)'],
                            ['attribute' => 'fecha_fin', 'value' => $model->fecha_fin ? $fecha($model->fecha_fin) : 'Sin fecha (siempre)'],
                            ['attribute' => 'fecha_registro', 'value' => $fecha($model->fecha_registro, true)],
                            ['attribute' => 'fecha_actualizacion', 'value' => $fecha($model->fecha_actualizacion, true)],
                        ],
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
                        <?php Pjax::begin(['id' => 'banner-historial', 'timeout' => 5000]); ?>
                        <?= GridView::widget([
                            'dataProvider' => $historial,
                            'tableOptions' => ['class' => 'table table-hover'],
                            'emptyText' => 'Todavía no hay movimientos.',
                            'summary' => '{totalCount} movimiento(s)',
                            'columns' => [
                                ['label' => 'Fecha', 'value' => fn($h) => $fecha($h->fecha, true), 'contentOptions' => ['class' => 'text-nowrap']],
                                ['label' => 'Acción', 'format' => 'raw', 'value' => fn($h) => Html::tag('strong', Html::encode($h->accion))],
                                ['label' => 'Detalle', 'format' => 'ntext', 'value' => fn($h) => (string) $h->descripcion],
                                ['label' => 'Hecho por', 'value' => fn($h) => $h->usuarioActor->usuario ?? ($h->idusuario_actor ? 'Usuario #' . $h->idusuario_actor : 'Sistema')],
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
