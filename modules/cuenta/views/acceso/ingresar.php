<?php

/**
 * Ingreso de inversores (con correo y contraseña).
 *
 * @var yii\web\View $this
 * @var app\models\LoginForm $model
 * @var string|null $sinConfirmar email (si falta confirmar el correo: muestra "reenviar")
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

\app\assets_b\CuentaAsset::register($this);

$campo = ['options' => ['class' => 'mb-3'], 'labelOptions' => ['class' => 'form-label'], 'inputOptions' => ['class' => 'form-control'],
    'errorOptions' => ['class' => 'invalid-feedback d-block']];
?>

<section class="huk-cuenta">
    <div class="container">
        <div class="huk-cuenta-cab">
            <span class="huk-cuenta-eyebrow">Área privada</span>
            <h1>Acceso inversores</h1>
        </div>
        <div class="huk-tarjeta-form is-angosta">
            <?= $this->render('../_aviso') ?>
            <?php $form = ActiveForm::begin(['id' => 'ingresar-form', 'options' => ['class' => 'huk-form'], 'fieldConfig' => $campo]); ?>
            <?= $form->field($model, 'username')->textInput(['type' => 'email', 'autocomplete' => 'username', 'autofocus' => true])->label('Correo electrónico') ?>
            <?= $form->field($model, 'password', ['template' => '{label}<div class="huk-clave">{input}<button type="button" data-ver-clave="#loginform-password" aria-label="Ver contraseña"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></div>{error}'])
                ->passwordInput(['autocomplete' => 'current-password'])->label('Contraseña') ?>
            <p class="text-end"><?= Html::a('¿Olvidaste tu contraseña?', ['/cuenta/acceso/olvide'], ['class' => 'small']) ?></p>
            <?= Html::submitButton('Ingresar', ['class' => 'huk-btn-oro w-100']) ?>
            <?php ActiveForm::end(); ?>

            <?php if ($sinConfirmar): ?>
                <?= Html::beginForm(['/cuenta/acceso/reenviar'], 'post', ['class' => 'huk-reenviar']) ?>
                <?= Html::hiddenInput('email', $sinConfirmar) ?>
                <?= Html::submitButton('<i class="fa-solid fa-envelope" aria-hidden="true"></i> Reenviarme el correo de confirmación', ['class' => 'btn btn-outline-dark w-100']) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>

            <p class="huk-form-pie">¿Todavía no sos socio? <?= Html::a('Enviá tu solicitud', ['/cuenta/acceso/registro']) ?></p>
        </div>
    </div>
</section>
