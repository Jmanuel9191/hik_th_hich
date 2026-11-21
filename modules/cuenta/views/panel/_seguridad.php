<?php

/**
 * Mi cuenta → Seguridad: cambiar la contraseña (pide la actual) y último ingreso.
 *
 * @var yii\web\View $this
 * @var app\models\Inversor $inversor
 * @var app\models\ContrasenaInversorForm $formClave
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$u = $inversor->usuario;
$clave = fn($id) => ['template' => '{label}<div class="huk-clave">{input}<button type="button" data-ver-clave="#' . $id
    . '" aria-label="Ver contraseña"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></div>{hint}{error}'];
?>

<div class="huk-perfil-bloque">
    <div class="huk-perfil-bloque-cab">
        <h2><i class="fa-solid fa-key" aria-hidden="true"></i> Cambiar contraseña</h2>
    </div>
    <?php $form = ActiveForm::begin([
        'id' => 'perfil-clave-form', 'action' => ['/cuenta/panel/contrasena'], 'options' => ['class' => 'huk-form', 'autocomplete' => 'off'],
        'fieldConfig' => ['options' => ['class' => 'mb-0'], 'labelOptions' => ['class' => 'form-label'], 'inputOptions' => ['class' => 'form-control'],
            'errorOptions' => ['class' => 'invalid-feedback d-block'], 'hintOptions' => ['class' => 'form-text']],
    ]); ?>
    <div class="row g-3">
        <div class="col-md-4"><?= $form->field($formClave, 'actual', $clave('contrasenainversorform-actual'))->passwordInput(['autocomplete' => 'current-password']) ?></div>
        <div class="col-md-4"><?= $form->field($formClave, 'nueva', $clave('contrasenainversorform-nueva'))->passwordInput(['autocomplete' => 'new-password'])
            ->hint('Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.') ?></div>
        <div class="col-md-4"><?= $form->field($formClave, 'nueva2', $clave('contrasenainversorform-nueva2'))->passwordInput(['autocomplete' => 'new-password']) ?></div>
    </div>
    <div class="huk-perfil-guardar">
        <p><i class="fa-solid fa-envelope" aria-hidden="true"></i> Le enviaremos un aviso por correo cuando la cambie.</p>
        <?= Html::submitButton('<i class="fa-solid fa-key" aria-hidden="true"></i> Cambiar contraseña', ['class' => 'huk-btn-oro']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<div class="huk-perfil-bloque">
    <div class="huk-perfil-bloque-cab">
        <h2><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i> Último ingreso</h2>
    </div>
    <div class="huk-datos-grid">
        <div class="huk-dato"><span class="huk-dato-rotulo">Fecha y hora</span>
            <span class="huk-dato-valor"><?= $u->ultimo_acceso ? Yii::$app->formatter->asDatetime($u->ultimo_acceso, 'php:d/m/Y H:i') : '—' ?></span></div>
        <div class="huk-dato"><span class="huk-dato-rotulo">Desde la conexión</span>
            <span class="huk-dato-valor notranslate" translate="no"><?= Html::encode($u->ultimo_acceso_ip ?: '—') ?></span></div>
    </div>
    <p class="huk-perfil-nota"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Si no reconoce este ingreso, cambie su contraseña y escríbanos.</p>
</div>
