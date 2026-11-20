<?php

/**
 * Aceptar la versión vigente del acuerdo de confidencialidad (firma con el nombre completo).
 *
 * @var yii\web\View $this
 * @var app\models\NdaVersion $version
 * @var app\models\Inversor $inversor
 * @var string|null $error
 * @var bool $anterior ya había aceptado una versión anterior
 */

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

\app\assets_b\CuentaAsset::register($this);
?>

<section class="huk-cuenta">
    <div class="container">
        <div class="huk-cuenta-cab">
            <span class="huk-cuenta-eyebrow">Último paso</span>
            <h1><?= Html::encode($version->titulo) ?></h1>
            <p><?= $anterior
                ? 'Actualizamos el acuerdo. Para seguir viendo los proyectos, lea y acepte la nueva versión.'
                : 'Su cuenta está aprobada. Antes de ver los proyectos, lea y acepte el acuerdo de confidencialidad.' ?></p>
        </div>

        <div class="huk-tarjeta-form">
            <?php if ($error): ?><div class="huk-aviso-danger"><?= Html::encode($error) ?></div><?php endif; ?>
            <p class="huk-nda-version">Versión <?= (int) $version->numero ?> · vigente desde <?= Yii::$app->formatter->asDate($version->fecha_publicacion, 'php:d/m/Y') ?></p>
            <div class="huk-nda-texto"><?= HtmlPurifier::process($version->texto) ?></div>

            <?= Html::beginForm(['/cuenta/panel/nda'], 'post', ['class' => 'huk-form', 'id' => 'nda-form']) ?>
            <div class="form-check mb-3">
                <?= Html::checkbox('acepto', false, ['value' => '1', 'id' => 'nda-acepto', 'class' => 'form-check-input', 'required' => true]) ?>
                <?= Html::label('Leí el acuerdo y lo acepto.', 'nda-acepto', ['class' => 'form-check-label']) ?>
            </div>
            <div class="mb-3">
                <?= Html::label('Firme con su nombre completo', 'nda-firma', ['class' => 'form-label']) ?>
                <?= Html::textInput('firma', Yii::$app->request->post('firma', ''), ['id' => 'nda-firma', 'class' => 'form-control', 'maxlength' => 150, 'required' => true,
                    'placeholder' => $inversor->usuario->nombrecompleto, 'autocomplete' => 'name']) ?>
                <div class="form-text">Queda registrado con la fecha, la hora y desde dónde aceptó.</div>
            </div>
            <?= Html::submitButton('Aceptar y continuar', ['class' => 'huk-btn-oro w-100']) ?>
            <?= Html::endForm() ?>
        </div>
    </div>
</section>
