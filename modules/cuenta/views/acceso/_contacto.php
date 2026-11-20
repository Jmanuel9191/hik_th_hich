<?php

/**
 * Botones de contacto (WhatsApp, llamar, correo) de Panel → Configuración. Registro y "Solicitud enviada".
 *
 * @var array $contacto [[ícono, texto, valor, url], …] (AccesoController::contacto)
 */

use yii\helpers\Html;

if (!$contacto) {
    return;
}
?>
<ul class="huk-contacto-botones">
    <?php foreach ($contacto as [$icono, $texto, $valor, $url]): ?>
        <li>
            <a href="<?= Html::encode($url) ?>"<?= str_starts_with($url, 'http') ? ' target="_blank" rel="noopener"' : '' ?> class="<?= str_contains($icono, 'whatsapp') ? 'is-wa' : '' ?>">
                <i class="<?= Html::encode($icono) ?>" aria-hidden="true"></i>
                <span><small><?= Html::encode($texto) ?></small><?= Html::encode($valor) ?></span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
