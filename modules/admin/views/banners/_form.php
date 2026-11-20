<?php

/**
 * Formulario de banner. Imágenes con app\widgets\CampoImagen (vista previa, arrastrar, Elegir / Quitar).
 * Página (Banners::UBICACIONES): cada una usa algunos textos (Inicio todos; Prefooter título, texto y botón;
 * páginas internas solo el título). huk-banners.js muestra solo los [data-campo] y la [data-ayuda-de] de la página elegida.
 *
 * @var yii\web\View $this
 * @var app\models\Banners $model
 */

use app\models\Banners;
use kartik\form\ActiveForm;
use kartik\widgets\DatePicker;
use app\widgets\CampoImagen;
use yii\helpers\Html;

\app\assets_b\BannersAdminAsset::register($this);

// Qué textos usa cada página (lo lee huk-banners.js) y atributos de cada grupo de campos
$camposPorPagina = array_map(fn($u) => $u['campos'], Banners::UBICACIONES);
$grupo = fn($campo) => ['data-campo' => $campo, 'hidden' => !$model->usa($campo)];
$fecha = [
    'language' => 'es',
    'removeButton' => ['icon' => 'trash'],
    'options' => ['placeholder' => 'Opcional', 'autocomplete' => 'off'],
    'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayHighlight' => true],
];
?>

<?php $form = ActiveForm::begin([
    'id' => 'banner-form',
    'options' => ['enctype' => 'multipart/form-data', 'class' => 'huk-banner-form'],
]); ?>

<div class="row">
    <div class="col-md-7">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-image"></i> Imágenes</h3></div>
            <div class="box-body">
                <?= $form->field($model, 'imagen')->widget(CampoImagen::class, ['url' => Banners::url($model->imagen), 'medida' => Banners::IMAGENES['imagen'][1]])
                    ->label('Imagen para computadora <span class="text-danger">*</span>') ?>

                <?= $form->field($model, 'imagen_movil')->widget(CampoImagen::class, ['url' => Banners::url($model->imagen_movil), 'medida' => Banners::IMAGENES['imagen_movil'][1]])
                    ->hint('Opcional. Más alta que ancha, para que en el celular no se vea recortada. Si no la carga, se usa la de computadora.') ?>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-map-signs"></i> Página</h3></div>
            <div class="box-body">
                <?= $form->field($model, 'ubicacion')->dropDownList(
                    array_map(fn($u) => $u['nombre'], Banners::UBICACIONES),
                    ['data-banner-ubicacion' => '', 'data-campos' => json_encode($camposPorPagina)]
                )->hint('Dónde se muestra. Inicio: carrusel grande. Las demás: se ve el primero vigente de la lista.') ?>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-font"></i> Textos</h3></div>
            <div class="box-body">
                <?php foreach (array_keys(Banners::UBICACIONES) as $id): ?>
                    <?= Html::tag('p', Html::encode(Banners::ayuda($id)), ['class' => 'text-muted', 'data-ayuda-de' => $id, 'hidden' => $id !== $model->ubicacion]) ?>
                <?php endforeach; ?>
                <?= Html::beginTag('div', $grupo('antetitulo')) ?>
                    <?= $form->field($model, 'antetitulo')->textInput(['maxlength' => 120, 'placeholder' => 'Ej.: HUK-THE-ICHI GLOBAL GROUP · ONE VISION'])
                        ->hint('Texto corto en dorado, arriba del título (sale en mayúsculas).') ?>
                <?= Html::endTag('div') ?>
                <?= $form->field($model, 'titulo')->textInput(['maxlength' => 150]) ?>
                <?= Html::beginTag('div', $grupo('texto')) ?>
                    <?= $form->field($model, 'texto')->textarea(['rows' => 3, 'maxlength' => 400]) ?>
                <?= Html::endTag('div') ?>
                <?= Html::beginTag('div', $grupo('boton')) ?>
                    <div class="row">
                        <div class="col-sm-5"><?= $form->field($model, 'boton_texto')->textInput(['maxlength' => 60, 'placeholder' => 'Ej.: Contáctenos']) ?></div>
                        <div class="col-sm-7"><?= $form->field($model, 'boton_url')->textInput(['maxlength' => 500, 'placeholder' => '/contacto o https://…']) ?></div>
                    </div>
                    <p class="huk-ayuda">Un enlace de otra página se abre en otra pestaña.</p>
                <?= Html::endTag('div') ?>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-calendar"></i> Fechas (opcional)</h3></div>
            <div class="box-body">
                <p class="text-muted">Para que se muestre solo un tiempo (ej. una promo). Vacías = siempre.</p>
                <div class="row">
                    <div class="col-sm-6"><?= $form->field($model, 'fecha_inicio')->widget(DatePicker::class, $fecha) ?></div>
                    <div class="col-sm-6"><?= $form->field($model, 'fecha_fin')->widget(DatePicker::class, $fecha) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="huk-form-acciones text-center">
    <?= Html::submitButton('<i class="fa fa-save"></i> ' . ($model->isNewRecord ? 'Crear banner' : 'Guardar cambios'), ['class' => 'btn btn-success btform']) ?>
    <?= Html::a('Cancelar', ['index', 'tab' => $model->ubicacion], ['class' => 'btn btn-default btform']) ?>
</div>

<?php ActiveForm::end(); ?>
