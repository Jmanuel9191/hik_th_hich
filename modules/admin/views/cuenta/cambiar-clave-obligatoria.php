<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;

$this->title = 'ACTUALIZAR PASSWORD TEMPORAL';
?>


<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">

            <div class="panel panel-default carw">
                <div class="panel-heading">
                    <h1 class="app-page-title" style="margin-bottom: 0">Actualizar contraseña temporal</h1>

                </div>

                <div class="panel-body">

                    <?php $form = ActiveForm::begin([
                            'id' => 'formCambioClave',
                            'class' => 'settings-form',
                            'action' => ['cuenta/cambiar-clave-obligatoria'],
                            'method' => 'post',
                        'fieldConfig' => [
                            'template' => "{label}\n<div class=\"col-md-8\">{input}</div>\n<div class=\"col-md-8\">{error}</div>",
                            'labelOptions' => ['class' => 'col-md-4 control-label'],
                        ],
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
                    <div class="col-12 col-lg-12 btnpasword">
                        <div class="mb-3">
                            <?= Html::submitButton('Actualizar contraseña', [
                                'class' => 'btn app-btn-primary',
                                'id' => 'btnCambiarPassword'
                            ]) ?>
                        </div>

                    </div>

                    <?php ActiveForm::end(); ?>



                </div>
            </div>

        </div>
    </div>
</div>



<?php
$this->registerJsFile('https://cdn.jsdelivr.net/npm/sweetalert2@11', ['position' => \yii\web\View::POS_END]);
$this->registerJsFile(
    '@web/assets_b/js/changepassword.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);
?>

