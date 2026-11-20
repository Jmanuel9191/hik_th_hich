<?php

/**
 * Ficha del proyecto. Según el tipo se muestran unos campos u otros y cambian algunas etiquetas
 * (huk-proyectos.js: data-solo-tipo / data-etiqueta-*). Imágenes: CampoImagen. Textos largos: editor.
 * Fotos, videos y documentos se cargan desde la vista del proyecto (pestañas).
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $model
 * @var array $ecosistemas id => nombre
 * @var array $gerentes id => nombre
 */

use app\models\Moneda;
use app\models\Proyecto;
use app\widgets\CampoImagen;
use app\widgets\MapaPaises;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\ProyectosAdminAsset::register($this);

$editor = fn($alto) => ['data-editor' => true, 'data-editor-alto' => $alto, 'data-tinymce-base' => Url::to('@web/assets_b/lib/tinymce'), 'rows' => 8];
$etiqueta = fn($campo) => [
    'data-etiqueta-financiamiento' => Proyecto::ETIQUETAS[$campo][0],
    'data-etiqueta-inversion' => Proyecto::ETIQUETAS[$campo][1],
];
$paises = array_combine(MapaPaises::PAISES, MapaPaises::PAISES);
if ($model->pais && !isset($paises[$model->pais])) {
    $paises[$model->pais] = $model->pais;
}
?>

<?php $form = ActiveForm::begin([
    'id' => 'proyecto-form',
    'options' => ['enctype' => 'multipart/form-data', 'class' => 'huk-proyecto-form', 'data-proyecto-form' => true],
]); ?>

<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-id-card-o"></i> Datos del proyecto</h3></div>
            <div class="box-body">
                <?= $form->field($model, 'tipo')->radioList(Proyecto::TIPOS, [
                    'class' => 'huk-proyecto-tipos',
                    'item' => fn($i, $label, $name, $checked, $value) => Html::label(
                        Html::radio($name, $checked, ['value' => $value, 'data-proyecto-tipo' => true])
                        . ' <strong>' . Html::encode($label) . '</strong><small>' . Html::encode(Proyecto::AYUDA_TIPOS[$value]) . '</small>',
                        null, ['class' => 'huk-proyecto-tipo' . ($checked ? ' is-activo' : '')]),
                ]) ?>
                <?= $form->field($model, 'titulo')->textInput(['maxlength' => 160]) ?>
                <div class="row">
                    <div class="col-sm-6"><?= $form->field($model, 'idecosistema')->dropDownList($ecosistemas, ['prompt' => 'Elija el ecosistema…']) ?></div>
                    <div class="col-sm-6"><?= $form->field($model, 'idgerente')->dropDownList($gerentes, ['prompt' => 'Sin asignar'])
                        ->hint('Le llegan los avisos de este proyecto. Tiene que tener el ecosistema asignado.') ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-4"><?= $form->field($model, 'pais')->dropDownList($paises, ['prompt' => 'Elija el país…']) ?></div>
                    <div class="col-sm-4"><?= $form->field($model, 'ciudad')->textInput(['maxlength' => 80]) ?></div>
                    <div class="col-sm-4"><?= $form->field($model, 'ubicacion')->textInput(['maxlength' => 200, 'placeholder' => 'Ej.: Km 12 carretera al norte']) ?></div>
                </div>
                <div data-solo-tipo="financiamiento">
                    <?= $form->field($model, 'etapa')->dropDownList(Proyecto::ETAPAS, ['prompt' => 'Elija la etapa…']) ?>
                </div>
                <div data-solo-tipo="inversion">
                    <?= $form->field($model, 'etapasInteres')->checkboxList(Proyecto::ETAPAS, ['class' => 'huk-proyecto-checks']) ?>
                </div>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text-o"></i> Presentación</h3></div>
            <div class="box-body">
                <?= $form->field($model, 'resumen')->textarea(['rows' => 3, 'maxlength' => 500])
                    ->hint('Lo que se lee en la tarjeta del listado. Máx. 500 caracteres.') ?>
                <?= $form->field($model, 'descripcion')->textarea($editor(380))
                    ->hint('Ficha interna (la ve solo el equipo de HUK). Lo que ve el inversor se arma en la pestaña «Página web».') ?>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-line-chart"></i> Números</h3></div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-4"><?= $form->field($model, 'idmoneda')->dropDownList(Moneda::lista($model->idmoneda), ['prompt' => 'Elija…']) ?></div>
                    <div class="col-sm-8" data-solo-tipo="financiamiento"><?= $form->field($model, 'monto_total')->textInput(['inputmode' => 'decimal', 'placeholder' => '2500000'])
                        ->hint('Lo que cuesta el proyecto completo (opcional).') ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-4"><?= $form->field($model, 'monto_objetivo')->textInput(['inputmode' => 'decimal', 'placeholder' => '1000000'])->label(null, $etiqueta('monto_objetivo')) ?></div>
                    <div class="col-sm-4"><?= $form->field($model, 'inversion_minima')->textInput(['inputmode' => 'decimal', 'placeholder' => '10000'])->label(null, $etiqueta('inversion_minima')) ?></div>
                    <div class="col-sm-4"><?= $form->field($model, 'inversion_maxima')->textInput(['inputmode' => 'decimal'])->label(null, $etiqueta('inversion_maxima')) ?></div>
                </div>
                <p class="huk-ayuda huk-ayuda-numeros"><i class="fa fa-info-circle"></i> Escriba solo el número, sin puntos de miles: <code>1500000</code> o <code>1500000.50</code>.</p>
                <div class="row">
                    <div class="col-sm-4"><?= $form->field($model, 'roi')->textInput(['inputmode' => 'decimal', 'placeholder' => '18'])->label(null, $etiqueta('roi')) ?></div>
                    <div class="col-sm-4"><?= $form->field($model, 'plazo_meses')->textInput(['inputmode' => 'numeric', 'placeholder' => '36'])->label(null, $etiqueta('plazo_meses')) ?></div>
                    <div class="col-sm-4" data-solo-tipo="financiamiento"><?= $form->field($model, 'frecuencia_retorno')->dropDownList(Proyecto::FRECUENCIAS, ['prompt' => 'Elija…']) ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-4"><?= $form->field($model, 'tipo_participacion')->dropDownList(Proyecto::PARTICIPACIONES, ['prompt' => 'Elija…']) ?></div>
                    <div class="col-sm-4"><?= $form->field($model, 'fecha_apertura')->input('date') ?></div>
                    <div class="col-sm-4"><?= $form->field($model, 'fecha_cierre')->input('date') ?></div>
                </div>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-shield"></i> Riesgos, garantías e impacto (opcional · ficha interna)</h3></div>
            <div class="box-body">
                <?= $form->field($model, 'riesgos')->textarea($editor(260)) ?>
                <?= $form->field($model, 'garantias')->textarea(['rows' => 3]) ?>
                <?= $form->field($model, 'impacto')->textarea(['rows' => 3, 'placeholder' => 'Ej.: 120 empleos directos, 40 proveedores locales…']) ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <?php foreach (Proyecto::IMAGENES as $campo => $titulo): ?>
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-picture-o"></i> <?= Html::encode($titulo) ?></h3></div>
                <div class="box-body">
                    <?= $form->field($model, $campo)->widget(CampoImagen::class, [
                        'url' => $model->urlImagen($campo),
                        'medida' => Proyecto::MEDIDAS[$campo],
                        'maxMb' => Proyecto::MAX_IMAGEN_MB,
                    ])->label(false)->hint($campo === 'foto_principal' ? 'Obligatoria para publicar. Sale en la tarjeta del listado.' : 'Opcional. Va arriba de todo en la ficha.') ?>
                </div>
            </div>
        <?php endforeach; ?>
        <p class="huk-aviso-contacto"><i class="fa fa-lock"></i> Todo lo del proyecto es <strong>privado</strong>: solo lo ven el equipo de HUK
            y los inversores aprobados del área. La galería de fotos, los videos y los documentos se cargan en la ficha, después de guardar.</p>
    </div>
</div>

<div class="huk-form-acciones text-center">
    <?= Html::submitButton('<i class="fa fa-save"></i> ' . ($model->isNewRecord ? 'Crear proyecto' : 'Guardar cambios'), ['class' => 'btn btn-success btform']) ?>
    <?= Html::a('Cancelar', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->idproyecto], ['class' => 'btn btn-default btform']) ?>
</div>

<?php ActiveForm::end(); ?>
