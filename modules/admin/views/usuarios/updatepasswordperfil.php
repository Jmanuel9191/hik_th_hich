<?php

/**
 * Cambiar mi contraseña. Pide la actual. Ojo en cada campo (huk-admin.js, data-ver-clave).
 *
 * @var yii\web\View $this
 * @var app\models\ChangePasswordForm $model
 */

use kartik\form\ActiveForm;
use yii\helpers\Html;

\app\assets_b\UsuariosAdminAsset::register($this);

$this->title = 'Cambiar mi contraseña';

$conOjo = [
    'addon' => [
        'groupOptions' => ['class' => 'huk-adm-field'],
        'append' => [
            'content' => '<button type="button" class="btn btn-default" data-ver-clave aria-label="Mostrar contraseña">'
                . '<i class="fa fa-eye" aria-hidden="true"></i></button>',
            'asButton' => true,
        ],
    ],
];
?>

<section class="content-header">
    <h1><i class="fa fa-key"></i> Cambiar mi contraseña</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-lg-7 col-md-9">
            <div class="box box-primary">
                <?php $form = ActiveForm::begin([
                    'id' => 'usuario-clave-form',
                    'type' => ActiveForm::TYPE_HORIZONTAL,
                    'formConfig' => ['labelSpan' => 4, 'deviceSize' => ActiveForm::SIZE_SMALL],
                    'options' => ['class' => 'huk-form-usuario', 'autocomplete' => 'off'],
                ]); ?>

                <?= $form->field($model, 'currentPassword', $conOjo)
                    ->passwordInput(['autocomplete' => 'current-password', 'value' => '', 'autofocus' => true]) ?>

                <?= $form->field($model, 'newPassword', $conOjo)
                    ->passwordInput(['autocomplete' => 'new-password', 'value' => ''])
                    ->hint('Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.') ?>

                <?= $form->field($model, 'confirmPassword', $conOjo)
                    ->passwordInput(['autocomplete' => 'new-password', 'value' => '']) ?>

                <p class="huk-clave-aviso">
                    <i class="fa fa-info-circle"></i> Esta sesión sigue abierta; si tenías el panel abierto en otro equipo, ahí se cierra.
                </p>

                <div class="huk-form-acciones text-center">
                    <?= Html::submitButton('<i class="fa fa-save"></i> Guardar contraseña', ['class' => 'btn btn-success btform']) ?>
                    <?= Html::a('Cancelar', ['cuenta'], ['class' => 'btn btn-default btform']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</section>
