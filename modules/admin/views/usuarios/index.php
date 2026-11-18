<?php

/**
 * Usuarios del panel (roles 1-4). Los inversores tienen su propio módulo.
 * Botones de estado y desbloqueo: assets_b/js/huk-usuarios.js (UsuariosAdminAsset), por atributos data-*.
 *
 * @var yii\web\View $this
 * @var app\models\UsuariosSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var bool $puedeExportar
 */

use app\components\Rol;
use app\models\Ecosistema;
use app\models\Roles;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

\app\assets_b\UsuariosAdminAsset::register($this);

$this->title = 'Usuarios';
$this->params['breadcrumbs'][] = $this->title;

$miId = (int) Yii::$app->user->id;
$roles = ArrayHelper::map(Roles::find()->where(['idrol' => Rol::PANEL])->orderBy('idrol')->all(), 'idrol', 'nombre');
?>

<section class="content-header">
    <h1><i class="fa fa-users"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-body">
                    <?php Pjax::begin(['id' => 'usuarios-grid', 'timeout' => 5000]); ?>
                    <div class="huk-barra-acciones">
                        <?= Html::a('<i class="fa fa-plus"></i> Nuevo usuario', ['create'], ['class' => 'btn btn-success', 'data-pjax' => '0']) ?>
                        <?php if ($puedeExportar): ?>
                            <?php $q = Yii::$app->request->getQueryParams(); unset($q['formato']); ?>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                        title="Exporta lo que ves en la tabla (con los filtros que tengas). Sin filtros = todos.">
                                    <i class="fa fa-download"></i> Exportar <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><?= Html::a('<i class="fa fa-file-excel-o"></i> Excel (.xlsx)', array_merge(['exportar', 'formato' => 'xlsx'], $q), ['data-pjax' => '0']) ?></li>
                                    <li><?= Html::a('<i class="fa fa-file-text-o"></i> CSV', array_merge(['exportar', 'formato' => 'csv'], $q), ['data-pjax' => '0']) ?></li>
                                    <li><?= Html::a('<i class="fa fa-file-pdf-o"></i> PDF', array_merge(['exportar', 'formato' => 'pdf'], $q), ['data-pjax' => '0', 'target' => '_blank']) ?></li>
                                </ul>
                            </div>
                            <small class="text-muted">Exporta con los filtros aplicados · sin filtros = todos</small>
                        <?php endif; ?>
                    </div>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'tableOptions' => ['class' => 'table table-striped table-hover'],
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            'nombrecompleto',
                            'usuario',
                            'email:email',
                            [
                                'attribute' => 'movil',
                                'label' => 'Celular / WhatsApp',
                                'value' => fn($m) => $m->movil ?: '—',
                            ],
                            [
                                'attribute' => 'idrol',
                                'label' => 'Tipo de usuario',
                                'value' => fn($m) => $m->rol->nombre ?? '(Sin rol)',
                                'filter' => $roles,
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                            ],
                            [
                                'attribute' => 'idecosistema',
                                'label' => 'Ecosistemas',
                                'format' => 'raw',
                                'filter' => Ecosistema::lista(false),
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'value' => function ($m) {
                                    if ((int) $m->idrol !== Rol::GERENTE) {
                                        return Html::tag('span', '—', ['class' => 'text-muted']);
                                    }
                                    $ecos = array_map(fn($e) => $e->nombre, $m->ecosistemas);
                                    return $ecos ? Html::encode(implode(', ', $ecos)) : Html::tag('span', 'Sin ecosistemas', ['class' => 'text-muted']);
                                },
                            ],
                            [
                                'attribute' => 'estado',
                                'label' => 'Estado',
                                'format' => 'raw',
                                'filter' => ['1' => 'Activo', '0' => 'Bloqueado'],
                                'filterInputOptions' => ['class' => 'form-control', 'prompt' => 'Todos'],
                                'value' => function ($m) {
                                    if ($m->estado !== '1') {
                                        return Html::tag('span', 'Bloqueado', ['class' => 'label label-danger']);
                                    }
                                    if ($m->bloqueado_hasta !== null && strtotime($m->bloqueado_hasta) > time()) {
                                        $min = (int) ceil((strtotime($m->bloqueado_hasta) - time()) / 60);
                                        return Html::tag('span', "Bloqueado por intentos ({$min} min)", ['class' => 'label label-warning']);
                                    }
                                    return Html::tag('span', 'Activo', ['class' => 'label label-success']);
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'Operaciones',
                                'template' => '<div class="btn-group btn-group-justified" role="group">{view}{update}{estado}{intentos}{delete}</div>',
                                'buttons' => [
                                    'view' => fn($url) => Html::a('<i class="fa fa-eye"></i>', $url, [
                                        'class' => 'btn btn-success', 'title' => 'Ver y ver historial', 'data-pjax' => '0',
                                    ]),
                                    'update' => fn($url) => Html::a('<i class="fa fa-pencil"></i>', $url, [
                                        'class' => 'btn btn-info', 'title' => 'Editar', 'data-pjax' => '0',
                                    ]),
                                    'estado' => function ($url, $m) use ($miId) {
                                        if ((int) $m->idusuario === $miId) {
                                            return '';
                                        }
                                        $bloqueado = $m->estado !== '1';
                                        return Html::a('<i class="fa ' . ($bloqueado ? 'fa-check' : 'fa-ban') . '"></i>', '#', [
                                            'class' => 'btn ' . ($bloqueado ? 'btn-success' : 'btn-danger'),
                                            'title' => $bloqueado ? 'Activar' : 'Bloquear',
                                            'data-usuario-estado' => $url,
                                            'data-accion' => $bloqueado ? 'activar' : 'bloquear',
                                            'data-nombre' => $m->nombrecompleto,
                                        ]);
                                    },
                                    'intentos' => function ($url, $m) {
                                        if ($m->bloqueado_hasta === null || strtotime($m->bloqueado_hasta) <= time()) {
                                            return '';
                                        }
                                        return Html::a('<i class="fa fa-unlock"></i>', '#', [
                                            'class' => 'btn btn-warning',
                                            'title' => 'Desbloquear por intentos fallidos',
                                            'data-usuario-intentos' => $url,
                                            'data-nombre' => $m->nombrecompleto,
                                        ]);
                                    },
                                    'delete' => function ($url, $m) use ($miId) {
                                        if ((int) $m->idusuario === $miId) {
                                            return '';
                                        }
                                        return Html::a('<i class="fa fa-trash"></i>', $url, [
                                            'class' => 'btn btn-danger',
                                            'title' => 'Eliminar',
                                            'data-pjax' => '0',
                                            'data-method' => 'post',
                                            'data-confirm' => '¿Eliminar a ' . $m->nombrecompleto . '? Mejor bloquealo si querés conservar su historial.',
                                        ]);
                                    },
                                ],
                                'urlCreator' => function ($action, $m) {
                                    $rutas = ['estado' => 'estado', 'intentos' => 'desbloquear-intentos'];
                                    return Url::to(['/admin/usuarios/' . ($rutas[$action] ?? $action), 'id' => $m->idusuario]);
                                },
                            ],
                        ],
                    ]) ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
