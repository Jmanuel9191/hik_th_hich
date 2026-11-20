<?php

/**
 * Datos de una plantilla: nombre, para qué sirve, ecosistema al que se sugiere y si está activa.
 *
 * @var yii\web\View $this
 * @var app\models\ProyectoPlantilla $model
 * @var array $ecosistemas id => nombre
 */

use yii\helpers\Html;

$err = fn($a) => $model->hasErrors($a) ? '<p class="help-block help-block-error">' . Html::encode($model->getFirstError($a)) . '</p>' : '';
$grupo = fn($a) => 'form-group' . ($model->hasErrors($a) ? ' has-error' : '');
?>
<div class="box">
    <div class="box-body">
        <?= Html::beginForm() ?>
            <div class="<?= $grupo('nombre') ?>">
                <?= Html::activeLabel($model, 'nombre', ['class' => 'control-label']) ?>
                <?= Html::activeTextInput($model, 'nombre', ['class' => 'form-control', 'maxlength' => 100, 'placeholder' => 'Energía solar']) ?>
                <?= $err('nombre') ?>
            </div>
            <div class="<?= $grupo('descripcion') ?>">
                <?= Html::activeLabel($model, 'descripcion', ['class' => 'control-label']) ?>
                <?= Html::activeTextInput($model, 'descripcion', ['class' => 'form-control', 'maxlength' => 300]) ?>
                <?= $err('descripcion') ?>
            </div>
            <div class="<?= $grupo('idecosistema') ?>">
                <?= Html::activeLabel($model, 'idecosistema', ['class' => 'control-label']) ?>
                <?= Html::activeDropDownList($model, 'idecosistema', $ecosistemas, ['class' => 'form-control', 'prompt' => 'Cualquiera (no se sugiere sola)']) ?>
                <p class="help-block">Al cargar la página de un proyecto de ese ecosistema, viene elegida esta plantilla.</p>
                <?= $err('idecosistema') ?>
            </div>
            <div class="huk-pe-casillas">
                <?= Html::hiddenInput('ProyectoPlantilla[estado]', '0') ?>
                <label><?= Html::checkbox('ProyectoPlantilla[estado]', $model->estado === '1', ['value' => '1']) ?> Activa (se puede elegir en los proyectos)</label>
            </div>
            <div class="huk-barra-acciones">
                <?= Html::a('<i class="fa fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-default']) ?>
                <?= Html::submitButton('<i class="fa fa-check"></i> ' . ($model->isNewRecord ? 'Crear y armar secciones' : 'Guardar'), ['class' => 'btn btn-success']) ?>
            </div>
        <?= Html::endForm() ?>
    </div>
</div>
