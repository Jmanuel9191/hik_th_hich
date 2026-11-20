<?php

/**
 * Login del panel admin (diseño HUK).
 * Ya NO registra AppAsset (los estilos de la web pública rompían el admin).
 *
 * @var \yii\web\View $this
 * @var \yii\base\Model $model  con los campos username y password
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Ingresar · Panel HUK';

$logoUrl = is_file(Yii::getAlias('@webroot/assets_b/img/logo.png'))
    ? Url::to('@web/assets_b/img/logo.png')
    : null;
$logo = $logoUrl
    ? Html::img($logoUrl, ['alt' => 'HUK-THE-ICHI GLOBAL GROUP'])
    : Html::tag('span', 'HUK-THE-ICHI');

?>

<div class="huk-adm-login">
    <aside class="huk-adm-login-side">
        <div class="huk-adm-login-logo"><?= $logo ?></div>
        <div class="huk-adm-login-claim">
            <small>Panel de administración</small>
            <h2>Gestione proyectos, inversores y contenido del sitio.</h2>
            <p>Acceso exclusivo para el equipo de HUK-THE-ICHI GLOBAL GROUP.</p>
        </div>
    </aside>

    <main class="huk-adm-login-main">
        <div class="huk-adm-login-card">
            <div class="huk-adm-mobile-logo"><?= $logo ?></div>

            <h1>Bienvenido</h1>
            <p class="huk-adm-sub">Ingrese con su usuario o email y su contraseña.</p>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'options' => ['autocomplete' => 'off'],
            ]); ?>

            <?= $form->field($model, 'username', [
                'template' => "{label}\n<div class=\"huk-adm-field\"><i class=\"fa fa-user\" aria-hidden=\"true\"></i>{input}</div>\n{error}",
                'inputOptions' => ['class' => 'form-control', 'placeholder' => 'Su usuario o email', 'autofocus' => true, 'autocomplete' => 'username'],
            ])->label('Usuario o email') ?>

            <?= $form->field($model, 'password', [
                'options' => ['class' => 'form-group', 'id' => 'huk-adm-pass'],
                'template' => "{label}\n<div class=\"huk-adm-field\"><i class=\"fa fa-lock\" aria-hidden=\"true\"></i>{input}"
                    . "<button type=\"button\" class=\"huk-adm-eye\" data-ver-clave aria-label=\"Mostrar contraseña\"><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></button></div>\n{error}",
                'inputOptions' => ['class' => 'form-control', 'placeholder' => 'Su contraseña', 'autocomplete' => 'current-password'],
            ])->passwordInput()->label('Contraseña') ?>

            <?= Html::submitButton('Entrar', ['class' => 'huk-adm-btn', 'name' => 'login-button']) ?>

            <?= Html::a('¿Olvidó su contraseña?', ['cuenta/forget'], ['class' => 'huk-adm-forgot']) ?>

            <?php ActiveForm::end(); ?>

            <p class="huk-adm-login-foot">&copy; <?= date('Y') ?> HUK-THE-ICHI GLOBAL GROUP</p>
        </div>
    </main>
</div>
