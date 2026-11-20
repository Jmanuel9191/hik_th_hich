<?php

/**
 * Inversores por estado (pestañas). El gerente ve los relacionados con sus áreas.
 *
 * @var yii\web\View $this
 * @var app\models\InversorSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $conteos pestaña => n
 * @var array $ecosistemas id => nombre
 * @var string $tab
 * @var array $pestanas
 * @var bool $puedeCrear
 * @var bool $puedeExportar
 */

use app\models\Inversor;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

\app\assets_b\InversoresAdminAsset::register($this);

$this->title = 'Inversores';
$vigente = \app\models\NdaVersion::vigente();
?>

<section class="content-header">
    <h1><i class="fa fa-handshake-o"></i> Inversores</h1>
    <p class="huk-inicio-sub">Solicitudes de la web y del panel. Apruebe con las áreas que puede ver; al entrar acepta el acuerdo de confidencialidad.</p>
</section>

<section class="content">
    <ul class="nav nav-tabs huk-tabs">
        <?php foreach ($pestanas as $id => $nombre): ?>
            <li class="<?= $id === $tab ? 'active' : '' ?>">
                <?= Html::a(Html::encode($nombre) . ' <span class="badge">' . (int) ($conteos[$id] ?? 0) . '</span>', ['index', 'tab' => $id]) ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="box">
        <div class="box-body">
            <?php Pjax::begin(['id' => 'inversores-grid', 'timeout' => 5000]); ?>
            <?php if ($puedeCrear || $puedeExportar): ?>
                <div class="huk-barra-acciones">
                    <?php if ($puedeCrear): ?>
                        <?= Html::a('<i class="fa fa-user-plus"></i> Cargar inversor', ['create'], ['class' => 'btn btn-success', 'data-pjax' => '0']) ?>
                    <?php endif; ?>
                    <?php if ($puedeExportar): ?>
                        <?= $this->render('/_exportar', ['ruta' => 'exportar', 'extra' => ['tab' => $tab]]) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'filterUrl' => ['index', 'tab' => $tab],
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'emptyText' => 'No hay inversores en esta pestaña.',
                'columns' => [
                    [
                        'attribute' => 'texto',
                        'label' => 'Inversor',
                        'format' => 'raw',
                        'filterInputOptions' => ['class' => 'form-control', 'placeholder' => 'Nombre, empresa o correo…'],
                        'value' => fn(Inversor $m) => Html::tag('strong', Html::encode($m->nombreVisible()))
                            . Html::tag('div', Html::encode($m->usuario->email) . ' · ' . Html::encode($m->usuario->movil), ['class' => 'huk-eco-resumen']),
                    ],
                    [
                        'attribute' => 'tipo',
                        'label' => 'Tipo',
                        'filter' => Inversor::TIPOS,
                        'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                        'value' => fn(Inversor $m) => Inversor::TIPOS[$m->tipo] ?? $m->tipo,
                    ],
                    ['label' => 'País', 'value' => fn(Inversor $m) => $m->usuario->pais],
                    [
                        'attribute' => 'interes',
                        'label' => $tab === 'aprobado' ? 'Áreas aprobadas' : ($tab === 'todos' ? 'Áreas (aprobadas o de interés)' : 'Áreas de interés'),
                        'format' => 'raw',
                        'filter' => $ecosistemas,
                        'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todas'],
                        'value' => fn(Inversor $m) => Html::tag('span', Html::encode(implode(', ', array_column($m->estado_solicitud === 'aprobado' ? $m->areas : $m->intereses, 'nombre'))), ['class' => 'huk-eco-resumen']),
                    ],
                    [
                        'label' => 'Estado',
                        'format' => 'raw',
                        'visible' => $tab === 'todos',
                        'value' => fn(Inversor $m) => Html::tag('span', Html::encode($m->estadoNombre()), ['class' => 'label ' . $m->estadoClase()])
                            . ($m->usuario->estado !== '1' ? ' <span class="label label-danger">Cuenta bloqueada</span>' : ''),
                    ],
                    [
                        'label' => 'Monto (USD)',
                        // Desde / hasta: muestra a los inversores cuyo mínimo–máximo se cruza con ese rango
                        'filter' => Html::tag('div',
                            Html::activeInput('number', $searchModel, 'monto_desde', ['class' => 'form-control', 'placeholder' => 'Desde', 'min' => 0, 'step' => 'any'])
                            . Html::activeInput('number', $searchModel, 'monto_hasta', ['class' => 'form-control', 'placeholder' => 'Hasta', 'min' => 0, 'step' => 'any']),
                            ['class' => 'huk-filtro-monto']),
                        'value' => fn(Inversor $m) => $m->montoTexto(),
                    ],
                    [
                        'attribute' => 'nda',
                        'label' => 'NDA',
                        'format' => 'raw',
                        'visible' => $tab === 'aprobado',
                        'filter' => ['si' => 'Aceptado', 'no' => 'Falta'],
                        'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                        'value' => function (Inversor $m) use ($vigente) {
                            $ok = $vigente === null || \app\models\NdaAceptacion::find()->where(['idusuario' => $m->idusuario, 'idversion' => $vigente->idversion])->exists();
                            return $ok ? '<span class="label label-success">v' . ($vigente->numero ?? '') . ' aceptado</span>' : '<span class="label label-warning">Falta aceptar</span>';
                        },
                    ],
                    [
                        'label' => 'Registro',
                        'format' => 'raw',
                        'contentOptions' => ['class' => 'text-nowrap'],
                        'value' => fn(Inversor $m) => date('d/m/Y', strtotime($m->fecha_registro))
                            . Html::tag('div', $m->origen === 'web' ? 'Desde la web' : 'Cargado en el panel', ['class' => 'huk-eco-resumen']),
                    ],
                    [
                        'label' => '',
                        'format' => 'raw',
                        'value' => fn(Inversor $m) => Html::a('<i class="fa fa-eye"></i> Ver', ['view', 'id' => $m->idusuario], ['class' => 'btn btn-success btn-sm', 'data-pjax' => '0']),
                    ],
                ],
            ]) ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</section>
