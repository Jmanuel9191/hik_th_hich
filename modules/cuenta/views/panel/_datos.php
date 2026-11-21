<?php

/**
 * Mi cuenta → Mis datos. Con candado: los cambia nuestro equipo. Editables: los de PerfilInversorForm
 * (contacto y, si invierte como empresa, los datos de la empresa).
 *
 * @var yii\web\View $this
 * @var app\models\Inversor $inversor
 * @var app\models\PerfilInversorForm $formDatos
 */

use app\models\Inversor;
use app\models\SolicitudInversorForm;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$u = $inversor->usuario;
$fijo = fn($rotulo, $valor) => '<div class="huk-dato"><span class="huk-dato-rotulo"><i class="fa-solid fa-lock" aria-hidden="true"></i> '
    . Html::encode($rotulo) . '</span><span class="huk-dato-valor notranslate" translate="no">' . Html::encode($valor !== null && $valor !== '' ? $valor : '—') . '</span></div>';
$empresa = $formDatos->scenario === \app\models\PerfilInversorForm::SCENARIO_EMPRESA;
?>

<?php $form = ActiveForm::begin([
    'id' => 'perfil-datos-form', 'action' => ['/cuenta/panel/datos'], 'options' => ['class' => 'huk-form'],
    'fieldConfig' => ['options' => ['class' => 'mb-0'], 'labelOptions' => ['class' => 'form-label'], 'inputOptions' => ['class' => 'form-control'],
        'errorOptions' => ['class' => 'invalid-feedback d-block'], 'hintOptions' => ['class' => 'form-text']],
]); ?>

<div class="huk-perfil-bloque">
    <div class="huk-perfil-bloque-cab">
        <h2><i class="fa-solid fa-user" aria-hidden="true"></i> Identidad</h2>
    </div>
    <div class="huk-datos-grid">
        <?= $fijo('Nombre completo', $u->nombrecompleto) ?>
        <?= $fijo('Invierte como', Inversor::TIPOS[$inversor->tipo] ?? $inversor->tipo) ?>
        <?= $fijo('Documento', trim((Inversor::DOCUMENTOS[$inversor->documento_tipo] ?? $inversor->documento_tipo) . ' ' . $inversor->documento_numero)) ?>
        <?= $fijo('Correo (para ingresar)', $u->email) ?>
    </div>
</div>

<div class="huk-perfil-bloque">
    <div class="huk-perfil-bloque-cab">
        <h2><i class="fa-solid fa-address-book" aria-hidden="true"></i> Contacto</h2>
    </div>
    <datalist id="lista-paises-perfil"><?php foreach (SolicitudInversorForm::PAISES as $p): ?><option value="<?= Html::encode($p) ?>"><?php endforeach; ?></datalist>
    <div class="row g-3">
        <div class="col-md-4"><?= $form->field($formDatos, 'celular')->textInput(['maxlength' => 30, 'type' => 'tel', 'autocomplete' => 'tel']) ?></div>
        <div class="col-md-4"><?= $form->field($formDatos, 'pais')->textInput(['maxlength' => 50, 'list' => 'lista-paises-perfil', 'autocomplete' => 'country-name']) ?></div>
        <div class="col-md-4"><?= $form->field($formDatos, 'ciudad')->textInput(['maxlength' => 50, 'autocomplete' => 'address-level2']) ?></div>
    </div>
</div>

<?php if ($empresa): ?>
    <div class="huk-perfil-bloque">
        <div class="huk-perfil-bloque-cab">
            <h2><i class="fa-solid fa-building" aria-hidden="true"></i> Empresa</h2>
        </div>
        <div class="row g-3">
            <div class="col-md-6"><?= $form->field($formDatos, 'razon_social')->textInput(['maxlength' => 200, 'autocomplete' => 'organization']) ?></div>
            <div class="col-md-3"><?= $form->field($formDatos, 'nit')->textInput(['maxlength' => 40]) ?></div>
            <div class="col-md-3"><?= $form->field($formDatos, 'cargo')->textInput(['maxlength' => 100, 'autocomplete' => 'organization-title']) ?></div>
            <div class="col-md-6"><?= $form->field($formDatos, 'sitio_web')->textInput(['maxlength' => 255, 'placeholder' => 'https://']) ?></div>
        </div>
    </div>
<?php endif; ?>

<div class="huk-perfil-bloque">
    <div class="huk-perfil-bloque-cab">
        <h2><i class="fa-solid fa-chart-line" aria-hidden="true"></i> Preferencias de inversión</h2>
    </div>
    <div class="huk-datos-grid">
        <?= $fijo('Monto que piensa invertir', $inversor->montoTexto()) ?>
        <?= $fijo('Plazo', Inversor::PLAZOS[$inversor->plazo_preferido] ?? null) ?>
        <?= $fijo('Participación', Inversor::PARTICIPACIONES[$inversor->participacion_preferida] ?? null) ?>
        <?= $fijo('Áreas de interés', implode(', ', array_column($inversor->intereses, 'nombre'))) ?>
    </div>
</div>

<div class="huk-perfil-guardar">
    <p><i class="fa-solid fa-lock" aria-hidden="true"></i> Los datos con candado los actualiza nuestro equipo. Si necesita corregir alguno, escríbanos.</p>
    <?= Html::submitButton('<i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Guardar cambios', ['class' => 'huk-btn-oro']) ?>
</div>

<?php ActiveForm::end(); ?>
