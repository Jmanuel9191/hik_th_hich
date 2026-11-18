<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;

$this->title = 'ACTUALIZAR PASSWORD TEMPORAL';
?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <h1 class="app-page-title">Actualizar contraseña temporal</h1>
        <hr class="mb-4">
        <div class="row g-4 settings-section">
            <div class="col-12">
                <div class="app-card app-card-settings shadow-sm p-4">
                    <div class="app-card-body">

                        <?php $form = ActiveForm::begin([
                            'id' => 'formCambioClave',
                            'class' => 'settings-form',
                            'action' => ['cuenta/cambiar-clave-obligatoria'],
                            'method' => 'post'
                        ]); ?>

                        <div class="col-12 col-lg-12">
                            <div class="mb-3">
                                <?= $form->field($model, 'nueva_contrasena')->passwordInput(['class' => 'form-control']) ?>
                            </div>
                        </div>

                        <div class="col-12 col-lg-12">
                            <div class="mb-3">
                                <?= $form->field($model, 'confirmar_contrasena')->passwordInput(['class' => 'form-control']) ?>
                            </div>
                        </div>

                        <?= Html::submitButton('Actualizar contraseña', ['class' => 'btn app-btn-primary']) ?>

                        <?php ActiveForm::end(); ?>

                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">
    </div>
</div>
