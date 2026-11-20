<?php

/**
 * Modal "Quiero invertir": formulario corto. Se envía por AJAX (huk-invertir.js) a /cuenta/panel/invertir.
 * El monto no es obligatorio: "A conversar".
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $proyecto
 * @var app\models\QuieroInvertirForm $form
 * @var app\models\InteresInversion|null $previo
 */

use app\models\InteresInversion;
use app\models\Inversor;
use yii\helpers\Html;

$actualiza = $previo !== null && $previo->activo();
$moneda = $proyecto->moneda->codigo ?? '';
$campo = function ($atributo, $input, $ayuda = null) use ($form) {
    return '<div class="huk-inv-campo">' . Html::activeLabel($form, $atributo, ['class' => 'form-label']) . $input
        . ($ayuda ? '<div class="form-text">' . $ayuda . '</div>' : '')
        . '<div class="invalid-feedback" data-error-de="quieroinvertirform-' . $atributo . '"></div></div>';
};
?>
<div class="modal fade huk-modal-invertir" id="modal-invertir" tabindex="-1" aria-labelledby="modal-invertir-titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <span class="huk-modal-ante"><?= $actualiza ? 'Actualizar mi interés' : 'Quiero invertir' ?></span>
                    <h2 class="modal-title" id="modal-invertir-titulo"><?= Html::encode($proyecto->titulo) ?></h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <?= Html::beginForm(['/cuenta/panel/invertir', 'slug' => $proyecto->slug], 'post', ['class' => 'huk-inv-form', 'data-invertir-form' => true, 'novalidate' => true]) ?>
            <div class="modal-body" data-invertir-cuerpo>
                <div class="huk-inv-aviso" data-invertir-aviso hidden role="alert"></div>

                <fieldset class="huk-inv-campo">
                    <legend class="form-label"><?= Html::encode($form->getAttributeLabel('tipo_monto')) ?></legend>
                    <div class="huk-inv-opciones">
                        <label class="huk-inv-opcion"><?= Html::radio('QuieroInvertirForm[tipo_monto]', $form->tipo_monto === 'monto', ['value' => 'monto', 'data-tipo-monto' => true]) ?>
                            <span><strong>Tengo un monto estimado</strong><small>Nos ayuda a preparar la propuesta.</small></span></label>
                        <label class="huk-inv-opcion"><?= Html::radio('QuieroInvertirForm[tipo_monto]', $form->tipo_monto === 'conversar', ['value' => 'conversar', 'data-tipo-monto' => true]) ?>
                            <span><strong>A conversar</strong><small>Lo definimos juntos en la reunión.</small></span></label>
                    </div>
                </fieldset>

                <div class="huk-inv-monto" data-monto-zona<?= $form->tipo_monto === 'conversar' ? ' hidden' : '' ?>>
                    <?= $campo('monto', '<div class="input-group"><span class="input-group-text">' . Html::encode($moneda) . '</span>'
                        . Html::activeTextInput($form, 'monto', ['class' => 'form-control', 'inputmode' => 'decimal', 'placeholder' => 'Ej.: 50000', 'autocomplete' => 'off']) . '</div>',
                        'Solo el número, sin puntos de miles. Es una estimación: no lo compromete.') ?>
                </div>

                <div class="row g-3">
                    <div class="col-md-6"><?= $campo('participacion', Html::activeDropDownList($form, 'participacion', Inversor::PARTICIPACIONES, ['class' => 'form-select', 'prompt' => 'Elija…'])) ?></div>
                    <div class="col-md-6"><?= $campo('plazo', Html::activeDropDownList($form, 'plazo', Inversor::PLAZOS, ['class' => 'form-select', 'prompt' => 'Elija…'])) ?></div>
                </div>

                <fieldset class="huk-inv-campo">
                    <legend class="form-label"><?= Html::encode($form->getAttributeLabel('contacto_medio')) ?></legend>
                    <div class="huk-inv-medios">
                        <?php foreach (InteresInversion::MEDIOS as $clave => [$texto, $icono]): ?>
                            <label class="huk-inv-medio"><?= Html::radio('QuieroInvertirForm[contacto_medio]', $form->contacto_medio === $clave, ['value' => $clave]) ?>
                                <i class="<?= $icono ?>" aria-hidden="true"></i><span><?= $texto ?></span></label>
                        <?php endforeach; ?>
                    </div>
                    <div class="invalid-feedback" data-error-de="quieroinvertirform-contacto_medio"></div>
                </fieldset>

                <?= $campo('contacto_horario', Html::activeTextInput($form, 'contacto_horario', ['class' => 'form-control', 'maxlength' => 100, 'placeholder' => 'Ej.: lunes a viernes por la tarde'])) ?>
                <?= $campo('comentario', Html::activeTextarea($form, 'comentario', ['class' => 'form-control', 'rows' => 3, 'maxlength' => 1000, 'placeholder' => '¿Algo que debamos saber? (opcional)'])) ?>

                <div class="huk-inv-campo huk-inv-acepto">
                    <div class="form-check">
                        <?= Html::activeCheckbox($form, 'acepto', ['class' => 'form-check-input', 'label' => false, 'uncheck' => '0']) ?>
                        <?= Html::activeLabel($form, 'acepto', ['class' => 'form-check-label']) ?>
                    </div>
                    <div class="invalid-feedback" data-error-de="quieroinvertirform-acepto"></div>
                </div>
                <p class="huk-inv-nota"><i class="fa-solid fa-clock" aria-hidden="true"></i> Lo contactaremos en las próximas <?= InteresInversion::HORAS_CONTACTO ?> horas hábiles por el medio que elija.</p>
            </div>
            <div class="modal-footer" data-invertir-pie>
                <button type="button" class="huk-btn-linea" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="huk-btn-oro" data-invertir-enviar><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> <?= $actualiza ? 'Guardar cambios' : 'Enviar mi interés' ?></button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
