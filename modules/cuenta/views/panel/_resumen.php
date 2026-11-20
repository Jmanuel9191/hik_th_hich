<?php

/**
 * Mi cuenta → Resumen: estado de la solicitud, áreas habilitadas, pedido de más áreas y acuerdo de confidencialidad.
 *
 * @var yii\web\View $this
 * @var app\models\Inversor $inversor
 * @var app\models\NdaAceptacion|null $nda
 * @var app\models\TerminosVersion|null $terminos
 * @var array $proyectos
 * @var app\models\Ecosistema[] $pendientes
 * @var array $disponibles
 */

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

$estado = $inversor->estado_solicitud;
$u = $inversor->usuario;
$fechaHora = fn($f) => Yii::$app->formatter->asDatetime($f, 'php:d/m/Y \a \l\a\s H:i');
?>

<?php if ($estado === 'aprobado'): ?>
    <div class="huk-perfil-bloque">
        <div class="huk-perfil-bloque-cab">
            <h2><i class="fa-solid fa-layer-group" aria-hidden="true"></i> Mis áreas</h2>
            <?= Html::a('Ver todos mis proyectos <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>', ['/proyectos/index'], ['class' => 'huk-btn-oro huk-btn-chico']) ?>
        </div>
        <div class="huk-panel-areas">
            <?php foreach ($inversor->areas as $area): ?>
                <?php $n = $proyectos[$area->idecosistema] ?? 0; ?>
                <?= Html::a('<strong>' . Html::encode($area->nombre) . '</strong><span>' . $n . ' ' . ($n === 1 ? 'proyecto' : 'proyectos')
                    . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>',
                    ['/proyectos/index', 'ecosistema' => $area->slug], ['class' => 'huk-panel-area']) ?>
            <?php endforeach; ?>
        </div>

        <?php if ($pendientes): ?>
            <p class="huk-perfil-pendientes"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i> Pedido en revisión:
                <?php foreach ($pendientes as $p): ?><span class="huk-chip"><?= Html::encode($p->nombre) ?></span><?php endforeach; ?>
            </p>
        <?php endif; ?>

        <?php if ($disponibles): ?>
            <details class="huk-perfil-pedir">
                <summary><i class="fa-solid fa-circle-plus" aria-hidden="true"></i> Solicitar más áreas</summary>
                <?= Html::beginForm(['/cuenta/panel/areas'], 'post', ['class' => 'huk-form']) ?>
                <p class="form-text">Elija las áreas que le interesan. Nuestro equipo revisa el pedido y le avisa por correo cuando estén habilitadas.</p>
                <div class="huk-areas">
                    <?php foreach ($disponibles as $id => $nombre): ?>
                        <label><?= Html::checkbox('areas[]', false, ['value' => $id]) ?> <?= Html::encode($nombre) ?></label>
                    <?php endforeach; ?>
                </div>
                <?= Html::submitButton('<i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Enviar pedido', ['class' => 'huk-btn-oro huk-btn-chico mt-3']) ?>
                <?= Html::endForm() ?>
            </details>
        <?php endif; ?>
    </div>

<?php elseif ($estado === 'rechazado'): ?>
    <div class="huk-perfil-bloque huk-perfil-estado-bloque">
        <div class="huk-mensaje-icono"><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i></div>
        <div><h2>Su solicitud no fue aprobada.</h2>
            <?php if ($inversor->motivo_rechazo): ?><p><?= Html::encode($inversor->motivo_rechazo) ?></p><?php endif; ?>
            <p>Si desea más información, escríbanos por los medios de contacto de la web.</p></div>
    </div>

<?php else: ?>
    <div class="huk-perfil-bloque huk-perfil-estado-bloque">
        <div class="huk-mensaje-icono"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i></div>
        <div><h2>Su solicitud está en revisión.</h2>
            <p>Nuestro equipo se comunicará con usted al <?= Html::encode($u->movil ?: $u->telefono) ?> o a <?= Html::encode($u->email) ?>.
                Cuando esté aprobada le avisaremos por correo.</p></div>
    </div>
<?php endif; ?>

<div class="huk-perfil-bloque">
    <div class="huk-perfil-bloque-cab">
        <h2><i class="fa-solid fa-file-signature" aria-hidden="true"></i> Documentos aceptados</h2>
    </div>
    <div class="huk-perfil-firmas">
        <div class="huk-perfil-firma<?= $nda ? ' is-ok' : '' ?>">
            <i class="fa-solid <?= $nda ? 'fa-circle-check' : 'fa-clock' ?>" aria-hidden="true"></i>
            <div>
                <strong>Acuerdo de confidencialidad</strong>
                <?php if ($nda): ?>
                    <span>Versión <?= (int) ($nda->version->numero ?? 0) ?> · firmado como «<?= Html::encode($nda->firma) ?>»</span>
                    <span><?= $fechaHora($nda->fecha) ?></span>
                    <?php if ($nda->version): ?>
                        <button type="button" class="huk-perfil-leer" data-bs-toggle="modal" data-bs-target="#modal-nda">Leer el acuerdo</button>
                    <?php endif; ?>
                <?php else: ?>
                    <span>Se firma al ingresar por primera vez, cuando su cuenta esté aprobada.</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="huk-perfil-firma<?= $inversor->terminos_fecha ? ' is-ok' : '' ?>">
            <i class="fa-solid <?= $inversor->terminos_fecha ? 'fa-circle-check' : 'fa-minus' ?>" aria-hidden="true"></i>
            <div>
                <strong>Términos de uso y privacidad</strong>
                <?php if ($inversor->terminos_fecha): ?>
                    <span>Versión <?= (int) $inversor->terminos_version ?> · aceptados al registrarse</span>
                    <span><?= $fechaHora($inversor->terminos_fecha) ?></span>
                    <?php if ($terminos): ?>
                        <button type="button" class="huk-perfil-leer" data-bs-toggle="modal" data-bs-target="#modal-terminos-perfil">Leer los términos</button>
                    <?php endif; ?>
                <?php else: ?>
                    <span>Su cuenta la creó nuestro equipo.</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php foreach (array_filter([
    'modal-nda' => $nda && $nda->version ? [$nda->version, 'Acuerdo firmado el ' . Yii::$app->formatter->asDate($nda->fecha, 'php:d/m/Y')] : null,
    'modal-terminos-perfil' => $terminos ? [$terminos, 'Aceptados el ' . Yii::$app->formatter->asDate($inversor->terminos_fecha, 'php:d/m/Y')] : null,
]) as $idModal => [$doc, $ante]): ?>
    <div class="modal fade huk-modal-terminos" id="<?= $idModal ?>" tabindex="-1" aria-labelledby="<?= $idModal ?>-titulo" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="huk-modal-ante">Versión <?= (int) $doc->numero ?> · <?= Html::encode($ante) ?></span>
                        <h2 class="modal-title" id="<?= $idModal ?>-titulo"><?= Html::encode($doc->titulo) ?></h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body huk-rich"><?= HtmlPurifier::process($doc->texto) ?></div>
                <div class="modal-footer">
                    <button type="button" class="huk-btn-oro" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
