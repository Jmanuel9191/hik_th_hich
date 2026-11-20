<?php

/**
 * "Quiero invertir" en la página del proyecto. Va dos veces: en el banner ($lugar = hero) y antes del pie ($lugar = pie).
 * Después de enviar, PanelController::actionInvertir devuelve este mismo bloque para reemplazarlo sin recargar.
 *  - Sin interés (o el anterior ya cerrado) y el proyecto recibe inversión → botón que abre el modal.
 *  - Con un interés en curso → su estado, monto y "Actualizar mi interés".
 *  - Concretado → mensaje de inversión concretada.
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $proyecto
 * @var app\models\InteresInversion|null $previo
 * @var bool $puede
 * @var string $lugar  hero | pie
 */

use app\models\InteresInversion;
use yii\helpers\Html;

$enCurso = $previo !== null && $previo->activo();
$fecha = fn($f) => Yii::$app->formatter->asDate($f, 'php:d/m/Y');
$boton = fn($texto) => Html::button('<i class="fa-solid fa-hand-holding-dollar" aria-hidden="true"></i> ' . $texto,
    ['class' => 'huk-invertir-btn huk-shine', 'data-bs-toggle' => 'modal', 'data-bs-target' => '#modal-invertir']);
?>
<div class="huk-invertir is-<?= $lugar ?>" data-interes-zona="<?= $lugar ?>">
    <?php if ($enCurso): ?>
        <div class="huk-invertir-estado">
            <span class="huk-invertir-chip <?= $previo->estadoClaseWeb() ?>"><i class="fa-solid fa-circle" aria-hidden="true"></i> <?= Html::encode($previo->estadoNombre()) ?></span>
            <p>Usted envió su interés el <strong><?= $fecha($previo->fecha_registro) ?></strong> · <?= Html::encode($previo->montoTexto()) ?>.
                <?php if ($previo->estado === 'nuevo'): ?>Lo contactaremos en las próximas <?= InteresInversion::HORAS_CONTACTO ?> horas hábiles.<?php endif; ?></p>
            <button type="button" class="huk-invertir-link" data-bs-toggle="modal" data-bs-target="#modal-invertir">
                <i class="fa-solid fa-pen" aria-hidden="true"></i> Actualizar mi interés
            </button>
        </div>
    <?php elseif ($previo !== null && $previo->estado === 'concretado'): ?>
        <div class="huk-invertir-estado is-ok">
            <span class="huk-invertir-chip is-concretado"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Inversión concretada</span>
            <p>Gracias por invertir en este proyecto. Sus pagos están en <?= Html::a('Mi cuenta → Mis inversiones', ['/cuenta/panel/index', 'tab' => 'inversiones']) ?>.</p>
            <?php if ($puede): ?><?= $boton('Quiero invertir más') ?><?php endif; ?>
        </div>
    <?php elseif ($puede): ?>
        <?php if ($lugar === 'pie'): ?>
            <div class="huk-invertir-cta">
                <div>
                    <h2>¿Le interesa este proyecto?</h2>
                    <p>Cuéntenos cuánto piensa invertir (o si prefiere conversarlo) y lo contactaremos en las próximas <?= InteresInversion::HORAS_CONTACTO ?> horas hábiles. No lo compromete.</p>
                </div>
                <?= $boton('Quiero invertir en este proyecto') ?>
            </div>
        <?php else: ?>
            <?= $boton('Quiero invertir en este proyecto') ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
