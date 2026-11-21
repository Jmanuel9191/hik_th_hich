<?php

/**
 * Pestaña Documentos: privados (privado/proyectos/<id>/documentos). Cada descarga queda registrada.
 * Subida con barra de progreso: huk-proyectos.js. Visible / eliminar: huk-listado.js.
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $model
 * @var bool $puedeEditar
 * @var array $descargas iddocumento => cantidad
 * @var int $limiteServidor bytes
 */

use app\components\SubidaArchivo;
use app\models\Proyecto;
use app\models\ProyectoDocumento;
use yii\helpers\Html;
use yii\helpers\Url;
use app\widgets\ZonaArchivo;

$maxBytes = min(Proyecto::MAX_DOCUMENTO_MB * 1048576, $limiteServidor ?: PHP_INT_MAX);
$nuevo = new ProyectoDocumento(['visible_inversor' => '1']);
$documentos = $model->documentos;
?>

<div class="row">
    <div class="<?= $puedeEditar ? 'col-lg-8' : 'col-lg-12' ?>">
        <div class="box box-primary">
            <div class="box-body">
                <?php if (!$documentos): ?>
                    <p class="text-muted">Todavía no hay documentos.</p>
                <?php else: ?>
                    <table class="table table-striped table-hover">
                        <thead><tr><th>Documento</th><th>Tipo</th><th>Lo ven</th><th>Peso</th><th>Subido</th><th>Descargas</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($documentos as $d): ?>
                            <tr>
                                <td>
                                    <strong><?= Html::encode($d->nombre) ?></strong>
                                    <div class="huk-eco-resumen"><i class="fa fa-file-o"></i> <?= Html::encode($d->nombre_original) ?></div>
                                </td>
                                <td><?= Html::encode($d->tipoNombre()) ?></td>
                                <td>
                                    <?php $visible = $d->visible_inversor === '1'; ?>
                                    <?php if ($puedeEditar): ?>
                                        <?= Html::button($visible ? '<i class="fa fa-users"></i> Inversores' : '<i class="fa fa-lock"></i> Solo HUK', [
                                            'class' => 'btn btn-xs ' . ($visible ? 'btn-success' : 'btn-default'),
                                            'title' => 'Cambiar quién lo ve',
                                            'data-listado-estado' => Url::to(['documento-visible', 'id' => $d->iddocumento]),
                                            'data-accion' => $visible ? 'desactivar' : 'activar',
                                            'data-nombre' => $d->nombre,
                                            'data-aviso' => $visible ? 'Los inversores dejan de verlo. Solo lo ve el equipo de HUK.' : 'Lo van a poder ver y descargar los inversores aprobados del área.',
                                        ]) ?>
                                    <?php else: ?>
                                        <?= $visible ? '<span class="label label-success">Inversores</span>' : '<span class="label label-default">Solo HUK</span>' ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap"><?= SubidaArchivo::peso($d->tamano) ?></td>
                                <td class="text-nowrap">
                                    <?= date('d/m/Y', strtotime($d->fecha_registro)) ?>
                                    <div class="huk-eco-resumen"><?= Html::encode($d->usuario->usuario ?? '—') ?></div>
                                </td>
                                <td class="text-center"><?= (int) ($descargas[$d->iddocumento] ?? 0) ?></td>
                                <td class="text-nowrap">
                                    <?= Html::a('<i class="fa fa-download"></i>', ['documento', 'id' => $d->iddocumento], ['class' => 'btn btn-info btn-xs', 'title' => 'Descargar', 'data-pjax' => '0']) ?>
                                    <?php if ($puedeEditar): ?>
                                        <?= Html::button('<i class="fa fa-trash"></i>', ['class' => 'btn btn-danger btn-xs', 'title' => 'Eliminar',
                                            'data-listado-eliminar' => Url::to(['documento-eliminar', 'id' => $d->iddocumento]), 'data-nombre' => $d->nombre]) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="huk-ayuda"><i class="fa fa-info-circle"></i> Cada descarga queda registrada con usuario, fecha e IP.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($puedeEditar): ?>
        <div class="col-lg-4">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-plus"></i> Agregar documento</h3></div>
                <?= Html::beginForm(['documento-agregar', 'id' => $model->idproyecto], 'post', [
                    'enctype' => 'multipart/form-data',
                    'data-subida' => true,
                    'data-max-bytes' => $maxBytes,
                    'data-max-texto' => round($maxBytes / 1048576) . ' MB',
                ]) ?>
                <div class="box-body">
                    <div class="form-group">
                        <?= Html::activeLabel($nuevo, 'nombre', ['class' => 'control-label']) ?>
                        <?= Html::activeTextInput($nuevo, 'nombre', ['class' => 'form-control', 'maxlength' => 150, 'required' => true, 'placeholder' => 'Ej.: Plan de negocios 2027']) ?>
                    </div>
                    <div class="form-group">
                        <?= Html::activeLabel($nuevo, 'tipo', ['class' => 'control-label']) ?>
                        <?= Html::activeDropDownList($nuevo, 'tipo', ProyectoDocumento::TIPOS, ['class' => 'form-control', 'required' => true, 'prompt' => 'Elija…']) ?>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="documento-archivo">Archivo</label>
                        <?= ZonaArchivo::envolver(Html::activeFileInput($nuevo, 'archivo', ['id' => 'documento-archivo', 'required' => true,
                            'accept' => '.' . implode(',.', array_keys(ProyectoDocumento::FORMATOS)), 'data-subida-archivo' => true]),
                            'PDF, Word, Excel, PowerPoint o imagen · hasta ' . round($maxBytes / 1048576) . ' MB') ?>
                    </div>
                    <div class="checkbox">
                        <label><?= Html::activeCheckbox($nuevo, 'visible_inversor', ['label' => null, 'uncheck' => '0']) ?> Lo pueden ver los inversores</label>
                        <p class="help-block">Destíldelo para documentos internos (solo el equipo de HUK).</p>
                    </div>
                    <div class="huk-subida-progreso" data-subida-barra hidden>
                        <div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar">0%</div></div>
                        <p class="help-block">Subiendo… no cierres esta página.</p>
                    </div>
                </div>
                <div class="box-footer text-center">
                    <?= Html::submitButton('<i class="fa fa-upload"></i> Agregar documento', ['class' => 'btn btn-success', 'data-subida-boton' => true]) ?>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    <?php endif; ?>
</div>
