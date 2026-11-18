<?php

/**
 * Cambiar la contraseña de un usuario (admin). Campos vacíos; ojo para ver lo escrito (huk-admin.js, data-ver-clave).
 *
 * @var yii\web\View $this
 * @var app\models\CambioPasswordObligatorioForm $model
 * @var app\models\Usuarios $usuario
 */

use kartik\form\ActiveForm;
use yii\helpers\Html;

\app\assets_b\UsuariosAdminAsset::register($this);

$this->title = 'Cambiar contraseña: ' . $usuario->nombrecompleto;

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
    <h1><i class="fa fa-key"></i> Cambiar contraseña</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-lg-7 col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Html::encode($usuario->nombrecompleto) ?></h3>
                    <span class="huk-clave-usuario"><i class="fa fa-user"></i> <?= Html::encode($usuario->usuario) ?> · <?= Html::encode($usuario->rol->nombre ?? '') ?></span>
                </div>

                <?php $form = ActiveForm::begin([
                    'id' => 'usuario-clave-form',
                    'type' => ActiveForm::TYPE_HORIZONTAL,
                    'formConfig' => ['labelSpan' => 4, 'deviceSize' => ActiveForm::SIZE_SMALL],
                    'options' => ['class' => 'huk-form-usuario', 'autocomplete' => 'off'],
                ]); ?>

                <?= $form->field($model, 'nueva_contrasena', $conOjo)
                    ->passwordInput(['autocomplete' => 'new-password', 'value' => '', 'autofocus' => true])
                    ->hint('Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.') ?>

                <?= $form->field($model, 'confirmar_contrasena', $conOjo)
                    ->passwordInput(['autocomplete' => 'new-password', 'value' => '']) ?>

                <p class="huk-clave-aviso">
                    <i class="fa fa-info-circle"></i> Al guardar se cierran las sesiones abiertas de este usuario y queda registrado en su historial.
                </p>

                <div class="huk-form-acciones text-center">
                    <?= Html::submitButton('<i class="fa fa-save"></i> Guardar contraseña', ['class' => 'btn btn-success btform']) ?>
                    <?= Html::a('Cancelar', ['view', 'id' => $usuario->idusuario], ['class' => 'btn btn-default btform']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</section>
