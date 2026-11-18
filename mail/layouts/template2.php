<?php
/**
 * Plantilla de todos los correos (estilo HUK: negro + dorado).
 * En correos los estilos van en línea: Gmail/Outlook ignoran los archivos .css.
 *
 * @var string $content  HTML del mensaje (ya escapado por CorreoService)
 * @var app\models\Configuracion|null $config
 */
use yii\helpers\Html;
use yii\helpers\Url;

$logo = Url::to('@web/assets_b/img/logo.png', true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($config->titulo_pagina ?? 'HUK-THE-ICHI') ?></title>
    <style>
        .parawhiote p, .parawhiote span, .im { color: #ffffff !important; }
    </style>
</head>
<body style="margin: 0; padding: 30px 12px; background-color: #f4f1ea; font-family: Figtree, Arial, Helvetica, sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="max-width: 600px; background-color: #0b0b0b; border-radius: 10px; border-top: 4px solid #bd944f;">

                <!-- LOGO -->
                <tr>
                    <td align="center" style="padding: 28px 30px 18px; border-bottom: 1px solid #2a2418;">
                        <img src="<?= Html::encode($logo) ?>" width="250" alt="HUK-THE-ICHI GLOBAL GROUP"
                             style="display: block; width: 250px; max-width: 100%; height: auto; border: 0;">
                    </td>
                </tr>

                <!-- CUERPO DEL MENSAJE -->
                <tr>
                    <td class="parawhiote" style="padding: 26px 30px 10px; font-size: 15px; line-height: 1.6; color: #ffffff;">
                        <?= $content ?>
                    </td>
                </tr>

                <!-- PIE DE PÁGINA -->
                <tr>
                    <td style="padding: 0 30px 26px;">
                        <hr style="margin: 20px 0 0; border: none; border-top: 1px solid #2a2418;">
                        <p style="font-size: 12px; color: #9a8f7a; text-align: center; margin: 18px 0 0;">
                            Este correo fue generado automáticamente por el sistema de HUK THE ICHI – ONE THE ONE.<br>
                            Si usted no solicitó este mensaje, puede ignorarlo con seguridad.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
