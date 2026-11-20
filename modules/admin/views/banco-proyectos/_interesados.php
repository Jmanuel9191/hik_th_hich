<?php

/**
 * Pestaña Interesados del proyecto: los "Quiero invertir" de la web, con su estado y el seguimiento del equipo.
 * Estados: Recibido → En contacto → En negociación → Concretado / Cerrado sin inversión.
 * Concretado + permiso de inversiones → "Registrar pago" con el proyecto y el inversor ya elegidos.
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $model
 * @var app\models\InteresInversion[] $interesados
 * @var bool $gestionarInteres
 * @var bool $registrarPago
 */

use app\models\InteresInversion;
use app\models\Inversor;
use yii\helpers\Html;

$fecha = fn($f) => date('d/m/Y H:i', strtotime($f));
$cuenta = array_count_values(array_map(fn($i) => $i->estado, $interesados));
$soloNumeros = fn($t) => preg_replace('/[^0-9]/', '', (string) $t);
$iconos = ['whatsapp' => 'fa fa-whatsapp', 'llamada' => 'fa fa-phone', 'correo' => 'fa fa-envelope', 'reunion' => 'fa fa-handshake-o']; // Font Awesome 4 del panel
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-hand-paper-o"></i> Inversores interesados</h3>
        <div class="box-tools huk-interes-resumen">
            <?php foreach (InteresInversion::ESTADOS as $clave => [$nombre, , $clase]): ?>
                <?php if (!empty($cuenta[$clave])): ?><span class="label <?= $clase ?>"><?= Html::encode($nombre) ?>: <?= (int) $cuenta[$clave] ?></span><?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="box-body">
        <p class="huk-ayuda"><i class="fa fa-info-circle"></i> Son manifestaciones de interés desde la web («Quiero invertir»), no compromisos.
            Se le prometió al inversor contactarlo en <?= InteresInversion::HORAS_CONTACTO ?> horas hábiles. Cuando se concrete, registre el pago en Inversiones.</p>

        <?php if (!$interesados): ?>
            <p class="text-muted huk-vacio"><i class="fa fa-inbox"></i> Todavía nadie envió su interés en este proyecto.</p>
        <?php endif; ?>

        <?php foreach ($interesados as $i): ?>
            <?php $u = $i->usuario; $inv = $i->inversor; $tel = $u->movil ?: $u->telefono; ?>
            <div class="huk-interes is-<?= Html::encode($i->estado) ?>" id="interes-<?= (int) $i->idinteres ?>">
                <div class="huk-interes-cab">
                    <div>
                        <strong class="huk-interes-nombre"><?= Html::a(Html::encode($inv ? $inv->nombreVisible() : $u->nombrecompleto), ['/admin/inversores/view', 'id' => $u->idusuario]) ?></strong>
                        <span class="label <?= $i->estadoClasePanel() ?>"><?= Html::encode($i->estadoNombre()) ?></span>
                        <div class="huk-eco-resumen">Envió su interés el <?= $fecha($i->fecha_registro) ?><?= $i->fecha_actualizacion !== $i->fecha_registro ? ' · actualizado el ' . $fecha($i->fecha_actualizacion) : '' ?></div>
                    </div>
                    <div class="huk-interes-monto"><span>Monto estimado</span><strong><?= Html::encode($i->montoTexto()) ?></strong></div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-condensed huk-interes-datos">
                            <tr><th>Participación</th><td><?= Html::encode(Inversor::PARTICIPACIONES[$i->participacion] ?? '—') ?></td></tr>
                            <tr><th>Plazo</th><td><?= Html::encode(Inversor::PLAZOS[$i->plazo] ?? '—') ?></td></tr>
                            <tr><th>Prefiere</th><td><i class="<?= $iconos[$i->contacto_medio] ?? 'fa fa-comment' ?>"></i> <?= Html::encode($i->medioTexto()) ?><?= $i->contacto_horario ? ' · ' . Html::encode($i->contacto_horario) : '' ?></td></tr>
                            <tr><th>Contacto</th><td>
                                <?php if ($tel): ?><?= Html::encode($tel) ?> <?= Html::a('<i class="fa fa-whatsapp"></i> Escribir', 'https://wa.me/' . $soloNumeros($tel), ['class' => 'btn btn-xs btn-success', 'target' => '_blank', 'rel' => 'noopener']) ?><br><?php endif; ?>
                                <?= Html::mailto(Html::encode($u->email), $u->email) ?>
                            </td></tr>
                            <?php if ($i->comentario): ?><tr><th>Comentario</th><td><?= nl2br(Html::encode($i->comentario)) ?></td></tr><?php endif; ?>
                        </table>
                        <?php if ($i->estado === 'concretado' && $registrarPago): ?>
                            <?= Html::a('<i class="fa fa-plus"></i> Registrar pago', ['/admin/inversiones/create', 'idproyecto' => $model->idproyecto, 'idinversor' => $u->idusuario],
                                ['class' => 'btn btn-success btn-sm']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <?php if ($gestionarInteres): ?>
                            <?= Html::beginForm(['interes-seguimiento', 'id' => $i->idinteres], 'post', ['class' => 'huk-interes-form']) ?>
                            <div class="form-group">
                                <label>Estado</label>
                                <?= Html::dropDownList('estado', $i->estado, array_map(fn($e) => $e[0], InteresInversion::ESTADOS), ['class' => 'form-control input-sm']) ?>
                            </div>
                            <div class="form-group">
                                <label>Nota de seguimiento <small class="text-muted">(interna: el inversor no la ve)</small></label>
                                <?= Html::textarea('nota', '', ['class' => 'form-control input-sm', 'rows' => 2, 'maxlength' => 1000, 'placeholder' => 'Ej.: Lo llamé, quedamos en reunirnos el jueves.']) ?>
                            </div>
                            <?= Html::submitButton('<i class="fa fa-save"></i> Guardar seguimiento', ['class' => 'btn btn-primary btn-sm']) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>
                        <?php if ($i->notas): ?>
                            <ul class="huk-interes-historial">
                                <?php foreach ($i->notas as $n): ?>
                                    <li class="is-<?= Html::encode($n->tipo) ?>">
                                        <span><?= $fecha($n->fecha) ?> · <?= Html::encode($n->tipo === 'inversor' ? 'Inversor' : ($n->usuario->nombrecompleto ?? 'Equipo')) ?></span>
                                        <?= nl2br(Html::encode($n->texto)) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
