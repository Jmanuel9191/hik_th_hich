<?php

/**
 * Cambiar el nombre de una categoría del blog (su dirección en la web se actualiza sola).
 *
 * @var yii\web\View $this
 * @var app\models\NoticiaCategoria $model
 */

use kartik\form\ActiveForm;
use yii\helpers\Html;

\app\assets_b\NoticiasAdminAsset::register($this);

$this->title = 'Categoría: ' . $model->nombre;
?>

<section class="content-header">
    <h1><i class="fa fa-tags"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-body">
                    <?php $form = ActiveForm::begin(['id' => 'categoria-form']); ?>
                    <?= $form->field($model, 'nombre')->textInput(['maxlength' => 100])
                        ->hint('La dirección en la web (/noticias/categoria/…) se actualiza sola con el nombre.') ?>
                    <?= Html::submitButton('<i class="fa fa-save"></i> Guardar', ['class' => 'btn btn-success']) ?>
                    <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-default']) ?>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
