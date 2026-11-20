<?php

/**
 * Banco de proyectos. El gerente solo ve los de sus áreas (lo filtra ProyectoService).
 *
 * @var yii\web\View $this
 * @var app\models\ProyectoSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $ecosistemas id => nombre
 * @var bool $puedeCrear
 * @var bool $puedeExportar
 * @var bool $verMontos  false para el Editor de contenido (no ve datos financieros)
 */

use app\models\Proyecto;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

\app\assets_b\ProyectosAdminAsset::register($this);

$this->title = 'Proyectos';
$vacio = Html::tag('span', '—', ['class' => 'text-muted']);
?>

<section class="content-header">
    <h1><i class="fa fa-briefcase"></i> Banco de proyectos</h1>
    <p class="huk-inicio-sub">Los ven solo los inversores aprobados de cada área, después de aceptar el acuerdo de confidencialidad.</p>
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
            <?php Pjax::begin(['id' => 'proyectos-grid', 'timeout' => 5000]); ?>
            <?php if ($puedeCrear || $puedeExportar): ?>
                <div class="huk-barra-acciones">
                    <?php if ($puedeCrear): ?>
                        <?= Html::a('<i class="fa fa-plus"></i> Nuevo proyecto', ['create'], ['class' => 'btn btn-success', 'data-pjax' => '0']) ?>
                    <?php endif; ?>
                    <?php if ($puedeExportar): ?>
                        <?= $this->render('/_exportar', ['ruta' => 'exportar']) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'emptyText' => 'Todavía no hay proyectos.',
                'columns' => [
                    [
                        'label' => 'Foto',
                        'format' => 'raw',
                        'value' => fn(Proyecto $m) => $m->foto_principal
                            ? Html::img($m->urlImagen('foto_principal'), ['class' => 'huk-item-mini', 'alt' => '', 'loading' => 'lazy'])
                            : Html::tag('span', '<i class="fa fa-picture-o"></i>', ['class' => 'huk-item-mini huk-sin-foto', 'title' => 'Sin foto principal']),
                    ],
                    [
                        'attribute' => 'titulo',
                        'label' => 'Proyecto',
                        'format' => 'raw',
                        'filterInputOptions' => ['class' => 'form-control', 'placeholder' => 'Título o código…'],
                        'value' => fn(Proyecto $m) => Html::tag('span', Html::encode($m->codigo), ['class' => 'huk-proyecto-codigo'])
                            . Html::tag('strong', Html::encode($m->titulo), ['class' => 'huk-proyecto-titulo'])
                            . Html::tag('div', Html::encode(\yii\helpers\StringHelper::truncate($m->resumen, 110)), ['class' => 'huk-eco-resumen']),
                    ],
                    [
                        'attribute' => 'idecosistema',
                        'label' => 'Ecosistema',
                        'filter' => $ecosistemas,
                        'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                        'value' => fn(Proyecto $m) => $m->ecosistema->nombre ?? '—',
                    ],
                    [
                        'attribute' => 'tipo',
                        'label' => 'Tipo',
                        'format' => 'raw',
                        'filter' => Proyecto::TIPOS,
                        'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                        'value' => fn(Proyecto $m) => Html::tag('span', Html::encode(Proyecto::TIPOS[$m->tipo] ?? $m->tipo), ['class' => 'huk-proyecto-tipo-label is-' . $m->tipo]),
                    ],
                    ...($verMontos ? [[
                        'label' => 'Monto',
                        'format' => 'raw',
                        'contentOptions' => ['class' => 'text-nowrap'],
                        'value' => fn(Proyecto $m) => $m->monto('monto_objetivo')
                            ? Html::encode($m->monto('monto_objetivo')) . Html::tag('div', Html::encode($m->getAttributeLabel('monto_objetivo')), ['class' => 'huk-eco-resumen'])
                            : $vacio,
                    ]] : []),
                    [
                        'attribute' => 'estado',
                        'label' => 'Estado',
                        'format' => 'raw',
                        'filter' => array_map(fn($e) => $e[0], Proyecto::ESTADOS),
                        'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                        'value' => fn(Proyecto $m) => Html::tag('span', Html::encode($m->estadoNombre()), ['class' => 'label ' . $m->estadoClase()]),
                    ],
                    [
                        'label' => 'Últ. cambio',
                        'contentOptions' => ['class' => 'text-nowrap'],
                        'value' => fn(Proyecto $m) => date('d/m/Y H:i', strtotime($m->fecha_actualizacion ?: $m->fecha_registro)),
                    ],
                    [
                        'label' => '',
                        'format' => 'raw',
                        'value' => fn(Proyecto $m) => Html::a('<i class="fa fa-eye"></i> Ver', ['view', 'id' => $m->idproyecto], ['class' => 'btn btn-success btn-sm', 'data-pjax' => '0']),
                    ],
                ],
            ]) ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</section>
