<?php

/**
 * Formulario de usuario del panel (crear y editar; _form2.php usa este mismo).
 * - Contraseña: solo al crear (para cambiarla después: botón "Contraseña" en Ver usuario).
 * - "Ecosistemas a cargo": aparece solo si el tipo es Gerente de área (huk-usuarios.js, data-solo-rol).
 * - Ojo de contraseña: huk-admin.js (data-ver-clave).
 *
 * @var yii\web\View $this
 * @var app\models\Usuarios $model
 * @var array $roles  idrol => nombre
 */

use app\components\Rol;
use app\components\Ubicaciones;
use app\models\Ecosistema;
use app\models\Usuarios;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\helpers\Html;

\app\assets_b\UsuariosAdminAsset::register($this);

$esGerente = (int) $model->idrol === Rol::GERENTE;
?>

<?php $form = ActiveForm::begin([
    'id' => 'usuario-form',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL],
    'options' => ['class' => 'huk-form-usuario'],
]); ?>

<div class="row">
    <div class="col-12 col-lg-6 col-md-6 col-sm-12">
        <?= $form->field($model, 'nombrecompleto')->textInput(['maxlength' => 100]) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => 100, 'type' => 'email']) ?>
        <?= $form->field($model, 'usuario')->textInput(['maxlength' => 100, 'autocomplete' => 'off']) ?>

        <?php if ($model->isNewRecord): ?>
            <?= $form->field($model, 'contrasena', [
                'addon' => [
                    'groupOptions' => ['class' => 'huk-adm-field'], // huk-admin.js busca el input aquí
                    'append' => [
                        'content' => '<button type="button" class="btn btn-default" data-ver-clave aria-label="Mostrar contraseña">'
                            . '<i class="fa fa-eye" aria-hidden="true"></i></button>',
                        'asButton' => true,
                    ],
                ],
            ])->passwordInput(['autocomplete' => 'new-password'])
              ->hint('Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.') ?>
        <?php endif; ?>

        <?= $form->field($model, 'telefono')->textInput(['maxlength' => 15]) ?>
        <?= $form->field($model, 'movil')->textInput(['maxlength' => 15])
            ->hint('Solo números, con código de país. Ej.: 59176303932') ?>
        <?= $form->field($model, 'direccion')->textInput(['maxlength' => 100]) ?>
    </div>

    <div class="col-12 col-lg-6 col-md-6 col-sm-12">
        <?= $form->field($model, 'idrol')->widget(Select2::class, [
            'data' => $roles,
            'language' => 'es',
            'options' => ['placeholder' => 'Seleccione el tipo de usuario', 'data-rol-select' => true],
            'pluginOptions' => ['allowClear' => true],
        ]) ?>

        <div data-solo-rol="<?= Rol::GERENTE ?>" class="<?= $esGerente ? '' : 'hidden' ?>">
            <?= $form->field($model, 'ecosistemasIds')->widget(Select2::class, [
                'data' => Ecosistema::lista(false),
                'language' => 'es',
                'options' => ['placeholder' => 'Elegí uno o varios ecosistemas', 'multiple' => true],
                'pluginOptions' => ['allowClear' => true, 'closeOnSelect' => false, 'width' => '100%'],
            ])->hint('El gerente solo ve y edita los ecosistemas que elijas acá.') ?>
        </div>

        <?= $form->field($model, 'pais_operacion')->widget(Select2::class, [
            'data' => Usuarios::PAISES,
            'language' => 'es',
            'options' => [
                'placeholder' => 'Seleccione el país',
                'data-pais-select' => true,
                'data-ciudades' => Ubicaciones::CIUDADES, // huk-usuarios.js arma la lista de ciudades
            ],
            'pluginOptions' => ['allowClear' => true],
        ]) ?>

        <?= $form->field($model, 'ciudad')->widget(Select2::class, [
            'data' => Ubicaciones::ciudadesDe($model->pais_operacion, $model->ciudad),
            'language' => 'es',
            'options' => ['placeholder' => 'Primero elegí el país', 'data-ciudad-select' => true],
            'pluginOptions' => ['allowClear' => true, 'tags' => true], // tags: deja escribir una ciudad que no está
        ])->hint('Si tu ciudad no aparece, escribila y presioná Enter.') ?>

        <?= $form->field($model, 'cargo')->textInput(['maxlength' => 500]) ?>

        <?= $form->field($model, 'fecha_nacimiento')->widget(DatePicker::class, [
            'language' => 'es',
            'removeButton' => false,
            'options' => ['placeholder' => 'Opcional'],
            'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy-mm-dd', 'todayBtn' => 'linked'],
        ]) ?>
    </div>
</div>

<div class="huk-form-acciones text-center">
    <?= Html::submitButton($model->isNewRecord ? 'GUARDAR' : 'ACTUALIZAR', ['class' => 'btn btn-success btform']) ?>
    <?= Html::a('Cancelar', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->idusuario], ['class' => 'btn btn-default btform']) ?>
</div>

<?php ActiveForm::end(); ?>
