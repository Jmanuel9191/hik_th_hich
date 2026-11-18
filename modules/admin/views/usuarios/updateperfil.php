<?php
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
$this->title = 'ACTUALIZAR CUENTA';
$format = <<< SCRIPT
function format(state) {
if (!state.id) return state.text; // optgroup
return state.text;
}
SCRIPT;
$escape = new \yii\web\JsExpression("function(m) { return m; }");
$this->registerJs($format, \yii\web\View::POS_HEAD);
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">


        <div class="col-xs-12">
            <h1 class="app-page-title">Mi cuenta</h1>
        </div>
        
        
            <div class="col-12">
                <div class="app-card app-card-settings shadow-sm p-4">

                    <div class="app-card-body">


                        <?php $form = ActiveForm::begin([
                            'options' => ['enctype' => 'multipart/form-data'],
                            'class' => 'settings-form',

                            'type' => ActiveForm::TYPE_HORIZONTAL,
                            'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL
                            ] // important
                        ]); ?>

                        <div class="col-12 col-lg-6 col-md-6 col-sm-12 mspeo">
                            <div class="mb-3">
                                <?= $form->field($model, 'nombrecompleto')->textInput(['class' => 'form-control', 'placeholder' => '']) ?>

                                <?= $form->field($model, 'email')->textInput(['class' => 'form-control  ', 'placeholder' => ' ']) ?>

                                <?= $form->field($model, 'usuario')->textInput(['class' => 'form-control', 'placeholder' => '']) ?>


                                <?= $form->field($model, 'telefono')->textInput(['class' => 'form-control  ', 'placeholder' => '']) ?>


                                <?= $form->field($model, 'direccion')->textInput(['class' => 'form-control  ', 'placeholder' => '']) ?>


                                <?= $form->field($model, 'sexo')->widget(Select2::classname(), [
                                    'data' => [
                                        'Hombre' => 'Hombre',
                                        'Mujer' => 'Mujer',
                                    ],
                                    'options' => [
                                        'placeholder' => 'Seleccione Sexo...',
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true, // Permitir limpiar la selección
                                    ],
                                ]) ?>

                            </div>
                        </div>


                        <div class="col-12 col-lg-6 col-md-6 col-sm-12 ">
                            <div class="mb-3">
                               


                                <?= $form->field($model, 'fecha_nacimiento')->widget(
                                    \kartik\widgets\DatePicker::className(), [
                                    // modify template for custom rendering
                                    'language' => 'es',
                                    'removeButton' => false,
                                    'options' => ['class' => 'form-control  ', 'placeholder' => ''],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                        'todayBtn' => "linked",
                                        //'keyboardNavigation' => false,
                                        //'forceParse' => false
                                    ]
                                ]) ?>

                               <?= $form->field($model, 'cargo')->textInput(['class' => 'form-control  ', 'placeholder' => '']) ?>

                               <?= $form->field($model, 'ciudad')->textInput(['class' => 'form-control  ', 'placeholder' => '']) ?>

                            </div>
                        </div>

                        <!-- NUEVO: cada usuario puede subir/cambiar su
                             propio sello acá, en "Mi cuenta". Opcional. -->
                       
<div class="col-12 col-lg-12 col-md-12 col-sm-12 qadede">
                            <div class="mb-3">
                                <label class="control-label" style="display:block; font-weight:bold; margin-bottom:5px;">
                                    Sello / firma (opcional)
                                </label>
                                <?= $form->field($model, 'foto_sello', ['template' => "{input}\n{error}"])->widget(\sadovojav\cutter\Cutter::className(), [
                                    'cropperOptions' => [
                                        'aspectRatio' => 350 / 200,
                                    ],
                                ]) ?>
                                <p class="text-muted" style="font-size:12px; margin-top:5px;">
                                    Se usa para estampar tu sello/firma en los resultados de laboratorio.
                                </p>
                            </div>
                        </div>


                        <div class="form-group clsgua" >

                            <div class="col-12 col-sm-12 col-md-12 col-lg-12" align="center">

                                <div class="mb-3">
                                    <?= Html::submitButton($model->isNewRecord ? 'GUARDAR' : 'ACTUALIZAR',    ['class' => 'btn btn btn-success btform']
                                    ) ?>
                                </div>
                            </div>
                        </div>

                        <?php ActiveForm::end(); ?>

                        <?php
                        // Arregla el preview del widget Cutter: arma <img
                        // src="/imagen/..."> sin el baseUrl de la app (asume
                        // que el sitio vive en la raíz del dominio). Esto
                        // SOLO corrige lo que se ve — no toca el campo
                        // oculto que realmente se guarda al enviar el form.
                        $this->registerJs("
                            document.querySelectorAll('.image-cutter .preview-image').forEach(function(img) {
                                var src = img.getAttribute('src');
                                if (src && src.indexOf('/imagen/') === 0) {
                                    img.setAttribute('src', '" . Yii::$app->request->baseUrl . "' + src);
                                }
                            });
                        ");
                        ?>

                        <script>
                            // Definir la URL generada por Yii2 en una variable JavaScript global
                            var urlGetPaises = '<?= Url::to(['administrador/get-paises']) ?>';
                        </script>



                    </div><!--//app-card-body-->

                </div><!--//app-card-->
            </div>



        </div><!--//row-->


    </div><!--//container-fluid-->
</section><!--//app-content-->





<script src="../assets_b/js/paisciud.js"></script>


<style>
   .qadede .form-group.highlight-addon {
    margin-right: 0!important;
    margin-left: 0!important;
}
</style>