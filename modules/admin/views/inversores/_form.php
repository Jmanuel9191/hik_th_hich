<?php

/**
 * Solicitud de inversor desde el panel (mismos campos que la web, sin contraseña: le llega un correo para crearla).
 * Persona / Empresa: huk-solicitud.js.
 *
 * @var yii\web\View $this
 * @var app\models\SolicitudInversorForm $model
 * @var array $ecosistemas id => nombre
 * @var bool $puedeAprobar
 */

use app\models\Inversor;
use app\models\SolicitudInversorForm;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use app\widgets\ZonaArchivo;

\app\assets_b\InversoresAdminAsset::register($this);

$etiquetaNombre = ['data-etiqueta-persona' => 'Nombre completo', 'data-etiqueta-empresa' => 'Nombre completo del representante'];
$nuevo = !$model->idusuario;
?>

<?php $form = ActiveForm::begin(['id' => 'inversor-form', 'options' => ['enctype' => 'multipart/form-data']]); ?>
<datalist id="lista-paises"><?php foreach (SolicitudInversorForm::PAISES as $p): ?><option value="<?= Html::encode($p) ?>"><?php endforeach; ?></datalist>

<div class="row">
    <div class="col-md-7">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-id-card-o"></i> Quién es</h3></div>
            <div class="box-body">
                <?= $form->field($model, 'tipo')->radioList(Inversor::TIPOS, [
                    'class' => 'huk-proyecto-tipos huk-inversor-tipos',
                    'item' => fn($i, $label, $name, $checked, $value) => Html::label(
                        Html::radio($name, $checked, ['value' => $value, 'data-inversor-tipo' => true]) . ' <strong>' . Html::encode($label) . '</strong>',
                        null, ['class' => 'huk-proyecto-tipo' . ($checked ? ' is-activo' : '')]),
                ]) ?>
                <div class="row" data-solo-empresa>
                    <div class="col-sm-6"><?= $form->field($model, 'razon_social')->textInput(['maxlength' => 200]) ?></div>
                    <div class="col-sm-6"><?= $form->field($model, 'nit')->textInput(['maxlength' => 40]) ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-7"><?= $form->field($model, 'nombre')->textInput(['maxlength' => 100])->label(null, $etiquetaNombre) ?></div>
                    <div class="col-sm-5" data-solo-empresa><?= $form->field($model, 'cargo')->textInput(['maxlength' => 100]) ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-5"><?= $form->field($model, 'documento_tipo')->dropDownList(Inversor::DOCUMENTOS) ?></div>
                    <div class="col-sm-7"><?= $form->field($model, 'documento_numero')->textInput(['maxlength' => 40]) ?></div>
                </div>
                <?php if ($nuevo): ?>
                    <?= $form->field($model, 'documento', ['template' => ZonaArchivo::plantilla('JPG, PNG, WEBP o PDF · hasta ' . Inversor::MAX_DOCUMENTO_MB . ' MB')])
                        ->fileInput(['accept' => '.jpg,.jpeg,.png,.webp,.pdf'])
                        ->hint('Opcional desde el panel (en la web es obligatorio).') ?>
                <?php endif; ?>
                <div class="row">
                    <div class="col-sm-6"><?= $form->field($model, 'pais')->textInput(['maxlength' => 50, 'list' => 'lista-paises']) ?></div>
                    <div class="col-sm-6"><?= $form->field($model, 'ciudad')->textInput(['maxlength' => 50]) ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-7"><?= $form->field($model, 'email')->textInput(['maxlength' => 100, 'type' => 'email'])
                        ->hint($nuevo ? 'Le llega el correo para crear su contraseña. Con este correo entra a la web.' : 'Con este correo entra a la web.') ?></div>
                    <div class="col-sm-5"><?= $form->field($model, 'celular')->textInput(['maxlength' => 30, 'placeholder' => '+591 70000000']) ?></div>
                </div>
                <div data-solo-empresa><?= $form->field($model, 'sitio_web')->textInput(['maxlength' => 255, 'placeholder' => 'https://']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-search"></i> Qué busca</h3></div>
            <div class="box-body">
                <?= $form->field($model, 'intereses')->checkboxList($ecosistemas, ['class' => 'huk-proyecto-checks huk-checks-columna']) ?>
                <div class="row">
                    <div class="col-sm-4"><?= $form->field($model, 'idmoneda')->dropDownList(\app\models\Moneda::lista($model->idmonedaActual)) ?></div>
                    <div class="col-sm-4"><?= $form->field($model, 'monto_minimo')->input('number', ['min' => 1, 'step' => 'any', 'placeholder' => 'Ej: 50000']) ?></div>
                    <div class="col-sm-4"><?= $form->field($model, 'monto_maximo')->input('number', ['min' => 1, 'step' => 'any', 'placeholder' => 'Ej: 250000']) ?></div>
                </div>
                <?= $form->field($model, 'plazo_preferido')->dropDownList(Inversor::PLAZOS, ['prompt' => 'Elija…']) ?>
                <?= $form->field($model, 'participacion_preferida')->dropDownList(Inversor::PARTICIPACIONES) ?>
                <?= $form->field($model, 'como_nos_conocio')->textInput(['maxlength' => 150]) ?>
                <?= $form->field($model, 'mensaje')->textarea(['rows' => 3, 'maxlength' => 1000]) ?>
                <?php if ($nuevo && $puedeAprobar): ?>
                    <?= $form->field($model, 'aprobar_ahora')->checkbox()->hint('Si ya hablaste con la persona: queda aprobado con sus áreas de interés.') ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="huk-form-acciones text-center">
    <?= Html::submitButton('<i class="fa fa-save"></i> ' . ($nuevo ? 'Cargar inversor' : 'Guardar cambios'), ['class' => 'btn btn-success btform']) ?>
    <?= Html::a('Cancelar', $nuevo ? ['index'] : ['view', 'id' => $model->idusuario], ['class' => 'btn btn-default btform']) ?>
</div>
<?php ActiveForm::end(); ?>
