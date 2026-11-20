<?php

/**
 * Plantillas de página de proyecto: nombre, ecosistema al que se sugiere, cantidad de secciones y acciones
 * (editar secciones, datos, subir/bajar, duplicar, eliminar). Botones: huk-listado.js (data-listado-*).
 *
 * @var yii\web\View $this
 * @var app\models\ProyectoPlantilla[] $plantillas
 * @var array $secciones idplantilla => cantidad
 */

use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\ProyectosAdminAsset::register($this);

$this->title = 'Plantillas de proyecto';
?>
<section class="content-header">
    <h1><i class="fa fa-clone"></i> Plantillas de proyecto</h1>
    <p class="huk-inicio-sub">Las secciones con las que arranca la página web de un proyecto. Cambiar una plantilla no toca los proyectos que ya la usaron.</p>
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
            <div class="huk-barra-acciones">
                <?= Html::a('<i class="fa fa-plus"></i> Nueva plantilla', ['create'], ['class' => 'btn btn-success']) ?>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th style="width:110px">Orden</th><th>Plantilla</th><th>Se sugiere para</th><th>Secciones</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($plantillas as $i => $t): ?>
                        <tr>
                            <td class="text-nowrap">
                                <button type="button" class="btn btn-default btn-xs" data-listado-mover="<?= Url::to(['mover', 'id' => $t->idplantilla, 'direccion' => -1]) ?>"<?= $i === 0 ? ' disabled' : '' ?> title="Subir"><i class="fa fa-arrow-up"></i></button>
                                <button type="button" class="btn btn-default btn-xs" data-listado-mover="<?= Url::to(['mover', 'id' => $t->idplantilla, 'direccion' => 1]) ?>"<?= $i === count($plantillas) - 1 ? ' disabled' : '' ?> title="Bajar"><i class="fa fa-arrow-down"></i></button>
                            </td>
                            <td>
                                <strong><?= Html::encode($t->nombre) ?></strong>
                                <?php if ($t->descripcion): ?><div class="huk-eco-resumen"><?= Html::encode($t->descripcion) ?></div><?php endif; ?>
                            </td>
                            <td><?= $t->ecosistema ? Html::tag('span', Html::encode($t->ecosistema->nombre), ['class' => 'huk-plantilla-eco']) : '<span class="text-muted">Cualquiera</span>' ?></td>
                            <td><?= $secciones[$t->idplantilla] ?? 0 ?></td>
                            <td><span class="label <?= $t->estado === '1' ? 'label-success' : 'label-default' ?>"><?= $t->estado === '1' ? 'Activa' : 'Oculta' ?></span></td>
                            <td class="text-nowrap text-right">
                                <?= Html::a('<i class="fa fa-list-alt"></i> Secciones', ['editar', 'id' => $t->idplantilla], ['class' => 'btn btn-success btn-sm']) ?>
                                <?= Html::a('<i class="fa fa-pencil"></i>', ['update', 'id' => $t->idplantilla], ['class' => 'btn btn-info btn-sm', 'title' => 'Nombre y ecosistema']) ?>
                                <button type="button" class="btn btn-default btn-sm" title="Duplicar" data-listado-destacar="<?= Url::to(['duplicar', 'id' => $t->idplantilla]) ?>"><i class="fa fa-files-o"></i></button>
                                <?= Html::a('<i class="fa fa-trash"></i>', '#', ['class' => 'btn btn-danger btn-sm', 'title' => 'Eliminar',
                                    'data-listado-eliminar' => Url::to(['delete', 'id' => $t->idplantilla]), 'data-nombre' => $t->nombre,
                                    'data-aviso' => 'Se borra la plantilla con sus secciones y fotos. Los proyectos que la usaron no cambian.']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$plantillas): ?>
                        <tr><td colspan="6" class="text-muted">Todavía no hay plantillas.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
