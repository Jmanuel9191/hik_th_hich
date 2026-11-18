<?php

/**
 * Roles y permisos (solo Superadmin). Casilla tildada = ese rol tiene ese permiso.
 *
 * @var yii\web\View $this
 * @var app\models\Roles[] $roles
 * @var array $grupos     modulo => ['nombre' => string, 'fijo' => bool, 'permisos' => app\models\Permisos[]]
 * @var array $asignados  "idrol-idpermiso" => true
 * @var yii\data\ActiveDataProvider $historial
 */

use app\components\Rol;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Roles y permisos';
?>

<section class="content-header">
    <h1><i class="fa fa-key"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body">
            <p class="text-muted">
                <i class="fa fa-info-circle"></i> Tildá lo que puede hacer cada tipo de usuario y presioná <b>Guardar cambios</b>.
                El <b>Superadmin</b> siempre tiene todo. Los permisos de <b>Usuarios</b> son solo del Superadmin.
                El Gerente de área, además, solo trabaja sobre los ecosistemas que tiene asignados.
            </p>

            <?= Html::beginForm(['index'], 'post') ?>
            <div class="table-responsive huk-permisos">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Permiso</th>
                            <?php foreach ($roles as $rol): ?>
                                <th class="text-center"><?= Html::encode($rol->nombre) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grupos as $grupo): ?>
                            <tr class="huk-permisos-grupo">
                                <td colspan="<?= count($roles) + 1 ?>">
                                    <?= Html::encode($grupo['nombre']) ?>
                                    <?php if ($grupo['fijo']): ?><small>· solo Superadmin</small><?php endif; ?>
                                </td>
                            </tr>
                            <?php foreach ($grupo['permisos'] as $p): ?>
                                <tr>
                                    <td>
                                        <?= Html::encode($p->descripcion) ?>
                                        <code><?= Html::encode($p->alias) ?></code>
                                    </td>
                                    <?php foreach ($roles as $rol): ?>
                                        <?php
                                        $esSuper = (int) $rol->idrol === Rol::SUPERADMIN;
                                        $tiene = $esSuper || isset($asignados[$rol->idrol . '-' . $p->idpermiso]);
                                        ?>
                                        <td class="text-center">
                                            <?= Html::checkbox('p[' . $rol->idrol . '][]', $tiene, [
                                                'value' => $p->idpermiso,
                                                'disabled' => $esSuper || $grupo['fijo'],
                                                'title' => $rol->nombre . ': ' . $p->alias,
                                            ]) ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= Html::submitButton('<i class="fa fa-save"></i> Guardar cambios', ['class' => 'btn btn-success']) ?>
            <?= Html::endForm() ?>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Historial de cambios</h3></div>
        <div class="box-body">
            <?php Pjax::begin(['id' => 'permisos-historial', 'timeout' => 5000]); ?>
            <?= GridView::widget([
                'dataProvider' => $historial,
                'tableOptions' => ['class' => 'table table-hover'],
                'emptyText' => 'Todavía no se cambió ningún permiso desde esta pantalla.',
                'summary' => '{totalCount} cambio(s)',
                'columns' => [
                    ['label' => 'Fecha', 'value' => fn($h) => date('d/m/Y H:i', strtotime($h->fecha)), 'contentOptions' => ['class' => 'text-nowrap']],
                    [
                        'label' => 'Acción',
                        'format' => 'raw',
                        'value' => fn($h) => Html::tag('span', Html::encode($h->accion), [
                            'class' => 'label ' . ($h->accion === 'Asignado' ? 'label-success' : 'label-danger'),
                        ]),
                    ],
                    ['label' => 'Rol', 'value' => fn($h) => $h->rol->nombre ?? '#' . $h->idrol],
                    ['label' => 'Permiso', 'value' => fn($h) => $h->permiso->descripcion ?? ('#' . $h->idpermiso . ' (borrado)')],
                    ['label' => 'Hecho por', 'value' => fn($h) => $h->usuarioActor->usuario ?? 'Usuario #' . $h->idusuario_actor],
                    ['label' => 'IP', 'value' => fn($h) => $h->ip ?: '—'],
                ],
            ]) ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</section>
