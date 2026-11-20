<?php

/**
 * @var yii\web\View $this
 * @var yii\base\DynamicModel $model
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

\app\assets_b\CuentaAsset::register($this);
?>

<section class="huk-cuenta">
    <div class="container">
        <div class="huk-cuenta-cab">
            <span class="huk-cuenta-eyebrow">Área privada</span>
            <h1>Recuperar contraseña</h1>
            <p>Escriba el correo con el que se registró y le enviamos un enlace para crear una contraseña nueva.</p>
        </div>
        <div class="huk-tarjeta-form is-angosta">
            <?php $form = ActiveForm::begin(['id' => 'olvide-form', 'options' => ['class' => 'huk-form'], 'fieldConfig' => [
                'options' => ['class' => 'mb-3'], 'labelOptions' => ['class' => 'form-label'], 'inputOptions' => ['class' => 'form-control'],
                'errorOptions' => ['class' => 'invalid-feedback d-block']]]); ?>
            <?= $form->field($model, 'email')->textInput(['type' => 'email', 'autocomplete' => 'email', 'autofocus' => true]) ?>
            <?= Html::submitButton('Enviar enlace', ['class' => 'huk-btn-oro w-100']) ?>
            <?php ActiveForm::end(); ?>
            <p class="huk-form-pie"><?= Html::a('Volver a ingresar', ['/cuenta/acceso/ingresar']) ?></p>
        </div>
    </div>
</section>
