<?php

/**
 * Crear contraseña nueva desde el enlace del correo (mismo diseño que el login).
 *
 * @var \yii\web\View $this
 * @var string $estado  'form' | 'listo' | 'invalido'
 * @var \app\models\CambioPasswordObligatorioForm|null $model
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Nueva contraseña · Panel HUK';

$logoUrl = is_file(Yii::getAlias('@webroot/assets_b/img/logo.png'))
    ? Url::to('@web/assets_b/img/logo.png')
    : null;
$logo = $logoUrl
    ? Html::img($logoUrl, ['alt' => 'HUK-THE-ICHI GLOBAL GROUP'])
    : Html::tag('span', 'HUK-THE-ICHI');

$ojo = '<button type="button" class="huk-adm-eye" data-ver-clave aria-label="Mostrar contraseña"><i class="fa fa-eye" aria-hidden="true"></i></button>';
?>

<div class="huk-adm-login">
    <aside class="huk-adm-login-side">
        <div class="huk-adm-login-logo"><?= $logo ?></div>
        <div class="huk-adm-login-claim">
            <small>Panel de administración</small>
            <h2>Creá tu nueva contraseña</h2>
            <p>Usá al menos 8 caracteres con una mayúscula, una minúscula, un número y un símbolo.</p>
        </div>
    </aside>

    <main class="huk-adm-login-main">
        <div class="huk-adm-login-card">
            <div class="huk-adm-mobile-logo"><?= $logo ?></div>

            <?php if ($estado === 'invalido'): ?>

                <h1>Enlace no válido</h1>
                <div class="huk-adm-alert is-error" role="alert">
                    <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                    <span>El enlace venció o ya fue usado. Pedí uno nuevo.</span>
                </div>
                <?= Html::a('Pedir un enlace nuevo', ['cuenta/forget'], ['class' => 'huk-adm-btn']) ?>
                <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Volver al login', ['default/login'], ['class' => 'huk-adm-forgot']) ?>

            <?php elseif ($estado === 'listo'): ?>

                <h1>¡Listo!</h1>
                <div class="huk-adm-alert is-ok" role="alert">
                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                    <span>Tu contraseña se cambió correctamente. Ya podés ingresar con la nueva.</span>
                </div>
                <?= Html::a('Ir al login', ['default/login'], ['class' => 'huk-adm-btn']) ?>

            <?php else: ?>

                <h1>Nueva contraseña</h1>
                <p class="huk-adm-sub">Escribila dos veces para confirmar.</p>

                <?php $form = ActiveForm::begin(['id' => 'restablecer-form']); ?>

                <?= $form->field($model, 'nueva_contrasena', [
                    'template' => "{label}\n<div class=\"huk-adm-field\"><i class=\"fa fa-lock\" aria-hidden=\"true\"></i>{input}{$ojo}</div>\n{error}",
                    'inputOptions' => ['class' => 'form-control', 'placeholder' => 'Nueva contraseña', 'autocomplete' => 'new-password', 'autofocus' => true],
                ])->passwordInput()->label('Nueva contraseña') ?>

                <?= $form->field($model, 'confirmar_contrasena', [
                    'template' => "{label}\n<div class=\"huk-adm-field\"><i class=\"fa fa-lock\" aria-hidden=\"true\"></i>{input}{$ojo}</div>\n{error}",
                    'inputOptions' => ['class' => 'form-control', 'placeholder' => 'Repetí la contraseña', 'autocomplete' => 'new-password'],
                ])->passwordInput()->label('Confirmar contraseña') ?>

                <?= Html::submitButton('Guardar contraseña', ['class' => 'huk-adm-btn', 'name' => 'restablecer-button']) ?>

                <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Volver al login', ['default/login'], ['class' => 'huk-adm-forgot']) ?>

                <?php ActiveForm::end(); ?>

            <?php endif; ?>

            <p class="huk-adm-login-foot">&copy; <?= date('Y') ?> HUK-THE-ICHI GLOBAL GROUP</p>
        </div>
    </main>
</div>
