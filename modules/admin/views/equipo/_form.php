<?php

/**
 * Formulario de miembro del equipo. Foto: app\widgets\CampoImagen. Biografía: editor (EditorAsset).
 *
 * @var yii\web\View $this
 * @var app\models\Equipo $model
 */

use app\models\Equipo;
use app\widgets\CampoImagen;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\EquipoAdminAsset::register($this);

$icono = fn($fa) => ['addon' => ['groupOptions' => ['class' => 'huk-adm-field'], 'prepend' => ['content' => '<i class="fa fa-' . $fa . '" aria-hidden="true"></i>']]];
?>

<?php $form = ActiveForm::begin([
    'id' => 'equipo-form',
    'options' => ['enctype' => 'multipart/form-data', 'class' => 'huk-equipo-form'],
]); ?>

<div class="row">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-camera"></i> Foto</h3></div>
            <div class="box-body">
                <?= $form->field($model, 'foto')->widget(CampoImagen::class, ['url' => Equipo::url($model->foto), 'medida' => Equipo::MEDIDA_FOTO])
                    ->label(false)
                    ->hint('Recomendado: foto de frente, con buena luz y fondo simple.') ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-id-card-o"></i> Datos</h3></div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-6"><?= $form->field($model, 'nombre')->textInput(['maxlength' => 150]) ?></div>
                    <div class="col-sm-6"><?= $form->field($model, 'puesto')->textInput(['maxlength' => 150, 'placeholder' => 'Ej.: Gerente de Energía']) ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-6"><?= $form->field($model, 'telefono', $icono('phone'))->textInput(['maxlength' => 50]) ?></div>
                    <div class="col-sm-6"><?= $form->field($model, 'email', $icono('envelope-o'))->textInput(['maxlength' => 150, 'type' => 'email']) ?></div>
                </div>
                <p class="huk-aviso-contacto">
                    <i class="fa fa-info-circle"></i> Opcionales. Se muestran en la web: recomendamos colocar el
                    <strong>teléfono de la empresa</strong> y el <strong>correo corporativo</strong>, no los personales.
                </p>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-share-alt"></i> Redes sociales (opcional)</h3></div>
            <div class="box-body">
                <div class="row">
                    <?php foreach (Equipo::REDES as $campo => [, $fa]): ?>
                        <div class="col-sm-6"><?= $form->field($model, $campo, $icono($fa))->textInput(['maxlength' => 255, 'placeholder' => 'https://']) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text-o"></i> Biografía (opcional)</h3></div>
    <div class="box-body">
        <?= $form->field($model, 'biografia')->textarea([
            'data-editor' => true,
            'data-editor-alto' => 320,
            'data-tinymce-base' => Url::to('@web/assets_b/lib/tinymce'),
            'rows' => 8,
        ])->label(false)->hint('Si la completás, en la web aparece el botón "Ver más" con este texto.') ?>
    </div>
</div>

<div class="huk-form-acciones text-center">
    <?= Html::submitButton('<i class="fa fa-save"></i> ' . ($model->isNewRecord ? 'Agregar al equipo' : 'Guardar cambios'), ['class' => 'btn btn-success btform']) ?>
    <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-default btform']) ?>
</div>

<?php ActiveForm::end(); ?>
