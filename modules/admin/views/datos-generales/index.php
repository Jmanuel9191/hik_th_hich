<?php

/**
 * Configuración → Datos generales.
 * Vista previa del mapa: assets_b/js/huk-datos-generales.js (data-mapa-input / data-mapa-preview).
 *
 * @var yii\web\View $this
 * @var app\models\DatosGeneralesForm $model
 * @var yii\data\ActiveDataProvider $historial
 */

use app\models\DatosGeneralesForm;
use kartik\form\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

\app\assets_b\DatosGeneralesAdminAsset::register($this);

$this->title = 'Datos generales';

$icono = fn($fa) => ['addon' => ['groupOptions' => ['class' => 'huk-adm-field'], 'prepend' => ['content' => '<i class="fa fa-' . $fa . '" aria-hidden="true"></i>']]];
$iconosRedes = ['facebook' => 'facebook', 'instagram' => 'instagram', 'tiktok' => 'music', 'youtube' => 'youtube-play', 'linkedin' => 'linkedin', 'twitter' => 'twitter'];
$mapaActual = DatosGeneralesForm::extraerMapa($model->mapa);
?>

<section class="content-header">
    <h1><i class="fa fa-globe"></i> Datos generales</h1>
    <p class="huk-inicio-sub">Lo que se muestra en la página web: título, contacto, redes sociales y ubicación.</p>
</section>

<section class="content">
    <?php $form = ActiveForm::begin([
        'id' => 'datos-generales-form',
        'type' => ActiveForm::TYPE_VERTICAL,
        'options' => ['class' => 'huk-form-usuario huk-datos-generales'],
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-id-card-o"></i> La página</h3></div>
                <div class="box-body">
                    <?= $form->field($model, 'titulo_pagina')->textInput(['maxlength' => 200])
                        ->hint('Es el nombre que aparece en la pestaña del navegador y en Google.') ?>
                    <?= $form->field($model, 'nombre_empresa')->textInput(['maxlength' => 200]) ?>
                    <?= $form->field($model, 'direccion_empresa', $icono('map-marker'))->textInput(['maxlength' => 250]) ?>
                    <?= $form->field($model, 'horarios', $icono('clock-o'))->textInput(['maxlength' => 500])
                        ->hint('Ej.: Lun a Vie: 09:00 a 18:00') ?>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-phone"></i> Contacto</h3></div>
                <div class="box-body">
                    <?= $form->field($model, 'email_web', $icono('envelope-o'))->textInput(['maxlength' => 500, 'type' => 'email'])
                        ->hint('El que se muestra en la página para que te escriban.') ?>
                    <?= $form->field($model, 'telefono_empresa', $icono('phone'))->textInput(['maxlength' => 250])
                        ->hint('Ej.: (+591) 75055468') ?>
                    <?= $form->field($model, 'whatsapp', $icono('whatsapp'))->textInput(['maxlength' => 15, 'inputmode' => 'numeric'])
                        ->hint('Solo números, con código de país. Ej.: 59175095874') ?>
                    <?= $form->field($model, 'telefonos_otros')->textarea(['rows' => 3, 'maxlength' => 500])
                        ->hint('Uno por renglón.') ?>
                    <div class="huk-aviso-remitente">
                        <?= $form->field($model, 'email', $icono('paper-plane'))->textInput(['maxlength' => 100, 'type' => 'email']) ?>
                        <p><i class="fa fa-exclamation-triangle"></i> Desde este email salen <strong>todos los correos del sistema</strong>
                            (recuperar contraseña, bloqueos, bienvenida). Si lo cambiás, tiene que ser un email del dominio
                            que esté configurado en el servidor de correo; si no, los correos dejan de llegar.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-share-alt"></i> Redes sociales</h3></div>
                <div class="box-body">
                    <p class="text-muted">Pegá el enlace completo de cada perfil. Las que dejes vacías no se muestran en la página.</p>
                    <?php foreach (DatosGeneralesForm::REDES as $red): ?>
                        <?= $form->field($model, $red, $icono($iconosRedes[$red]))->textInput(['maxlength' => 255, 'placeholder' => 'https://']) ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-map-o"></i> Ubicación</h3></div>
                <div class="box-body">
                    <ol class="huk-pasos-mapa">
                        <li>Buscá el lugar en <strong>Google Maps</strong>.</li>
                        <li>Tocá <strong>Compartir</strong> → pestaña <strong>Insertar un mapa</strong> → <strong>Copiar HTML</strong>.</li>
                        <li>Pegalo acá abajo. Se guarda solo el enlace del mapa; el tamaño lo pone la página.</li>
                    </ol>
                    <?= $form->field($model, 'mapa')->textarea([
                        'rows' => 3,
                        'data-mapa-input' => true,
                        'placeholder' => '<iframe src="https://www.google.com/maps/embed?pb=..." ...></iframe>',
                        'spellcheck' => 'false',
                    ]) ?>
                    <div class="huk-mapa-preview" data-mapa-preview>
                        <iframe title="Vista previa del mapa" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen
                            <?= $mapaActual ? 'src="' . Html::encode($mapaActual) . '"' : 'hidden' ?>></iframe>
                        <p class="huk-mapa-aviso" data-mapa-aviso <?= $mapaActual ? 'hidden' : '' ?>>Todavía no hay mapa.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="huk-form-acciones text-center">
        <?= Html::submitButton('<i class="fa fa-save"></i> Guardar cambios', ['class' => 'btn btn-success btform']) ?>
    </div>
    <?php ActiveForm::end(); ?>

    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Historial de cambios</h3></div>
        <div class="box-body">
            <?php Pjax::begin(['id' => 'datos-historial', 'timeout' => 5000]); ?>
            <?= GridView::widget([
                'dataProvider' => $historial,
                'tableOptions' => ['class' => 'table table-hover'],
                'emptyText' => 'Todavía no hay cambios.',
                'summary' => '{totalCount} cambio(s)',
                'columns' => [
                    ['label' => 'Fecha', 'value' => fn($h) => date('d/m/Y H:i', strtotime($h->fecha)), 'contentOptions' => ['class' => 'text-nowrap']],
                    ['label' => 'Qué cambió', 'format' => 'ntext', 'value' => fn($h) => $h->detalle],
                    ['label' => 'Hecho por', 'value' => fn($h) => $h->usuarioActor->usuario ?? ($h->idusuario_actor ? 'Usuario #' . $h->idusuario_actor : 'Sistema')],
                    ['label' => 'IP', 'value' => fn($h) => $h->ip ?: '—'],
                ],
            ]) ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</section>
