<?php

/**
 * Mi cuenta: estado de la solicitud. Aprobado → áreas habilitadas con la cantidad de proyectos publicados.
 *
 * @var yii\web\View $this
 * @var app\models\Inversor $inversor
 * @var array $proyectos idecosistema => cantidad publicados
 */

use yii\helpers\Html;

\app\assets_b\CuentaAsset::register($this);

$estado = $inversor->estado_solicitud;
?>

<section class="huk-cuenta">
    <div class="container">
        <div class="huk-cuenta-cab">
            <span class="huk-cuenta-eyebrow">Mi cuenta</span>
            <h1>Hola, <?= Html::encode($inversor->usuario->nombrecompleto) ?></h1>
        </div>

        <div class="huk-tarjeta-form is-media">
            <?= $this->render('../_aviso') ?>

            <?php if ($estado === 'aprobado'): ?>
                <div class="huk-panel-estado">
                    <div class="huk-mensaje-icono"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></div>
                    <div><h2>Tu cuenta está aprobada.</h2> Estas son las áreas en las que podés ver proyectos.</div>
                </div>
                <div class="huk-panel-areas">
                    <?php foreach ($inversor->areas as $area): ?>
                        <div class="huk-panel-area">
                            <strong><?= Html::encode($area->nombre) ?></strong>
                            <span><?= $n = $proyectos[$area->idecosistema] ?? 0 ?> <?= $n === 1 ? 'proyecto publicado' : 'proyectos publicados' ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="huk-nota">Muy pronto vas a poder ver el detalle de cada proyecto desde acá.</p>

            <?php elseif ($estado === 'rechazado'): ?>
                <div class="huk-panel-estado">
                    <div class="huk-mensaje-icono"><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i></div>
                    <div><h2>Tu solicitud no fue aprobada.</h2>
                        <?php if ($inversor->motivo_rechazo): ?><?= Html::encode($inversor->motivo_rechazo) ?><?php endif; ?>
                        <br>Si querés más información, escribinos por los medios de contacto de la web.</div>
                </div>

            <?php else: ?>
                <div class="huk-panel-estado">
                    <div class="huk-mensaje-icono"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i></div>
                    <div><h2>Tu solicitud está en revisión.</h2> Nuestro equipo se va a comunicar con vos al
                        <?= Html::encode($inversor->usuario->telefono) ?> o a <?= Html::encode($inversor->usuario->email) ?>.
                        Cuando esté aprobada te avisamos por correo.</div>
                </div>
            <?php endif; ?>

            <?= Html::beginForm(['/cuenta/acceso/salir'], 'post', ['class' => 'huk-panel-salir']) ?>
            <?= Html::submitButton('<i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Cerrar sesión', ['class' => 'btn btn-outline-dark']) ?>
            <?= Html::endForm() ?>
        </div>
    </div>
</section>
