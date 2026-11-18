<?php

/**
 * Formulario de ecosistema (crear / editar).
 * Editor: assets_b/js/huk-ecosistemas.js. Imágenes: app\widgets\CampoImagen.
 *
 * @var yii\web\View $this
 * @var app\models\Ecosistema $model
 */

use app\models\Ecosistema;
use app\widgets\CampoImagen;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\EcosistemasAdminAsset::register($this);
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'class' => 'huk-eco-form']]); ?>

<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Contenido</h3></div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-3">
                        <?= $form->field($model, 'numero')->input('number', ['min' => 0, 'max' => 999])->hint('Orden en la web (01, 02…)') ?>
                    </div>
                    <div class="col-sm-9">
                        <?= $form->field($model, 'nombre')->textInput(['maxlength' => 120]) ?>
                    </div>
                </div>

                <?= $form->field($model, 'resumen')->textarea(['rows' => 3, 'maxlength' => 500])
                    ->hint('Una o dos líneas. Se muestra en las tarjetas de la web.') ?>

                <?= $form->field($model, 'descripcion')->textarea([
                    'data-editor' => true,
                    'data-tinymce-base' => Url::to('@web/assets_b/lib/tinymce'),
                    'rows' => 14,
                ])->hint('Podés pegar tablas desde Excel: se mantienen los colores, bordes y negritas.') ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Imágenes</h3></div>
            <div class="box-body">
                <?php foreach (Ecosistema::IMAGENES as $campo => $etiqueta): ?>
                    <?= $form->field($model, $campo)->widget(CampoImagen::class, $campo === 'icono'
                        ? ['url' => $model->url($model->$campo), 'medida' => 'Cuadrado, fondo transparente', 'extensiones' => ['svg', 'png', 'webp'], 'maxMb' => 1, 'icono' => true]
                        : ['url' => $model->url($model->$campo), 'medida' => trim(strstr(Ecosistema::MEDIDAS[$campo], '(', true))]
                    )->label($etiqueta) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="huk-form-acciones">
    <?= Html::submitButton('<i class="fa fa-save"></i> Guardar', ['class' => 'btn btn-success']) ?>
    <?= Html::a('Cancelar', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->idecosistema], ['class' => 'btn btn-default']) ?>
</div>

<?php ActiveForm::end(); ?>
