<?php

/**
 * Ficha de una noticia: acciones, vista previa de la tarjeta, datos, texto, galería e historial.
 *
 * @var yii\web\View $this
 * @var app\models\Noticia $model
 * @var yii\data\ActiveDataProvider|null $historial
 * @var array $puede
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

\app\assets_b\NoticiasAdminAsset::register($this);

$this->title = $model->titulo;
$fecha = fn($v, $hora = true) => $v ? date($hora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($v)) : '—';
$publica = $model->estado === '1';
?>

<section class="content-header">
    <h1><i class="fa fa-newspaper-o"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
    <div class="huk-barra-acciones">
        <?= Html::a('<i class="fa fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-default']) ?>
        <?php if ($puede['editar']): ?>
            <?= Html::a('<i class="fa fa-pencil"></i> Editar', ['update', 'id' => $model->idnoticia], ['class' => 'btn btn-info']) ?>
        <?php endif; ?>
        <?php if ($puede['galeria']): ?>
            <?= Html::a('<i class="fa fa-picture-o"></i> Galería (' . count($model->imagenes) . ')', ['galeria', 'id' => $model->idnoticia], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
        <?php if ($puede['estado']): ?>
            <?= Html::a('<i class="fa ' . ($publica ? 'fa-eye-slash' : 'fa-check') . '"></i> ' . ($publica ? 'Ocultar' : 'Publicar'), '#', [
                'class' => 'btn ' . ($publica ? 'btn-warning' : 'btn-success'),
                'data-listado-estado' => Url::to(['estado', 'id' => $model->idnoticia]),
                'data-accion' => $publica ? 'ocultar' : 'publicar',
                'data-nombre' => $model->titulo,
            ]) ?>
        <?php endif; ?>
        <?php if ($publica): ?>
            <?= Html::a('<i class="fa fa-external-link"></i> Ver en la web', ['/noticias/ver', 'slug' => $model->slug], ['class' => 'btn btn-default', 'target' => '_blank']) ?>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-5">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-th-large"></i> Así se ve la tarjeta</h3></div>
                <div class="box-body">
                    <div class="huk-noticia-vista">
                        <div class="huk-noticia-vista-img">
                            <?= Html::img($model->url($model->imagen_portada), ['alt' => '']) ?>
                            <span class="huk-noticia-vista-fecha"><b><?= date('d', strtotime($model->fecha)) ?></b><?= Yii::$app->formatter->asDate($model->fecha, 'MMM') ?></span>
                        </div>
                        <div class="huk-noticia-vista-txt">
                            <span class="huk-noticia-vista-cat"><?= Html::encode($model->categoria->nombre ?? '') ?></span>
                            <h3><?= Html::encode($model->titulo) ?></h3>
                            <p><?= Html::encode($model->resumen) ?></p>
                            <span class="huk-vista-boton">Leer más <i class="fa fa-angle-right"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-info-circle"></i> Datos</h3></div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped detail-view'],
                        'attributes' => [
                            ['label' => 'Estado', 'format' => 'raw', 'value' => $publica
                                ? Html::tag('span', 'Publicada', ['class' => 'label label-success'])
                                : Html::tag('span', 'Oculta (no sale en la web)', ['class' => 'label label-default'])],
                            ['attribute' => 'destacado', 'format' => 'raw', 'value' => $model->destacado === '1' ? '<i class="fa fa-star huk-estrella is-activa"></i> Sí (carrusel del Inicio)' : 'No'],
                            ['attribute' => 'idcategoria', 'value' => $model->categoria->nombre ?? '—'],
                            ['attribute' => 'fecha', 'value' => $fecha($model->fecha, false)],
                            ['attribute' => 'slug', 'format' => 'raw', 'value' => Html::tag('code', '/noticias/' . Html::encode($model->slug))],
                            ['attribute' => 'fecha_registro', 'value' => $fecha($model->fecha_registro)],
                            ['attribute' => 'fecha_actualizacion', 'value' => $fecha($model->fecha_actualizacion)],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-md-7">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text-o"></i> Contenido</h3></div>
                <div class="box-body huk-rich">
                    <?php if ($model->imagen_contenido): ?>
                        <p><?= Html::img($model->url($model->imagen_contenido), ['alt' => 'Imagen del contenido', 'class' => 'img-responsive huk-noticia-contenido-img']) ?></p>
                    <?php endif; ?>
                    <?= $model->descripcion /* HTML limpio (HtmlEditor) */ ?>
                </div>
            </div>

            <?php if ($model->imagenes): ?>
                <div class="box box-primary">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-picture-o"></i> Galería (<?= count($model->imagenes) ?>)</h3></div>
                    <div class="box-body huk-noticia-galeria">
                        <?php foreach ($model->imagenes as $img): ?>
                            <?= Html::img($model->url('galeria/' . $img->archivo), ['alt' => '', 'loading' => 'lazy', 'title' => $img->nombre_original]) ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Historial</h3></div>
                <div class="box-body">
                    <?php if ($historial === null): ?>
                        <p class="text-muted">No tiene permiso para ver el historial.</p>
                    <?php else: ?>
                        <?php Pjax::begin(['id' => 'noticia-historial', 'timeout' => 5000]); ?>
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
