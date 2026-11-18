<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($config->titulo_pagina ?? 'Sistema') ?></title>
</head>

<style>
    .parawhiote p,.parawhiote h2, .parawhiote span{
        color: white!important;

    }

    .im {
        color: white!important;
    }
</style>
<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 30px;">

<div style="max-width: 600px; margin: auto; background-color: #222222; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); padding: 30px;">

    <!-- LOGO -->
   

    <!-- CUERPO DEL MENSAJE -->
    <div class="parawhiote" style="font-size: 15px; color: white!important;">
        <?= $content ?>
    </div>

    <!-- PIE DE PÁGINA -->
    <hr style="margin-top: 30px; border: none; border-top: 1px solid #ddd;">
    <p style="font-size: 12px; color: #c7c7c7; text-align: center; margin-top: 20px;">
        Este correo fue generado automáticamente por el sistema de 	HUK THE ICHI – ONE THE ONE.<br>
        Si usted no solicitó este mensaje, puede ignorarlo con seguridad.
    </p>
</div>

</body>
</html>
