<?php

/**
 * "¡Solicitud enviada correctamente!": qué sigue (confirmar el correo → revisión → aviso de aprobación),
 * reenviar el correo de confirmación y cómo comunicarse. Llega después del registro (AccesoController::actionEnviada).
 *
 * @var yii\web\View $this
 * @var string $nombre
 * @var string $email
 * @var app\models\Banners|null $encabezado
 * @var array $contacto
 */

use yii\helpers\Html;

\app\assets_b\CuentaAsset::register($this);

$pasos = [
    ['fa-envelope-circle-check', 'Confirme su correo', 'Le enviamos un enlace a <strong>' . Html::encode($email) . '</strong>. Ábralo para confirmar que es suyo (si no lo ve, revise la carpeta de spam).', 'is-actual'],
    ['fa-user-check', 'Verificamos sus datos', 'Nuestro equipo revisa su solicitud y su documento. Es posible que nos comuniquemos con usted para conocer mejor su perfil.', ''],
    ['fa-bell', 'Le avisaremos por correo', 'Cuando su solicitud esté aprobada le llegará un correo con las áreas habilitadas y ya podrá ingresar.', ''],
];
?>
<?= $this->render('//site/widgets/encabezado', [
    'banner' => $encabezado, 'titulo' => 'Quiero ser socio', 'antetitulo' => 'Inversores y socios', 'tituloDelBanner' => true,
    'migas' => [['label' => 'Inicio', 'url' => ['/site/index']], ['label' => 'Acceso inversores', 'url' => ['/cuenta/acceso/ingresar']], ['label' => 'Solicitud enviada']],
]) ?>

<section class="huk-cuenta is-con-banner">
    <div class="container">
        <div class="huk-tarjeta-form is-media huk-enviada" data-reveal>
            <div class="huk-enviada-check" aria-hidden="true"><i class="fa-solid fa-check"></i></div>
            <h1>¡Solicitud enviada correctamente!</h1>
            <p class="huk-enviada-lead">Gracias, <strong><?= Html::encode($nombre) ?></strong>. Recibimos sus datos.
                Esté atento a su correo: le avisaremos cuando su solicitud sea aprobada.</p>

            <ol class="huk-enviada-pasos">
                <?php foreach ($pasos as $i => [$icono, $titulo, $texto, $clase]): ?>
                    <li class="<?= $clase ?>" style="--i:<?= $i ?>">
                        <span class="huk-enviada-paso-icono"><i class="fa-solid <?= $icono ?>" aria-hidden="true"></i></span>
                        <div><strong><?= $i + 1 ?>. <?= Html::encode($titulo) ?></strong><p><?= $texto ?></p></div>
                    </li>
                <?php endforeach; ?>
            </ol>

            <div class="huk-enviada-reenviar">
                <p>¿No le llegó el correo de confirmación?</p>
                <?= Html::beginForm(['/cuenta/acceso/reenviar'], 'post', ['class' => 'huk-reenviar']) ?>
                <?= Html::input('email', 'email', $email, ['class' => 'form-control', 'required' => true, 'aria-label' => 'Su correo']) ?>
                <?= Html::submitButton('Reenviar', ['class' => 'btn btn-outline-dark']) ?>
                <?= Html::endForm() ?>
            </div>

            <?php if ($contacto): ?>
                <div class="huk-enviada-contacto">
                    <p>¿Alguna consulta? Estamos para ayudarte:</p>
                    <?= $this->render('_contacto', ['contacto' => $contacto]) ?>
                </div>
            <?php endif; ?>
            <p class="mt-4 mb-0"><?= Html::a('<i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver al inicio', ['/site/index'], ['class' => 'huk-enlace-volver']) ?></p>
        </div>
    </div>
</section>
