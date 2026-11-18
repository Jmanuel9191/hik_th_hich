<?php
use dosamigos\ckeditor\CKEditor;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\helpers\Html;
use kartik\widgets\FileInput;
use yii\helpers\Url;

$this->title = 'Recurso: ' . $recurso->titulo;
?>





    <style> body { overflow: hidden!important; } .app-footer{ display: none!important;}</style>



        <div class=" g-3 mb-4 align-items-center justify-content-between">
         <div class="card chathistorialmaterial">
            <div class="row g-0">
                <div class="col-12 col-lg-12 col-xl-12">
                    <div class="border-bottom d-lg-block cajadetitulohistorial">
                        <div class="d-flex align-items-center ">                            <div class="d-flex flex-wrap w-100">
                                <div class="w-50 pl-3 w100p">
                                    <p ><strong>Recurso:</strong> <?= $recurso->titulo ?></p>
                                    <p class="d-none"><strong>Tarea:</strong> <?= $recurso->asignacion->tareas->titulo ?></p>

                                </div>
                                <div class="w-50 pl-3 w100p">
                                    <?php if (
                                        $recurso->asignacion->proyecto->idcliente == Yii::$app->user->identity->id &&
                                        in_array($recurso->estado, [1, 3])
                                    ): ?>
                                        <div class="rigkes">
                                            <button class="btn btn-success btn-lg mr-1 px-3 btn-aprobar" data-id="<?= $recurso->idrecurso ?>">Aprobar</button>
                                            <button class="btn btn-danger btn-lg mr-1 px-3 d-md-inline-block btn-rechazar" data-id="<?= $recurso->idrecurso ?>">
                                                Rechazar
                                            </button>
                                        </div>
                                    <?php endif; ?>









                                </div>
                            </div>

                        </div>
                     </div>



                    <div class="position-relative chat-wrapper">
                        <div class="chat-messages " id="contenedor-chat" data-idrecurso="<?= $recurso->idrecurso ?>" data-ultimo-id="<?= $ultimoId ?? 0 ?>">


                            <?php foreach ($dataProvider->getModels() as $mensaje): ?>
                                <?= $this->render('widgets/_mensaje_historial', ['model' => $mensaje]) ?>

                            <?php endforeach; ?>

                        </div>
                    </div>





                    <div class="flex-grow-0 py-3 border-top cajainputsend">
                        <div class="d-flex">

                        <?php $form = ActiveForm::begin([
                            'id' => 'form-mensaje-recurso',
                            'options' => ['enctype' => 'multipart/form-data'],
                            'type' => ActiveForm::TYPE_HORIZONTAL,
                            'enableClientValidation' => true,
                            'enableAjaxValidation' => false,
                            'action' => ['proyectos/enviar-mensaje'], // <- tu ruta
                            'formConfig' => ['labelSpan' => 0, 'deviceSize' => ActiveForm::SIZE_SMALL]
                        ]); ?>

                            <div id="contenedor-archivos" style="display: none;">


                                <?= $form->field($model, 'archivo')->label(false)->widget(\kartik\file\FileInput::classname(), [
                                    'options' => [
                                        'multiple' => true,
                                        'accept' => '.jpg,.jpeg,.png,.gif,.pdf,.docx,.xlsx,.zip,.ppt,.pptx,.txt,.rar',
                                        'id' => 'input-archivo'
                                    ],


                                    'pluginOptions' => [
                                        'showUpload' => false,
                                        'showCancel' => false,
                                        'removeLabel' => 'Limpiar',
                                        'debug' => true,
                                        'allowedFileExtensions' => ['jpg', 'jpeg','png', 'gif','pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar'],
                                        'language' => 'es',
                                        'browseLabel' => 'Buscar',
                                        'previewFileType' => 'any', // 👈 muestra preview de cualquier archivo compatible
                                        'showPreview' => true,
                                        'overwriteInitial' => false,
                                        'showCaption' => true,
                                        'initialPreviewAsData' => true,
                                        'previewFileIcon' => '<i class="fas fa-file"></i>',
                                        'uploadAsync' => false, // 👈 esto es clave
                                        'fileActionSettings' => [
                                            'showRemove' => true, // 👈 esto habilita el botón de basurero
                                            'showZoom' => false,
                                            'removeIcon' => '<i class="fa fa-trash"></i>',
                                        ],
                                    ]
                                ]) ?>
                            </div>



                            <div id="contenedor-progreso" class="d-flex align-items-center" style="visibility: hidden; ">
                                <div class="progress flex-grow-1" style="height: 15px;">
                                    <div id="barra-progreso" class="progress-bar progress-bar-striped progress-bar-animated"
                                         role="progressbar" style="width: 0%">0%</div>
                                </div>
                                <span id="texto-progreso" class="ms-2" style="white-space: nowrap;">Enviando...</span>
                            </div>




                            <div class="input-group formularioenviar">


                            <?= $form->field($model, 'comentario', ['template' => '{input}'])->textInput([
                                'placeholder' => 'Escribe tu mensaje...',
                                'class' => 'form-control btncomentar',
                                'id' => 'input-comentario'
                            ]) ?>
                            <?= Html::activeHiddenInput($model, 'idrecurso', ['value' => $recurso->idrecurso]) ?>




                            <div class="lateaeals">
                            <button type="button" id="btn-mostrar-archivos" class="btn btn-secondary btnclps">
                                <i class="fa-solid fa-paperclip"></i>
                            </button>

                            <?= Html::submitButton('Enviar', [
                                'class' => 'btn btn-primary btnenviars',
                                'id' => 'btn-enviar',
                                'disabled' => true
                            ]) ?>

                            </div>




                        </div>

                        <?php ActiveForm::end(); ?>








                     </div>
                    </div>









                </div>
            </div>
        </div>
        </div>
<?php
$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/sweetalert2@11',
    ['position' => \yii\web\View::POS_HEAD]
);
$this->registerJsFile(
    '@web/assets_b/js/chatdeenviar.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);


?>
