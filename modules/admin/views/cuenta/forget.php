<?php

/**
 * Recuperar contraseña del panel admin (mismo diseño que el login).
 * Ya NO registra AppAsset (los estilos de la web pública rompían el admin).
 *
 * @var \yii\web\View $this
 * @var \app\models\Forget $model
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Recuperar contraseña · Panel HUK';

$logoUrl = is_file(Yii::getAlias('@webroot/assets_b/img/logo.png'))
    ? Url::to('@web/assets_b/img/logo.png')
    : null;
$logo = $logoUrl
    ? Html::img($logoUrl, ['alt' => 'HUK-THE-ICHI GLOBAL GROUP'])
    : Html::tag('span', 'HUK-THE-ICHI');

// Mensajes que deja el controlador: setFlash('success'|'error', ['message' => ..., 'type' => ...])
$avisos = [];
foreach (Yii::$app->session->getAllFlashes() as $clave => $flash) {
    $texto = is_array($flash) ? ($flash['message'] ?? '') : (string) $flash;
    $tipo = is_array($flash) ? ($flash['type'] ?? $clave) : $clave;
    if ($texto !== '') {
        $avisos[] = ['ok' => $tipo === 'success', 'texto' => $texto];
    }
}
?>

<div class="huk-adm-login">
    <aside class="huk-adm-login-side">
        <div class="huk-adm-login-logo"><?= $logo ?></div>
        <div class="huk-adm-login-claim">
            <small>Panel de administración</small>
            <h2>¿Olvidó su contraseña?</h2>
            <p>Le enviamos un enlace a su correo para que cree una contraseña nueva. Vence en 30 minutos.</p>
        </div>
    </aside>

    <main class="huk-adm-login-main">
        <div class="huk-adm-login-card">
            <div class="huk-adm-mobile-logo"><?= $logo ?></div>

            <h1>Recuperar contraseña</h1>
            <p class="huk-adm-sub">Ingrese el correo con el que está registrado en el panel.</p>

            <?php foreach ($avisos as $aviso): ?>
                <div class="huk-adm-alert <?= $aviso['ok'] ? 'is-ok' : 'is-error' ?>" role="alert">
                    <i class="fa <?= $aviso['ok'] ? 'fa-check-circle' : 'fa-exclamation-circle' ?>" aria-hidden="true"></i>
                    <span><?= Html::encode($aviso['texto']) ?></span>
                </div>
            <?php endforeach; ?>

            <?php $form = ActiveForm::begin(['id' => 'forget-form']); ?>

            <?= $form->field($model, 'email', [
                'template' => "{label}\n<div class=\"huk-adm-field\"><i class=\"fa fa-envelope\" aria-hidden=\"true\"></i>{input}</div>\n{error}",
                'inputOptions' => [
                    'class' => 'form-control',
                    'type' => 'email',
                    'placeholder' => 'tucorreo@empresa.com',
                    'autocomplete' => 'email',
                    'autofocus' => true,
                ],
            ])->label('Correo electrónico') ?>

            <?= Html::submitButton('Enviar enlace', ['class' => 'huk-adm-btn', 'name' => 'forget-button']) ?>

            <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Volver al login', ['default/login'], ['class' => 'huk-adm-forgot']) ?>

            <?php ActiveForm::end(); ?>

            <p class="huk-adm-login-foot">&copy; <?= date('Y') ?> HUK-THE-ICHI GLOBAL GROUP</p>
        </div>
    </main>
</div>
