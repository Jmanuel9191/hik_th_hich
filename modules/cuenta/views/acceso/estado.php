<?php

/**
 * Ingresó con la clave correcta pero todavía no está aprobado (o fue rechazado): no entra a Mi cuenta,
 * ve su estado y cómo comunicarse con la empresa (Panel → Configuración).
 *
 * @var yii\web\View $this
 * @var string $estado   revision | rechazado
 * @var string $nombre
 * @var string|null $motivo
 * @var array $contacto  [[ícono, texto, valor, url], …]
 */

use yii\helpers\Html;

\app\assets_b\CuentaAsset::register($this);
$rechazado = $estado === 'rechazado';
?>

<section class="huk-cuenta">
    <div class="container">
        <div class="huk-tarjeta-form is-media huk-mensaje huk-estado is-<?= $rechazado ? 'rechazado' : 'revision' ?>">
            <div class="huk-mensaje-icono"><i class="fa-solid <?= $rechazado ? 'fa-circle-xmark' : 'fa-hourglass-half' ?>" aria-hidden="true"></i></div>
            <span class="huk-estado-etiqueta"><?= $rechazado ? 'Solicitud no aprobada' : 'En revisión' ?></span>
            <h1><?= $rechazado ? 'Su solicitud fue rechazada' : 'Su solicitud está siendo procesada' ?></h1>
            <p>Hola, <strong><?= Html::encode($nombre) ?></strong>.
                <?= $rechazado
                    ? 'Revisamos su solicitud y por ahora no pudimos aprobarla, por eso no puede ingresar a la plataforma.'
                    : 'Nuestro equipo está verificando sus datos. Podrá ingresar en cuanto su solicitud sea aprobada: le avisaremos por correo.' ?></p>
            <?php if ($rechazado && $motivo): ?>
                <p class="huk-estado-motivo"><strong>Motivo:</strong> <?= Html::encode($motivo) ?></p>
            <?php endif; ?>
            <p class="huk-estado-consulta"><?= $rechazado ? 'Si tiene alguna consulta, comuníquese con nosotros:' : '¿Tiene alguna consulta? Comuníquese con nosotros:' ?></p>
            <?= $this->render('_contacto', ['contacto' => $contacto]) ?>
            <p class="mt-4"><?= Html::a('<i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver al inicio', ['/site/index'], ['class' => 'huk-btn-linea']) ?></p>
        </div>
    </div>
</section>
