<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;
$this->title = 'ACTUALIZAR PASSWORD';
?>
<!-- Main content -->
<section class="content">
            <div class="box">

    <div class="row">
        <div class="col-xs-12">


        <div class="col-xs-12">
            <h1 class="app-page-title">Mi cuenta</h1>
        </div>
        
        
            <div class="col-12">
                <div class="app-card app-card-settings shadow-sm p-4">

                    <div class="app-card-body">



                            <?php $form = ActiveForm::begin([
                                'id' => 'frmContact',
                                'class' => 'settings-form',
                                'action' => ['usuarios/updatepasswordperfil'],
                                'method' => 'post'
                            ]); ?>
                        <div class="col-12 col-lg-12">


                       


    <div class="mb-3 laelsle">
        <?= $form->field($model, 'currentPassword', [
            'template' => '
            {label}
            <div class="input-group desl">
                {input}
                <div class="input-group-append">
                    <button type="button" class="btn btn-secondary toggle-pass">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
            </div>
            {error}',
        ])->passwordInput(['class' => 'form-control', 'placeholder' => '']) ?>
    </div>
</div>

<div class="col-12 col-lg-12">
    <div class="mb-3 laelsle">
        <?= $form->field($model, 'newPassword', [
            'template' => '
            {label}
            <div class="input-group desl">
                {input}
                <div class="input-group-append">
                    <button type="button" class="btn btn-secondary toggle-pass">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
            </div>
            {error}',
        ])->passwordInput(['class' => 'form-control', 'placeholder' => '']) ?>
    </div>
</div>

<div class="col-12 col-lg-12">
    <div class="mb-3 laelsle">
        <?= $form->field($model, 'confirmPassword', [
            'template' => '
            {label}
            <div class="input-group desl">
                {input}
                <div class="input-group-append">
                    <button type="button" class="btn btn-secondary toggle-pass">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
            </div>
            {error}',
        ])->passwordInput(['class' => 'form-control', 'placeholder' => '']) ?>
    </div>
</div>

                    <div class="col-12 col-lg-12  " >
                            <div class="mb-3">

                        <?= Html::submitButton('Actualizar', ['class' => 'btn btn-success btform', 'name' => 'contact-button']) ?>

                     </div>
                        </div>


                        <?php ActiveForm::end(); ?>


                  


                    </div><!--//app-card-body-->

                </div><!--//app-card-->
            </div>


            
        </div><!--//row-->


    </div><!--//container-fluid-->
</section><!--//app-content-->



<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-pass').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = this.closest('.input-group').querySelector('input');
            var icon  = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
</script>


