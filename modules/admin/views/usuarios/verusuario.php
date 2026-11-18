<?php

/**
 * Ficha del usuario + historial (quién hizo qué, cuándo, desde qué IP).
 * Botón de estado: assets_b/js/huk-usuarios.js (UsuariosAdminAsset), por atributos data-*.
 *
 * @var yii\web\View $this
 * @var app\models\Usuarios $model
 * @var yii\data\ActiveDataProvider|null $historial
 */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

\app\assets_b\UsuariosAdminAsset::register($this);

$this->title = $model->nombrecompleto;
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$esYo = (int) $model->idusuario === (int) Yii::$app->user->id;
$bloqueado = $model->estado !== '1';
$porIntentos = !$bloqueado && $model->bloqueado_hasta !== null && strtotime($model->bloqueado_hasta) > time();

if ($bloqueado) {
    $estadoHtml = Html::tag('span', 'Bloqueado', ['class' => 'label label-danger']);
} elseif ($porIntentos) {
    $min = (int) ceil((strtotime($model->bloqueado_hasta) - time()) / 60);
    $estadoHtml = Html::tag('span', "Bloqueado por intentos ({$min} min)", ['class' => 'label label-warning']);
} else {
    $estadoHtml = Html::tag('span', 'Activo', ['class' => 'label label-success']);
}

// Las fechas se guardan en hora de Bolivia: se muestran tal cual (sin conversión de zona).
$fecha = fn($v) => $v ? date('d/m/Y H:i', strtotime($v)) : '—';
?>

<section class="content-header">
    <h1><i class="fa fa-user"></i> <?= Html::encode($model->nombrecompleto) ?></h1>
</section>

<section class="content">
    <div class="row">

        <!-- Datos -->
        <div class="col-md-5">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Datos del usuario</h3>
                </div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped detail-view'],
                        'attributes' => [
                            'usuario',
                            'email:email',
                            ['label' => 'Tipo de usuario', 'value' => $model->rol->nombre ?? '(Sin rol)'],
                            [
                                'label' => 'Ecosistemas a cargo',
                                'value' => implode(', ', array_map(fn($e) => $e->nombre, $model->ecosistemas)),
                                'visible' => (int) $model->idrol === \app\components\Rol::GERENTE,
                            ],
                            ['label' => 'Estado', 'format' => 'raw', 'value' => $estadoHtml],
                            'pais_operacion',
                            'telefono',
                            'movil',
                            'cargo',
                            'ciudad',
                            'direccion',
                            ['label' => 'Fecha de registro', 'value' => $fecha($model->fecha_registro)],
                            [
                                'label' => 'Último acceso',
                                'value' => $model->ultimo_acceso
                                    ? $fecha($model->ultimo_acceso) . ($model->ultimo_acceso_ip ? ' · IP ' . $model->ultimo_acceso_ip : '')
                                    : 'Nunca ingresó',
                            ],
                        ],
                    ]) ?>
                </div>
                <div class="box-footer">
                    <?= Html::a('<i class="fa fa-pencil"></i> Editar', ['update', 'id' => $model->idusuario], ['class' => 'btn btn-info']) ?>
                    <?= Html::a('<i class="fa fa-key"></i> Contraseña', ['updatepassword', 'id' => $model->idusuario], ['class' => 'btn btn-default']) ?>
                    <?php if (!$esYo): ?>
                        <?= Html::a(
                            '<i class="fa ' . ($bloqueado ? 'fa-check' : 'fa-ban') . '"></i> ' . ($bloqueado ? 'Activar' : 'Bloquear'),
                            '#',
                            [
                                'class' => 'btn ' . ($bloqueado ? 'btn-success' : 'btn-danger'),
                                'data-usuario-estado' => Url::to(['estado', 'id' => $model->idusuario]),
                                'data-accion' => $bloqueado ? 'activar' : 'bloquear',
                                'data-nombre' => $model->nombrecompleto,
                            ]
                        ) ?>
                    <?php endif; ?>
                    <?php if ($porIntentos): ?>
                        <?= Html::a('<i class="fa fa-unlock"></i> Desbloquear intentos', '#', [
                            'class' => 'btn btn-warning',
                            'data-usuario-intentos' => Url::to(['desbloquear-intentos', 'id' => $model->idusuario]),
                            'data-nombre' => $model->nombrecompleto,
                        ]) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Historial -->
        <div class="col-md-7">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-history"></i> Historial</h3>
                </div>
                <div class="box-body">
                    <?php if ($historial === null): ?>
                        <p class="text-muted">No tenés permiso para ver el historial.</p>
                    <?php else: ?>
                        <?php Pjax::begin(['id' => 'historial-grid', 'timeout' => 5000]); ?>
                        <?= GridView::widget([
                            'dataProvider' => $historial,
                            'tableOptions' => ['class' => 'table table-hover'],
                            'emptyText' => 'Todavía no hay movimientos.',
                            'summary' => '{totalCount} movimiento(s)',
                            'columns' => [
                                [
                                    'label' => 'Fecha',
                                    'value' => fn($h) => $fecha($h->fecha),
                                    'contentOptions' => ['class' => 'text-nowrap'],
                                ],
                                [
                                    'label' => 'Acción',
                                    'format' => 'raw',
                                    'value' => fn($h) => Html::tag('strong', Html::encode($h->accion)),
                                ],
                                [
                                    'label' => 'Detalle',
                                    'format' => 'ntext',
                                    'value' => fn($h) => (string) $h->descripcion,
                                ],
                                [
                                    'label' => 'Hecho por',
                                    'value' => fn($h) => $h->idusuario_actor === null
                                        ? 'Sistema'
                                        : ($h->usuarioActor->usuario ?? 'Usuario #' . $h->idusuario_actor . ' (eliminado)'),
                                ],
                                [
                                    'label' => 'IP',
                                    'value' => fn($h) => $h->ip ?: '—',
                                ],
                            ],
                        ]) ?>
                        <?php Pjax::end(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</section>
