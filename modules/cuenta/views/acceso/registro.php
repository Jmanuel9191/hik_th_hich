<?php

/**
 * Solicitud para ser inversor (web). Los mismos campos que carga el panel, más contraseña, términos y documento.
 * Arriba: banner de Panel → Banners → "Quiero ser socio". Después, la bienvenida (sus datos se verifican y cómo
 * comunicarse: _contacto) y el formulario en 5 secciones numeradas.
 * Persona / Empresa, ver contraseña y vista previa del documento: huk-solicitud.js. Estilos: huk-cuenta.css.
 *
 * @var yii\web\View $this
 * @var app\models\SolicitudInversorForm $model
 * @var array $ecosistemas id => "01 · Nombre"
 * @var app\models\Banners|null $encabezado
 * @var array $contacto [[ícono, texto, valor, url], …] (AccesoController::contacto)
 * @var array $monedas id => ['codigo', 'simbolo', 'nombre'] (Configuración → Monedas, activas)
 * @var app\models\TerminosVersion|null $terminos versión vigente (Configuración → Términos y privacidad): se lee en un modal
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
// Encabezado de cada sección: número, ícono, título y para qué se pide
$seccion = fn(int $n, string $icono, string $titulo, string $texto) => '<header class="huk-seccion-cab"><span class="huk-paso">' . $n . '</span>'
    . '<i class="fa-solid ' . $icono . '" aria-hidden="true"></i><div><h2>' . Html::encode($titulo) . '</h2><p>' . Html::encode($texto) . '</p></div></header>';
$formatos = '.' . implode(',.', array_keys(Inversor::FORMATOS_DOCUMENTO));
?>
<?= $this->render('//site/widgets/encabezado', [
    'banner' => $encabezado, 'titulo' => 'Quiero ser socio', 'antetitulo' => 'Inversores y socios',
    'migas' => [['label' => 'Inicio', 'url' => ['/site/index']], ['label' => 'Acceso inversores', 'url' => ['/cuenta/acceso/ingresar']], ['label' => 'Quiero ser socio']],
]) ?>

<section class="huk-cuenta is-con-banner">
    <div class="container">
        <div class="huk-tarjeta-form is-registro" data-reveal>
            <div class="huk-bienvenida">
                <div class="huk-bienvenida-icono"><i class="fa-solid fa-handshake" aria-hidden="true"></i></div>
                <div class="huk-bienvenida-texto">
                    <h2>Le damos la bienvenida a HUK-THE-ICHI</h2>
                    <p>Complete sus datos con atención: nuestro equipo <strong>los verifica</strong> antes de habilitar su acceso
                        y le avisaremos por correo cuando su solicitud esté aprobada. ¿Tiene alguna consulta? Escríbanos o llámenos.</p>
                    <?= $this->render('_contacto', ['contacto' => $contacto]) ?>
                </div>
            </div>

            <?= $this->render('../_aviso') ?>
            <?php if ($model->hasErrors()): ?>
                <?= $this->render('_errores_registro', ['model' => $model]) ?>
            <?php endif; ?>
            <?php $form = ActiveForm::begin([
                'id' => 'registro-form',
                'options' => ['enctype' => 'multipart/form-data', 'class' => 'huk-form'],
                'fieldConfig' => $campo,
            ]); ?>
            <datalist id="lista-paises"><?php foreach (SolicitudInversorForm::PAISES as $p): ?><option value="<?= Html::encode($p) ?>"><?php endforeach; ?></datalist>
            <p class="huk-form-obligatorio"><span>*</span> Campos obligatorios</p>

            <!-- 1. Información general -->
            <section class="huk-seccion-form">
                <?= $seccion(1, 'fa-id-card', 'Información general', 'Quién va a invertir: una persona o una empresa.') ?>
                <?= $form->field($model, 'tipo')->radioList(Inversor::TIPOS, [
                    'class' => 'huk-opciones',
                    'item' => fn($i, $label, $name, $checked, $value) => Html::label(
                        Html::radio($name, $checked, ['value' => $value, 'data-inversor-tipo' => true])
                        . '<i class="fa-solid ' . ($value === 'empresa' ? 'fa-building' : 'fa-user') . '" aria-hidden="true"></i> '
                        . '<span>' . Html::encode($label) . '<small>' . ($value === 'empresa' ? 'Invierto en nombre de una empresa' : 'Invierto a título personal') . '</small></span>',
                        null, ['class' => 'huk-opcion' . ($checked ? ' is-activo' : '')]),
                ])->label('Invierto como') ?>
                <div class="row" data-solo-empresa>
                    <div class="col-md-6"><?= $form->field($model, 'razon_social', ['options' => ['class' => 'mb-3 required']])->textInput(['maxlength' => 200, 'autocomplete' => 'organization']) ?></div>
                    <div class="col-md-3"><?= $form->field($model, 'nit', ['options' => ['class' => 'mb-3 required']])->textInput(['maxlength' => 40]) ?></div>
                    <div class="col-md-3"><?= $form->field($model, 'cargo', ['options' => ['class' => 'mb-3 required']])->textInput(['maxlength' => 100, 'autocomplete' => 'organization-title']) ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?= $form->field($model, 'nombre')->textInput(['maxlength' => 100, 'autocomplete' => 'name'])
                        ->label(null, ['class' => 'form-label', 'data-etiqueta-persona' => 'Nombre completo', 'data-etiqueta-empresa' => 'Nombre completo del representante']) ?></div>
                    <div class="col-md-3"><?= $form->field($model, 'pais')->textInput(['maxlength' => 50, 'list' => 'lista-paises', 'autocomplete' => 'country-name', 'placeholder' => 'Bolivia']) ?></div>
                    <div class="col-md-3"><?= $form->field($model, 'ciudad')->textInput(['maxlength' => 50, 'autocomplete' => 'address-level2', 'placeholder' => 'Santa Cruz']) ?></div>
                </div>
                <div class="row" data-solo-empresa>
                    <div class="col-md-6"><?= $form->field($model, 'sitio_web')->textInput(['maxlength' => 255, 'placeholder' => 'https://']) ?></div>
                </div>
            </section>

            <!-- 2. Documento de identidad -->
            <section class="huk-seccion-form">
                <?= $seccion(2, 'fa-address-card', 'Documento de identidad', 'Lo usamos solo para verificar su identidad. Únicamente lo ve nuestro equipo.') ?>
                <div class="row">
                    <div class="col-md-5"><?= $form->field($model, 'documento_tipo', $select)->dropDownList(Inversor::DOCUMENTOS) ?></div>
                    <div class="col-md-7"><?= $form->field($model, 'documento_numero')->textInput(['maxlength' => 40]) ?></div>
                </div>
                <?= $form->field($model, 'documento', [
                    'template' => '{label}<div class="huk-archivo" data-archivo data-max-mb="' . Inversor::MAX_DOCUMENTO_MB . '">{input}'
                        . '<label class="huk-archivo-zona" for="solicitudinversorform-documento" data-archivo-zona>'
                        . '<i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>'
                        . '<strong>Haga clic o arrastre el archivo aquí</strong>'
                        . '<span>Foto o PDF · JPG, PNG, WEBP o PDF · hasta ' . Inversor::MAX_DOCUMENTO_MB . ' MB</span></label>'
                        . '<div class="huk-archivo-vista" data-archivo-vista hidden>'
                        . '<div class="huk-archivo-mini" data-archivo-mini></div>'
                        . '<div class="huk-archivo-datos"><strong data-archivo-nombre></strong><span data-archivo-peso></span></div>'
                        . '<div class="huk-archivo-acciones">'
                        . '<label class="huk-archivo-btn" for="solicitudinversorform-documento"><i class="fa-solid fa-rotate" aria-hidden="true"></i> Cambiar</label>'
                        . '<button type="button" class="huk-archivo-btn is-quitar" data-archivo-quitar><i class="fa-solid fa-trash-can" aria-hidden="true"></i> Quitar</button>'
                        . '</div></div></div>{hint}{error}',
                ])->fileInput(['accept' => $formatos, 'class' => 'huk-archivo-input'])
                    ->hint('Una foto clara o un PDF del frente (si tiene datos atrás, de los dos lados).') ?>
            </section>

            <!-- 3. Datos de contacto -->
            <section class="huk-seccion-form">
                <?= $seccion(3, 'fa-envelope-open-text', 'Datos de contacto', 'Por aquí le avisaremos cómo avanza su solicitud.') ?>
                <div class="row">
                    <div class="col-md-6"><?= $form->field($model, 'email', ['enableAjaxValidation' => true])->textInput(['maxlength' => 100, 'type' => 'email', 'autocomplete' => 'email', 'placeholder' => 'nombre@correo.com'])
                        ->hint('Con este correo ingresará. Le enviaremos un enlace para confirmarlo.') ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'celular')->textInput(['maxlength' => 30, 'type' => 'tel', 'autocomplete' => 'tel', 'placeholder' => '+591 70000000'])
                        ->hint('Con código de país. Si tiene WhatsApp, mejor.') ?></div>
                </div>
            </section>

            <!-- 4. Intereses de inversión -->
            <section class="huk-seccion-form">
                <?= $seccion(4, 'fa-chart-line', 'Intereses de inversión', 'Nos ayuda a mostrarle los proyectos que encajan con su perfil.') ?>
                <?= $form->field($model, 'intereses')->checkboxList($ecosistemas, [
                    'class' => 'huk-areas',
                    'item' => fn($i, $label, $name, $checked, $value) => Html::label(Html::checkbox($name, $checked, ['value' => $value])
                        . '<span>' . Html::encode(preg_replace('/^\d+ · /', '', $label)) . '</span>'),
                ])->hint('Puede elegir varias.') ?>
                <?php
                // Moneda (Configuración → Monedas): el código se ve adelante de cada monto y cambia al elegir otra
                $actual = $monedas[$model->idmoneda]['codigo'] ?? (reset($monedas)['codigo'] ?? '');
                $conMoneda = ['template' => '{label}<div class="huk-monto"><span class="huk-monto-moneda" data-monto-moneda>' . Html::encode($actual) . '</span>{input}</div>{hint}{error}'];
                ?>
                <div class="row">
                    <div class="col-md-4"><?= $form->field($model, 'idmoneda', $select)->dropDownList(array_map(fn($m) => $m['codigo'] . ' · ' . $m['nombre'], $monedas), [
                        'data-moneda' => true, 'data-codigos' => json_encode(array_map(fn($m) => $m['codigo'], $monedas)),
                    ])->hint('En qué moneda piensa invertir.') ?></div>
                    <div class="col-md-4"><?= $form->field($model, 'monto_minimo', $conMoneda)->input('number', ['min' => 1, 'step' => 'any', 'inputmode' => 'decimal', 'placeholder' => 'Ej: 50000'])
                        ->hint('Sin puntos ni comas.') ?></div>
                    <div class="col-md-4"><?= $form->field($model, 'monto_maximo', $conMoneda)->input('number', ['min' => 1, 'step' => 'any', 'inputmode' => 'decimal', 'placeholder' => 'Ej: 250000'])
                        ->hint('Si es un monto fijo, el mismo en los dos.') ?></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><?= $form->field($model, 'plazo_preferido', $select)->dropDownList(Inversor::PLAZOS, ['prompt' => 'Elija…']) ?></div>
                    <div class="col-md-4"><?= $form->field($model, 'participacion_preferida', $select)->dropDownList(Inversor::PARTICIPACIONES) ?></div>
                    <div class="col-md-4"><?= $form->field($model, 'como_nos_conocio')->textInput(['maxlength' => 150, 'placeholder' => 'Ej: recomendación, LinkedIn…']) ?></div>
                </div>
                <?= $form->field($model, 'mensaje')->textarea(['rows' => 3, 'maxlength' => 1000, 'placeholder' => 'Cuéntenos brevemente qué tipo de proyectos le interesan.']) ?>
            </section>

            <!-- 5. Datos de acceso -->
            <section class="huk-seccion-form">
                <?= $seccion(5, 'fa-lock', 'Datos de acceso', 'Su contraseña para entrar a la plataforma cuando su solicitud esté aprobada.') ?>
                <div class="row">
                    <div class="col-md-6"><?= $form->field($model, 'contrasena', $clave('solicitudinversorform-contrasena'))->passwordInput(['maxlength' => 72, 'autocomplete' => 'new-password'])
                        ->hint('Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.') ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'contrasena2', $clave('solicitudinversorform-contrasena2'))->passwordInput(['maxlength' => 72, 'autocomplete' => 'new-password']) ?></div>
                </div>
                <div class="huk-hp" aria-hidden="true"><?= $form->field($model, 'sitio_hp')->textInput(['tabindex' => -1, 'autocomplete' => 'off']) ?></div>
                <?= Html::activeHiddenInput($model, 'inicio', ['value' => SolicitudInversorForm::marcaInicio()]) /* antispam: trampa de tiempo */ ?>
                <div class="huk-terminos">
                    <?= $form->field($model, 'terminos', ['options' => ['class' => 'mb-0']])->checkbox(['uncheck' => '0', 'value' => '1']) ?>
                    <?php if ($terminos): ?>
                        <button type="button" class="huk-terminos-leer" data-bs-toggle="modal" data-bs-target="#modal-terminos">
                            <i class="fa-solid fa-file-contract" aria-hidden="true"></i> Leer los términos de uso y la política de privacidad
                        </button>
                    <?php endif; ?>
                </div>
            </section>

            <div class="huk-form-enviar">
                <?= Html::submitButton('<i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Enviar solicitud', ['class' => 'huk-btn-oro', 'data-enviar-solicitud' => true]) ?>
                <p class="huk-form-pie">¿Ya tiene cuenta? <?= Html::a('Ingrese aquí', ['/cuenta/acceso/ingresar']) ?></p>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</section>

<?php if ($terminos): ?>
    <!-- Términos y privacidad (Configuración → Términos y privacidad). "Acepto" marca la casilla del formulario. -->
    <div class="modal fade huk-modal-terminos" id="modal-terminos" tabindex="-1" aria-labelledby="modal-terminos-titulo" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="huk-modal-ante">Versión <?= (int) $terminos->numero ?> · <?= Yii::$app->formatter->asDate($terminos->fecha_publicacion, 'php:d/m/Y') ?></span>
                        <h2 class="modal-title" id="modal-terminos-titulo"><?= Html::encode($terminos->titulo) ?></h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body huk-rich"><?= $terminos->texto /* HTML limpio (HtmlEditor) */ ?></div>
                <div class="modal-footer">
                    <button type="button" class="btn huk-btn-linea" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="huk-btn-oro" data-bs-dismiss="modal" data-aceptar-terminos="#solicitudinversorform-terminos">
                        <i class="fa-solid fa-check" aria-hidden="true"></i> Leí y acepto
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
