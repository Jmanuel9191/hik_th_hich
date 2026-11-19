<?php

/**
 * Solicitud para ser inversor (web). Los mismos campos que carga el panel, más contraseña, términos y documento.
 * Persona / Empresa y ver contraseña: huk-solicitud.js.
 *
 * @var yii\web\View $this
 * @var app\models\SolicitudInversorForm $model
 * @var array $ecosistemas id => "01 · Nombre"
 */

use app\models\Inversor;
use app\models\SolicitudInversorForm;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

\app\assets_b\CuentaAsset::register($this);

$campo = ['options' => ['class' => 'mb-3'], 'labelOptions' => ['class' => 'form-label'], 'inputOptions' => ['class' => 'form-control'],
    'errorOptions' => ['class' => 'invalid-feedback d-block'], 'hintOptions' => ['class' => 'form-text']];
$select = ['inputOptions' => ['class' => 'form-select']];
$clave = fn($id) => ['template' => '{label}<div class="huk-clave">{input}<button type="button" data-ver-clave="#' . $id
    . '" aria-label="Ver contraseña"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></div>{hint}{error}'];
?>

<section class="huk-cuenta">
    <div class="container">
        <div class="huk-cuenta-cab">
            <span class="huk-cuenta-eyebrow">Inversores y socios</span>
            <h1>Quiero ser socio</h1>
            <p>Completá tu solicitud. Nuestro equipo la revisa, se comunica con vos y, una vez aprobada, accedés a los proyectos de tus áreas de interés.</p>
        </div>

        <div class="huk-tarjeta-form">
            <?= $this->render('../_aviso') ?>
            <?php $form = ActiveForm::begin([
                'id' => 'registro-form',
                'options' => ['enctype' => 'multipart/form-data', 'class' => 'huk-form'],
                'fieldConfig' => $campo,
            ]); ?>
            <datalist id="lista-paises"><?php foreach (SolicitudInversorForm::PAISES as $p): ?><option value="<?= Html::encode($p) ?>"><?php endforeach; ?></datalist>

            <div class="huk-seccion-form">
                <h2><span class="huk-paso">1</span> Quién sos</h2>
                <?= $form->field($model, 'tipo')->radioList(Inversor::TIPOS, [
                    'class' => 'huk-opciones',
                    'item' => fn($i, $label, $name, $checked, $value) => Html::label(
                        Html::radio($name, $checked, ['value' => $value, 'data-inversor-tipo' => true])
                        . '<i class="fa-solid ' . ($value === 'empresa' ? 'fa-building' : 'fa-user') . '" aria-hidden="true"></i> ' . Html::encode($label),
                        null, ['class' => 'huk-opcion' . ($checked ? ' is-activo' : '')]),
                ]) ?>
                <div class="row" data-solo-empresa>
                    <div class="col-md-7"><?= $form->field($model, 'razon_social')->textInput(['maxlength' => 200, 'autocomplete' => 'organization']) ?></div>
                    <div class="col-md-5"><?= $form->field($model, 'nit')->textInput(['maxlength' => 40]) ?></div>
                </div>
                <div class="row">
                    <div class="col-md-7"><?= $form->field($model, 'nombre')->textInput(['maxlength' => 100, 'autocomplete' => 'name'])
                        ->label(null, ['class' => 'form-label', 'data-etiqueta-persona' => 'Nombre completo', 'data-etiqueta-empresa' => 'Nombre completo del representante']) ?></div>
                    <div class="col-md-5" data-solo-empresa><?= $form->field($model, 'cargo')->textInput(['maxlength' => 100, 'autocomplete' => 'organization-title']) ?></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><?= $form->field($model, 'documento_tipo', $select)->dropDownList(Inversor::DOCUMENTOS) ?></div>
                    <div class="col-md-8"><?= $form->field($model, 'documento_numero')->textInput(['maxlength' => 40]) ?></div>
                </div>
                <?= $form->field($model, 'documento')->fileInput(['class' => 'form-control', 'accept' => '.jpg,.jpeg,.png,.webp,.pdf'])
                    ->hint('Una foto clara o un PDF (frente; si tiene datos atrás, ambos lados). JPG, PNG, WEBP o PDF, hasta ' . Inversor::MAX_DOCUMENTO_MB . ' MB. Solo lo ve nuestro equipo.') ?>
                <div class="row">
                    <div class="col-md-6"><?= $form->field($model, 'pais')->textInput(['maxlength' => 50, 'list' => 'lista-paises', 'autocomplete' => 'country-name']) ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'ciudad')->textInput(['maxlength' => 50, 'autocomplete' => 'address-level2']) ?></div>
                </div>
                <div class="row">
                    <div class="col-md-7"><?= $form->field($model, 'email')->textInput(['maxlength' => 100, 'type' => 'email', 'autocomplete' => 'email'])
                        ->hint('Con este correo vas a ingresar. Te mandamos un enlace para confirmarlo.') ?></div>
                    <div class="col-md-5"><?= $form->field($model, 'celular')->textInput(['maxlength' => 30, 'type' => 'tel', 'autocomplete' => 'tel', 'placeholder' => '+591 70000000']) ?></div>
                </div>
                <div data-solo-empresa><?= $form->field($model, 'sitio_web')->textInput(['maxlength' => 255, 'placeholder' => 'https://']) ?></div>
            </div>

            <div class="huk-seccion-form">
                <h2><span class="huk-paso">2</span> Qué buscás</h2>
                <?= $form->field($model, 'intereses')->checkboxList($ecosistemas, [
                    'class' => 'huk-areas',
                    'item' => fn($i, $label, $name, $checked, $value) => Html::label(Html::checkbox($name, $checked, ['value' => $value]) . ' ' . Html::encode(preg_replace('/^\d+ · /', '', $label))),
                ])->hint('Podés elegir varias.') ?>
                <div class="row">
                    <div class="col-md-6"><?= $form->field($model, 'monto_minimo')->input('number', ['min' => 1, 'step' => 'any', 'inputmode' => 'decimal', 'placeholder' => 'Ej: 50000'])
                        ->hint('En dólares, sin puntos ni comas.') ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'monto_maximo')->input('number', ['min' => 1, 'step' => 'any', 'inputmode' => 'decimal', 'placeholder' => 'Ej: 250000'])
                        ->hint('Si es un monto fijo, poné el mismo en los dos.') ?></div>
                </div>
                <?= $form->field($model, 'plazo_preferido', $select)->dropDownList(Inversor::PLAZOS, ['prompt' => 'Elegí…']) ?>
                <div class="row">
                    <div class="col-md-6"><?= $form->field($model, 'participacion_preferida', $select)->dropDownList(Inversor::PARTICIPACIONES) ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'como_nos_conocio')->textInput(['maxlength' => 150]) ?></div>
                </div>
                <?= $form->field($model, 'mensaje')->textarea(['rows' => 3, 'maxlength' => 1000, 'placeholder' => 'Contanos brevemente qué tipo de proyectos te interesan.']) ?>
            </div>

            <div class="huk-seccion-form">
                <h2><span class="huk-paso">3</span> Tu cuenta</h2>
                <div class="row">
                    <div class="col-md-6"><?= $form->field($model, 'contrasena', $clave('solicitudinversorform-contrasena'))->passwordInput(['maxlength' => 72, 'autocomplete' => 'new-password'])
                        ->hint('Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.') ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'contrasena2', $clave('solicitudinversorform-contrasena2'))->passwordInput(['maxlength' => 72, 'autocomplete' => 'new-password']) ?></div>
                </div>
                <div class="huk-hp" aria-hidden="true"><?= $form->field($model, 'sitio_hp')->textInput(['tabindex' => -1, 'autocomplete' => 'off']) ?></div>
                <div class="huk-terminos">
                    <?= $form->field($model, 'terminos', ['options' => ['class' => 'mb-0']])->checkbox(['uncheck' => '0', 'value' => '1']) ?>
                    <details>
                        <summary>Leer los términos y la política de privacidad</summary>
                        <p>Usamos tus datos solo para evaluar tu perfil como inversor, comunicarnos con vos y darte acceso a la plataforma.
                            No los vendemos ni los compartimos con terceros sin tu autorización. Tu documento de identidad solo lo ve el equipo de HUK.</p>
                        <p>Podés pedir que corrijamos o borremos tus datos escribiéndonos a los medios de contacto de la web.
                            El acceso a los proyectos requiere aceptar, además, un acuerdo de confidencialidad.</p>
                    </details>
                </div>
            </div>

            <div class="text-center">
                <?= Html::submitButton('<i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Enviar solicitud', ['class' => 'huk-btn-oro']) ?>
                <p class="huk-form-pie">¿Ya tenés cuenta? <?= Html::a('Ingresá acá', ['/cuenta/acceso/ingresar']) ?></p>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</section>
