<?php

/**
 * Editar mis datos. El tipo de usuario, el estado y la contraseña no se cambian desde acá.
 * País → Ciudad: huk-usuarios.js.
 *
 * @var yii\web\View $this
 * @var app\models\Usuarios $model
 */

use app\components\Ubicaciones;
use app\models\Usuarios;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\helpers\Html;

\app\assets_b\UsuariosAdminAsset::register($this);

$this->title = 'Editar mis datos';
?>

<section class="content-header">
    <h1><i class="fa fa-user-circle"></i> Editar mis datos</h1>
</section>

<section class="content">
    <div class="box box-primary">
        <?php $form = ActiveForm::begin([
            'id' => 'perfil-form',
            'type' => ActiveForm::TYPE_HORIZONTAL,
            'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL],
            'options' => ['class' => 'huk-form-usuario'],
        ]); ?>

        <div class="row">
            <div class="col-12 col-lg-6 col-md-6 col-sm-12">
                <?= $form->field($model, 'nombrecompleto')->textInput(['maxlength' => 100]) ?>
                <?= $form->field($model, 'email')->textInput(['maxlength' => 100, 'type' => 'email']) ?>
                <?= $form->field($model, 'usuario')->textInput(['maxlength' => 100, 'autocomplete' => 'off']) ?>
                <?= $form->field($model, 'telefono')->textInput(['maxlength' => 15]) ?>
                <?= $form->field($model, 'movil')->textInput(['maxlength' => 15])
                    ->hint('Solo números, con código de país. Ej.: 59176303932') ?>
            </div>
            <div class="col-12 col-lg-6 col-md-6 col-sm-12">
                <?= $form->field($model, 'pais_operacion')->widget(Select2::class, [
                    'data' => Usuarios::PAISES,
                    'language' => 'es',
                    'options' => ['placeholder' => 'Seleccione el país', 'data-pais-select' => true, 'data-ciudades' => Ubicaciones::CIUDADES],
                    'pluginOptions' => ['allowClear' => true],
                ]) ?>
                <?= $form->field($model, 'ciudad')->widget(Select2::class, [
                    'data' => Ubicaciones::ciudadesDe($model->pais_operacion, $model->ciudad),
                    'language' => 'es',
                    'options' => ['placeholder' => 'Primero elegí el país', 'data-ciudad-select' => true],
                    'pluginOptions' => ['allowClear' => true, 'tags' => true],
                ])->hint('Si tu ciudad no aparece, escribila y presioná Enter.') ?>
                <?= $form->field($model, 'direccion')->textInput(['maxlength' => 100]) ?>
                <?= $form->field($model, 'fecha_nacimiento')->widget(DatePicker::class, [
                    'language' => 'es',
                    'removeButton' => false,
                    'options' => ['placeholder' => 'Opcional'],
                    'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayBtn' => 'linked'],
                ]) ?>
            </div>
        </div>

        <div class="huk-form-acciones text-center">
            <?= Html::submitButton('<i class="fa fa-save"></i> Guardar', ['class' => 'btn btn-success btform']) ?>
            <?= Html::a('Cancelar', ['cuenta'], ['class' => 'btn btn-default btform']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</section>
