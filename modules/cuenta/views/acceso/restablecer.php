<?php

/**
 * Crear contraseña: recuperación o activación de una cuenta cargada desde el panel.
 *
 * @var yii\web\View $this
 * @var yii\base\DynamicModel $model
 * @var bool $activar
 * @var string $nombre
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

\app\assets_b\CuentaAsset::register($this);

$clave = fn($id) => ['template' => '{label}<div class="huk-clave">{input}<button type="button" data-ver-clave="#' . $id
    . '" aria-label="Ver contraseña"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></div>{hint}{error}'];
?>

<section class="huk-cuenta">
    <div class="container">
        <div class="huk-cuenta-cab">
            <span class="huk-cuenta-eyebrow">Área privada</span>
            <h1><?= $activar ? 'Activá tu cuenta' : 'Nueva contraseña' ?></h1>
            <p>Hola, <?= Html::encode($nombre) ?>. <?= $activar ? 'Creá la contraseña con la que vas a ingresar.' : 'Elegí tu contraseña nueva.' ?></p>
        </div>
        <div class="huk-tarjeta-form is-angosta">
            <?php $form = ActiveForm::begin(['id' => 'restablecer-form', 'options' => ['class' => 'huk-form'], 'fieldConfig' => [
                'options' => ['class' => 'mb-3'], 'labelOptions' => ['class' => 'form-label'], 'inputOptions' => ['class' => 'form-control'],
                'errorOptions' => ['class' => 'invalid-feedback d-block'], 'hintOptions' => ['class' => 'form-text']]]); ?>
            <?= $form->field($model, 'contrasena', $clave('dynamicmodel-contrasena'))->passwordInput(['maxlength' => 72, 'autocomplete' => 'new-password'])
                ->hint('Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.') ?>
            <?= $form->field($model, 'contrasena2', $clave('dynamicmodel-contrasena2'))->passwordInput(['maxlength' => 72, 'autocomplete' => 'new-password']) ?>
            <?= Html::submitButton($activar ? 'Activar mi cuenta' : 'Guardar contraseña', ['class' => 'huk-btn-oro w-100']) ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</section>
