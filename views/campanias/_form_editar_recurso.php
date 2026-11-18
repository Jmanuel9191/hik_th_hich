<?php

use dosamigos\ckeditor\CKEditor;
use kartik\form\ActiveForm;
use kartik\widgets\DatePicker;
use yii\helpers\Html;
use kartik\select2\Select2;
?>
<style>
    .select2-dropdown.select2-dropdown--below {
        margin-top: 1px;
        z-index: 9999999!important;
    }
</style>
<div class="modal" id="modalEditarRecurso<?= $modelRecurso->idrecurso?>" tabindex="-1">
    <div class="modal-dialog modal-lg a">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center">Editar recurso para: <?= $tarea ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>
            <div class="modal-body desproyecto">

                <div class="row g-4 settings-section">
                    <div class="col-12">
                        <div class="app-card app-card-settings shadow-sm p-4">
                            <div class="app-card-body">


                                <?php $form = ActiveForm::begin([
                                    'options' => ['enctype' => 'multipart/form-data'],
                                    'action' => ['campanias/editar-recurso', 'id' => $modelRecurso->idrecurso],
                                    'enableAjaxValidation' => true,
                                    'validationUrl' => ['campanias/validar-recurso', 'idasignartarea' => $idasignartarea],

                                    'method' => 'post',
                                    'type' => ActiveForm::TYPE_HORIZONTAL,
                                    'formConfig' => ['labelSpan' => 2, 'deviceSize' => ActiveForm::SIZE_SMALL],
                                ]); ?>



                                <div class="col-12">
                    <div class="mb-3">
                        <?= $form->field($modelRecurso, 'titulo')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-3">

                        <?= $form->field($modelRecurso, 'descripcion')->textArea() ?>
                    </div>
                </div>

                <div class="col-12">
                    <div class="mb-3">
                        <?= $form->field($modelRecurso, 'fechaentrega')->widget(DatePicker::classname(), [
                            'language' => 'es',
                            'options' => [
                                'id' => 'recursos-editar-' . $modelRecurso->idrecurso,
                                'data-fechafin' => $fechafinTarea,
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayBtn' => 'linked',
                            ]
                        ]) ?>


                    </div>
                </div>



                                <?php if ($asignacion->tarea->tipo->alias === 'revision'): ?>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <?= $form->field($modelRecurso, 'idcanal', [
                                                'inputOptions' => ['id' => 'recursos-idcanal-' . $idasignartarea]
                                            ])->widget(Select2::classname(), [
                                                'data' => \app\models\CanalesMarketing::getListaCanales(),
                                                'language' => 'es',
                                                'options' => [
                                                    'placeholder' => 'Seleccionar el canal...',
                                                ],
                                                'pluginOptions' => [
                                                    'allowClear' => true,
                                                ],
                                            ]); ?>  </div>
                                    </div>
                                <?php endif; ?>


                <div class="form-group text-end">
                    <?= Html::submitButton('EDITAR', ['class' => 'btn btn-success btnmodals']) ?>

                </div>
                <?php ActiveForm::end(); ?>

                            </div><!--//app-card-body-->

                        </div><!--//app-card-->
                    </div>
                </div><!--//row-->

            </div>

        </div>
    </div>
</div><!-- End Basic Modal-->



