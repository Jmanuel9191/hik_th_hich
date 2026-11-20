<?php

/**
 * Mi cuenta → Mis inversiones: por proyecto, el total y cada pago con su comprobante.
 * Los pagos los registra el equipo (Superadmin / gerente del área) desde el panel; acá solo se consultan.
 *
 * @var yii\web\View $this
 * @var array $inversiones [['proyecto', 'enlace', 'totales', 'pagos'], ...]
 * @var app\models\InteresInversion[] $intereses  sus "Quiero invertir" (arriba, con el estado)
 */

use yii\helpers\Html;

$fecha = fn($f) => Yii::$app->formatter->asDate($f, 'php:d/m/Y');
?>

<?php if ($intereses): ?>
    <div class="huk-perfil-bloque">
        <div class="huk-perfil-bloque-cab">
            <h2><i class="fa-solid fa-hand-holding-dollar" aria-hidden="true"></i> Mis solicitudes de inversión</h2>
        </div>
        <ul class="huk-solicitudes">
            <?php foreach ($intereses as $s): ?>
                <li>
                    <div>
                        <strong><?= $s->proyecto ? Html::a(Html::encode($s->proyecto->titulo), ['/proyectos/ver', 'slug' => $s->proyecto->slug]) : '—' ?></strong>
                        <span><?= Html::encode($s->montoTexto()) ?> · enviada el <?= $fecha($s->fecha_registro) ?> · por <?= Html::encode($s->medioTexto()) ?></span>
                    </div>
                    <span class="huk-solicitud-estado is-<?= Html::encode($s->estado) ?>"><?= Html::encode($s->estadoNombre()) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="huk-perfil-nota"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Son manifestaciones de interés: no lo comprometen. Nuestro equipo actualiza el estado a medida que avanza la conversación.</p>
    </div>
<?php endif; ?>

<?php if (!$inversiones && !$intereses): ?>
    <div class="huk-perfil-bloque huk-perfil-vacio">
        <i class="fa-solid fa-sack-dollar" aria-hidden="true"></i>
        <h2>Todavía no tiene inversiones registradas</h2>
        <p>Si un proyecto le interesa, toque «Quiero invertir» en su página. Cuando se registre un pago a su nombre, lo verá aquí con su comprobante.</p>
        <?= Html::a('Ver mis proyectos <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>', ['/proyectos/index'], ['class' => 'huk-btn-oro huk-btn-chico']) ?>
    </div>
<?php endif; ?>

<?php foreach ($inversiones as $i): ?>
    <?php $p = $i['proyecto']; ?>
    <article class="huk-perfil-bloque huk-inversion">
        <header class="huk-inversion-cab">
            <div>
                <span class="huk-inversion-ante"><?= Html::encode(trim(($p->ecosistema->nombre ?? '') . ' · ' . $p->codigo, ' ·')) ?></span>
                <h2><?= $i['enlace'] ? Html::a(Html::encode($p->titulo), ['/proyectos/ver', 'slug' => $p->slug]) : Html::encode($p->titulo) ?></h2>
            </div>
            <div class="huk-inversion-total">
                <span>Total invertido</span>
                <strong><?= Html::encode($i['totales']) ?></strong>
                <small><?= count($i['pagos']) ?> <?= count($i['pagos']) === 1 ? 'pago' : 'pagos' ?></small>
            </div>
        </header>
        <div class="table-responsive">
            <table class="table huk-inversion-tabla">
                <thead><tr><th>Fecha</th><th>Monto</th><th>Forma de pago</th><th>Referencia</th><th class="text-end">Comprobante</th></tr></thead>
                <tbody>
                <?php foreach ($i['pagos'] as $pago): ?>
                    <tr>
                        <td data-rotulo="Fecha"><?= $fecha($pago->fecha_pago) ?></td>
                        <td data-rotulo="Monto"><span><strong><?= Html::encode($pago->montoTexto()) ?></strong>
                            <?php if ((int) $pago->idmoneda !== (int) $pago->idmoneda_proyecto): ?><small class="d-block">≈ <?= Html::encode($pago->equivalenteTexto()) ?></small><?php endif; ?></span></td>
                        <td data-rotulo="Forma de pago"><?= Html::encode($pago->tipoPagoTexto()) ?></td>
                        <td data-rotulo="Referencia"><?= Html::encode($pago->referencia ?: '—') ?></td>
                        <td data-rotulo="Comprobante" class="text-end">
                            <?= $pago->comprobante_archivo
                                ? Html::a('<i class="fa-solid fa-file-invoice" aria-hidden="true"></i> Ver', ['/cuenta/panel/comprobante', 'id' => $pago->idpago],
                                    ['class' => 'huk-btn-linea huk-btn-mini', 'target' => '_blank', 'rel' => 'noopener'])
                                : '<span class="text-muted">—</span>' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
<?php endforeach; ?>

<?php if ($inversiones): ?>
    <p class="huk-perfil-nota"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Los pagos los registra nuestro equipo. Si falta alguno o ve un dato incorrecto, escríbanos.</p>
<?php endif; ?>
