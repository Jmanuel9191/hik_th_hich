<?php

/**
 * Formulario de ecosistema (crear / editar).
 * Editor e imágenes: assets_b/js/huk-ecosistemas.js (por atributos data-*).
 *
 * @var yii\web\View $this
 * @var app\models\Ecosistema $model
 */

use app\models\Ecosistema;
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
                    <?php $idImg = 'prev-' . $campo; $actual = $model->url($model->$campo); ?>
                    <div class="form-group huk-img-campo <?= $model->hasErrors($campo) ? 'has-error' : '' ?>">
                        <label class="control-label"><?= Html::encode($etiqueta) ?></label>
                        <div class="huk-img-prev <?= $campo === 'icono' ? 'is-icono' : '' ?>">
                            <?= Html::img($actual ?: 'data:,', ['id' => $idImg, 'alt' => '', 'class' => $actual ? '' : 'hidden']) ?>
                        </div>
                        <?= Html::activeFileInput($model, $campo, [
                            'accept' => $campo === 'icono' ? '.svg,.png,.webp' : '.jpg,.jpeg,.png,.webp',
                            'data-preview' => '#' . $idImg,
                        ]) ?>
                        <?php if ($actual): ?>
                            <label class="huk-img-quitar">
                                <?= Html::checkbox('Ecosistema[quitar][' . $campo . ']', false, ['value' => 1, 'data-quitar' => '#' . $idImg]) ?>
                                Quitar esta imagen
                            </label>
                        <?php endif; ?>
                        <p class="help-block"><?= Html::encode(Ecosistema::MEDIDAS[$campo]) ?></p>
                        <?php if ($model->hasErrors($campo)): ?>
                            <p class="help-block help-block-error"><?= Html::encode($model->getFirstError($campo)) ?></p>
                        <?php endif; ?>
                    </div>
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
