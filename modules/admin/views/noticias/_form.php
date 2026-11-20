<?php

/**
 * Formulario de noticia. Imágenes: app\widgets\CampoImagen. Descripción: editor (EditorAsset).
 * Vista previa de la dirección y contador del resumen: huk-noticias.js.
 *
 * @var yii\web\View $this
 * @var app\models\Noticia $model
 * @var array $categorias  id => nombre
 * @var bool $puedeCategorias
 */

use app\models\Noticia;
use app\widgets\CampoImagen;
use kartik\form\ActiveForm;
use kartik\widgets\DatePicker;
use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\NoticiasAdminAsset::register($this);
?>

<?php if (!$categorias): ?>
    <div class="callout callout-warning">
        <p><i class="fa fa-exclamation-triangle"></i> Primero cree al menos una categoría
            <?= $puedeCategorias ? Html::a('en Blog → Categorías', ['/admin/noticias-categorias/index']) : '(pedíselo a quien administra el blog)' ?>.</p>
    </div>
<?php endif; ?>

<?php $form = ActiveForm::begin(['id' => 'noticia-form', 'options' => ['enctype' => 'multipart/form-data']]); ?>

<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-pencil-square-o"></i> Noticia</h3></div>
            <div class="box-body">
                <?= $form->field($model, 'titulo')->textInput(['maxlength' => 200, 'autofocus' => $model->isNewRecord]) ?>
                <?= $form->field($model, 'slug')->textInput(['maxlength' => 200, 'placeholder' => 'Vacío = se arma sola con el título'])
                    ->hint('Así queda la dirección: <code data-slug-vista data-titulo="#noticia-titulo" data-slug="#noticia-slug" data-base="'
                        . Html::encode(Url::to(['/noticias/index'], true) . '/') . '"></code>') ?>
                <?= $form->field($model, 'resumen')->textarea(['rows' => 3, 'maxlength' => Noticia::RESUMEN_MAX, 'data-contador' => Noticia::RESUMEN_MAX])
                    ->hint('Sale en la tarjeta del blog y es el texto que aparece al compartir la noticia (Facebook, WhatsApp, Google).') ?>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text-o"></i> Descripción completa <span class="text-danger">*</span></h3></div>
            <div class="box-body">
                <?= $form->field($model, 'descripcion', ['enableClientValidation' => false])->textarea([ // el editor llena el campo al guardar
                    'data-editor' => true,
                    'data-editor-alto' => 460,
                    'data-tinymce-base' => Url::to('@web/assets_b/lib/tinymce'),
                    'rows' => 14,
                ])->label(false) ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-sliders"></i> Datos</h3></div>
            <div class="box-body">
                <?= $form->field($model, 'idcategoria')->dropDownList($categorias, ['prompt' => 'Elija la categoría…'])
                    ->hint($puedeCategorias ? Html::a('<i class="fa fa-tags"></i> Administrar categorías', ['/admin/noticias-categorias/index'], ['target' => '_blank']) : '') ?>
                <?= $form->field($model, 'fecha')->widget(DatePicker::class, [
                    'language' => 'es',
                    'removeButton' => ['icon' => 'trash'],
                    'options' => ['placeholder' => 'Vacío = la fecha de hoy', 'autocomplete' => 'off'],
                    'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayHighlight' => true],
                ])->hint('Si no pone nada, se usa la fecha en que se crea la noticia.') ?>
                <?php if (!$model->isNewRecord): ?>
                    <p class="huk-ayuda"><i class="fa fa-info-circle"></i> Publicar u ocultar y marcar como destacada se hace desde el listado o la ficha.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php foreach (Noticia::IMAGENES as $campo => [$etiqueta, $medida]): ?>
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-picture-o"></i> <?= Html::encode($etiqueta) ?><?= $campo === 'imagen_portada' ? ' <span class="text-danger">*</span>' : '' ?></h3></div>
                <div class="box-body">
                    <?= $form->field($model, $campo)->widget(CampoImagen::class, ['url' => $model->url($model->$campo), 'medida' => $medida])->label(false) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="huk-form-acciones text-center">
    <?= Html::submitButton('<i class="fa fa-save"></i> ' . ($model->isNewRecord ? 'Publicar noticia' : 'Guardar cambios'), ['class' => 'btn btn-success btform']) ?>
    <?= Html::a('Cancelar', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->idnoticia], ['class' => 'btn btn-default btform']) ?>
</div>
<?php if ($model->isNewRecord): ?>
    <p class="text-center text-muted"><i class="fa fa-info-circle"></i> La galería de fotos se carga después de guardar, desde la ficha de la noticia.</p>
<?php endif; ?>

<?php ActiveForm::end(); ?>
