<?php

/**
 * Resumen de lo que falta corregir en el registro (después de enviar). La página baja sola hasta acá
 * (huk-solicitud.js) y cada error lleva a su campo. Si el correo ya tiene cuenta: Ingresar / Recuperar contraseña.
 * Recuerda volver a elegir el documento y escribir la contraseña (por seguridad el navegador no los guarda).
 *
 * @var yii\web\View $this
 * @var app\models\SolicitudInversorForm $model
 */

use yii\helpers\Html;

$etiquetas = $model->attributeLabels();
$errores = $model->getFirstErrors();
$repetido = $model->correoRepetido();
unset($errores['contrasena2']); // con "Contraseña" alcanza
?>
<div class="huk-errores" data-errores-registro role="alert" tabindex="-1">
    <?php if ($repetido): ?>
        <div class="huk-errores-cab">
            <i class="fa-solid fa-user-check" aria-hidden="true"></i>
            <div>
                <strong>Ya tiene una cuenta con <?= Html::encode($model->email) ?></strong>
                <p>No hace falta que se registre de nuevo: ingrese con ese correo. Si no recuerda la contraseña, puede recuperarla.</p>
                <p class="huk-errores-acciones">
                    <?= Html::a('<i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Ingresar', ['/cuenta/acceso/ingresar'], ['class' => 'huk-btn-oro']) ?>
                    <?= Html::a('Recuperar mi contraseña', ['/cuenta/acceso/olvide'], ['class' => 'btn huk-btn-linea']) ?>
                </p>
                <p class="huk-errores-nota">¿No es su correo? Cámbielo en «Datos de contacto» y vuelva a enviar.</p>
            </div>
        </div>
        <?php unset($errores['email']); ?>
    <?php endif; ?>
    <?php if ($errores): ?>
        <div class="huk-errores-cab">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
            <div>
                <strong><?= $repetido ? 'Además, revise' : 'Su solicitud todavía no se envió. Revise' ?> <?= count($errores) === 1 ? 'este dato' : 'estos ' . count($errores) . ' datos' ?>:</strong>
                <ul>
                    <?php foreach ($errores as $atributo => $error): ?>
                        <?php if ($atributo === 'inicio'): /* trampa de tiempo: no hay campo al que ir */ ?>
                            <li><?= Html::encode($error) ?></li>
                        <?php else: ?>
                            <li><a href="#<?= Html::getInputId($model, $atributo) ?>" data-ir-campo><?= Html::encode(preg_replace('/\s*\(opcional\)$/', '', $etiquetas[$atributo] ?? $atributo)) ?></a>: <?= Html::encode($error) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
    <p class="huk-errores-nota"><i class="fa-solid fa-lock" aria-hidden="true"></i> Por seguridad, vuelva a elegir su documento y a escribir la contraseña: el navegador no los guarda al recargar.</p>
</div>
