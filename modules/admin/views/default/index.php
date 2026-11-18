<?php

/**
 * Inicio del panel: saludo y accesos a los módulos que el usuario puede usar.
 *
 * @var yii\web\View $this
 * @var array $accesos [[título, ícono, ruta, descripción], ...]
 * @var app\models\Usuarios $usuario
 */

use yii\helpers\Html;

$this->title = 'Inicio';
$nombre = explode(' ', trim((string) $usuario->nombrecompleto))[0] ?: $usuario->usuario;
?>

<section class="content-header">
    <h1><i class="fa fa-home"></i> Hola, <?= Html::encode($nombre) ?></h1>
    <p class="huk-inicio-sub"><?= Html::encode($usuario->rol->nombre ?? '') ?> · HUK-THE-ICHI GLOBAL GROUP</p>
</section>

<section class="content">
    <div class="row">
        <?php foreach ($accesos as [$titulo, $icono, $ruta, $texto]): ?>
            <div class="col-lg-4 col-sm-6">
                <?= Html::a(
                    '<span class="huk-acceso-icono"><i class="fa fa-' . $icono . '"></i></span>'
                    . '<span class="huk-acceso-texto"><strong>' . Html::encode($titulo) . '</strong><small>' . Html::encode($texto) . '</small></span>',
                    $ruta,
                    ['class' => 'huk-acceso']
                ) ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
