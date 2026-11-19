<?php

/**
 * Ficha del inversor: datos de la solicitud, documento, decisión (aprobar con áreas / rechazar), cuenta, NDA e historial.
 * Rechazar, bloquear/activar y reenviar: huk-inversores.js.
 *
 * @var yii\web\View $this
 * @var app\models\Inversor $model
 * @var app\models\NdaAceptacion[] $aceptaciones
 * @var app\models\NdaVersion|null $vigente
 * @var yii\data\ActiveDataProvider|null $historial
 * @var array $ecosistemas id => nombre
 * @var array|null $inversiones pagos por proyecto (null = sin permiso)
 * @var array $puede ['editar','aprobar','estado','registrarPago' => bool]
 */

use app\models\Inversor;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

\app\assets_b\InversoresAdminAsset::register($this);

$u = $model->usuario;
$this->title = $model->nombreVisible();
$fecha = fn($v) => $v ? date('d/m/Y H:i', strtotime($v)) : '—';
$activa = $u->estado === '1';
$estado = $model->estado_solicitud;
$aceptoVigente = $vigente === null || in_array($vigente->idversion, array_map(fn($a) => (int) $a->idversion, $aceptaciones), true);
$reenviable = ($model->origen === 'web' && $estado === 'sin_verificar') || ($model->origen === 'admin' && !$model->email_verificado);
$marcadas = array_map('intval', array_column($estado === 'aprobado' ? $model->areas : $model->intereses, 'idecosistema'));
$intereses = array_map('intval', array_column($model->intereses, 'idecosistema'));
?>

<section class="content-header">
    <h1><i class="fa fa-handshake-o"></i> <?= Html::encode($this->title) ?></h1>
    <p class="huk-inicio-sub">
        <span class="label <?= $model->estadoClase() ?>"><?= Html::encode($model->estadoNombre()) ?></span>
        <?php if (!$activa): ?><span class="label label-danger">Cuenta bloqueada</span><?php endif; ?>
        · <?= Html::encode(Inversor::TIPOS[$model->tipo] ?? '') ?>
        · <?= $model->origen === 'web' ? 'Se registró en la web' : 'Cargado en el panel' ?> el <?= date('d/m/Y', strtotime($model->fecha_registro)) ?>
    </p>
</section>

<section class="content">
    <div class="huk-barra-acciones">
        <?= Html::a('<i class="fa fa-arrow-left"></i> Volver', ['index', 'tab' => $activa ? $estado : 'bloqueado'], ['class' => 'btn btn-default']) ?>
        <?php if ($puede['editar']): ?>
            <?= Html::a('<i class="fa fa-pencil"></i> Editar datos', ['update', 'id' => $model->idusuario], ['class' => 'btn btn-info']) ?>
        <?php endif; ?>
        <?php if ($reenviable): ?>
            <?= Html::button('<i class="fa fa-envelope-o"></i> ' . ($model->origen === 'web' ? 'Reenviar confirmación de correo' : 'Reenviar activación de cuenta'), [
                'class' => 'btn btn-default',
                'data-inversor-post' => Url::to(['reenviar', 'id' => $model->idusuario]),
                'data-titulo' => '¿Reenviar el correo?',
                'data-texto' => 'Se le manda un enlace nuevo a ' . $u->email . '. El anterior deja de servir.',
            ]) ?>
        <?php endif; ?>
        <?php if ($puede['aprobar'] && in_array($estado, ['pendiente', 'aprobado'], true)): ?>
            <?= Html::button('<i class="fa fa-ban"></i> Rechazar', [
                'class' => 'btn btn-danger',
                'data-inversor-motivo' => Url::to(['rechazar', 'id' => $model->idusuario]),
                'data-titulo' => '¿Rechazar la solicitud?',
                'data-texto' => 'El motivo se le envía por correo al inversor. Si estaba aprobado, deja de ver los proyectos.',
                'data-boton' => 'Rechazar',
                'data-color' => '#a33a2c',
            ]) ?>
        <?php endif; ?>
        <?php if ($puede['estado']): ?>
            <?= Html::button('<i class="fa ' . ($activa ? 'fa-lock' : 'fa-unlock') . '"></i> ' . ($activa ? 'Bloquear cuenta' : 'Reactivar cuenta'), [
                'class' => 'btn ' . ($activa ? 'btn-warning' : 'btn-success') . ' huk-boton-derecha',
                'data-inversor-motivo' => Url::to(['estado', 'id' => $model->idusuario]),
                'data-titulo' => $activa ? '¿Bloquear la cuenta?' : '¿Reactivar la cuenta?',
                'data-texto' => $activa ? 'No va a poder entrar. Si está conectado, se le cierra la sesión. Se le avisa por correo con el motivo.' : 'Vuelve a poder entrar. Se le avisa por correo.',
                'data-boton' => $activa ? 'Bloquear' : 'Reactivar',
                'data-color' => $activa ? '#b07a12' : '#2f7d4f',
            ]) ?>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-id-card-o"></i> Quién es</h3></div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped detail-view'],
                        'attributes' => array_values(array_filter([
                            $model->tipo === 'empresa' ? ['label' => 'Razón social', 'value' => $model->razon_social] : null,
                            $model->tipo === 'empresa' ? ['label' => 'NIT / registro', 'value' => $model->nit] : null,
                            ['label' => $model->tipo === 'empresa' ? 'Representante' : 'Nombre completo', 'value' => $u->nombrecompleto . ($model->tipo === 'empresa' && $u->cargo ? ' · ' . $u->cargo : '')],
                            ['label' => 'Documento', 'value' => (Inversor::DOCUMENTOS[$model->documento_tipo] ?? $model->documento_tipo) . ' ' . $model->documento_numero],
                            ['label' => 'Correo', 'format' => 'raw', 'value' => Html::mailto(Html::encode($u->email), $u->email)],
                            ['label' => 'Celular (WhatsApp)', 'format' => 'raw', 'value' => Html::encode($u->movil)
                                . ' ' . Html::a('<i class="fa fa-whatsapp"></i> Escribir', 'https://wa.me/' . preg_replace('/\D/', '', (string) $u->movil), ['target' => '_blank', 'rel' => 'noopener', 'class' => 'btn btn-success btn-xs'])],
                            ['label' => 'Ubicación', 'value' => $u->ciudad . ', ' . $u->pais],
                            $model->sitio_web ? ['label' => 'Sitio web', 'format' => 'raw', 'value' => Html::a(Html::encode($model->sitio_web), $model->sitio_web, ['target' => '_blank', 'rel' => 'noopener nofollow'])] : null,
                        ])),
                    ]) ?>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-search"></i> Qué busca</h3></div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped detail-view'],
                        'attributes' => [
                            ['label' => 'Áreas de interés', 'value' => implode(', ', array_column($model->intereses, 'nombre')) ?: '—'],
                            ['label' => 'Monto', 'value' => $model->montoTexto() ?: '—'],
                            ['label' => 'Plazo', 'value' => Inversor::PLAZOS[$model->plazo_preferido] ?? '—'],
                            ['label' => 'Participación', 'value' => Inversor::PARTICIPACIONES[$model->participacion_preferida] ?? '—'],
                            ['label' => '¿Cómo nos conoció?', 'value' => $model->como_nos_conocio ?: '—'],
                            ['label' => 'Mensaje', 'format' => 'ntext', 'value' => $model->mensaje ?: '—'],
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text-o"></i> Acuerdo de confidencialidad</h3></div>
                <div class="box-body">
                    <?php if (!$aceptaciones): ?>
                        <p class="text-muted">Todavía no aceptó ninguna versión<?= $estado === 'aprobado' ? ': lo hace la primera vez que entre.' : ' (se acepta después de ser aprobado).' ?></p>
                    <?php else: ?>
                        <?php if (!$aceptoVigente): ?>
                            <p class="huk-aviso-contacto"><i class="fa fa-exclamation-triangle"></i> Hay una versión nueva (v<?= $vigente->numero ?>) que todavía no aceptó: se le pide al entrar.</p>
                        <?php endif; ?>
                        <table class="table table-striped">
                            <thead><tr><th>Versión</th><th>Firma</th><th>Fecha</th><th>IP</th></tr></thead>
                            <tbody>
                            <?php foreach ($aceptaciones as $a): ?>
                                <tr>
                                    <td><?= Html::a('v' . $a->version->numero, ['/admin/nda/index', 'ver' => $a->version->numero]) ?></td>
                                    <td><?= Html::encode($a->firma) ?></td>
                                    <td class="text-nowrap"><?= $fecha($a->fecha) ?></td>
                                    <td><?= Html::encode($a->ip) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($inversiones !== null): ?>
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-money"></i> Inversiones</h3></div>
                <div class="box-body table-responsive">
                    <?php if (!$inversiones): ?>
                        <p class="text-muted">Todavía no tiene pagos registrados.</p>
                    <?php else: ?>
                        <table class="table table-striped">
                            <thead><tr><th>Proyecto</th><th>Pagó</th><th>Equivale a</th><th>De la ronda</th></tr></thead>
                            <tbody>
                            <?php foreach ($inversiones as $fila): $p = $fila['proyecto']; ?>
                                <tr>
                                    <td><?= Html::a(Html::encode($p->codigo), ['banco-proyectos/view', 'id' => $p->idproyecto, 'tab' => 'inversiones']) ?>
                                        <div class="huk-eco-resumen"><?= Html::encode($p->titulo) ?></div></td>
                                    <td><?= Html::encode(\app\models\InversionPago::totalesTexto($fila['totales'])) ?>
                                        <?php if ($fila['anulados']): ?><div class="huk-eco-resumen"><?= $fila['anulados'] ?> anulado<?= $fila['anulados'] > 1 ? 's' : '' ?></div><?php endif; ?></td>
                                    <td><?= Html::encode(\app\models\InversionPago::dinero($fila['equivalente'], $p->moneda)) ?></td>
                                    <td><?= (float) $p->monto_objetivo ? Yii::$app->formatter->asDecimal($fila['equivalente'] * 100 / (float) $p->monto_objetivo, 1) . ' %' : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <?php if ($puede['registrarPago'] && $estado === 'aprobado' && $activa): ?>
                        <?= Html::a('<i class="fa fa-plus"></i> Registrar pago', ['inversiones/create', 'idinversor' => $model->idusuario, 'volver' => 'inversor'], ['class' => 'btn btn-success btn-sm']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Historial</h3></div>
                <div class="box-body">
                    <?php if ($historial === null): ?>
                        <p class="text-muted">No tenés permiso para ver el historial.</p>
                    <?php else: ?>
                        <?php Pjax::begin(['id' => 'inversor-historial', 'timeout' => 5000]); ?>
                        <?= GridView::widget([
                            'dataProvider' => $historial,
                            'tableOptions' => ['class' => 'table table-hover'],
                            'emptyText' => 'Todavía no hay movimientos.',
                            'summary' => '{totalCount} movimiento(s)',
                            'columns' => [
                                ['label' => 'Fecha', 'value' => fn($h) => $fecha($h->fecha), 'contentOptions' => ['class' => 'text-nowrap']],
                                ['label' => 'Acción', 'format' => 'raw', 'value' => fn($h) => Html::tag('strong', Html::encode($h->accion))],
                                ['label' => 'Detalle', 'format' => 'ntext', 'value' => fn($h) => (string) $h->descripcion],
                                ['label' => 'Hecho por', 'value' => fn($h) => (int) $h->idusuario_actor === (int) $model->idusuario ? 'El inversor'
                                    : ($h->usuarioActor->usuario ?? ($h->idusuario_actor ? 'Usuario #' . $h->idusuario_actor : 'El inversor (web)'))],
                                ['label' => 'IP', 'value' => fn($h) => $h->ip ?: '—'],
                            ],
                        ]) ?>
                        <?php Pjax::end(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-primary huk-decision">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-check-square-o"></i> Decisión</h3></div>
                <div class="box-body">
                    <?php if ($estado === 'sin_verificar' && $model->origen === 'web'): ?>
                        <p class="text-muted">Esperando que confirme su correo. Hasta entonces no se puede aprobar.</p>
                    <?php elseif (!$puede['aprobar']): ?>
                        <p><span class="label <?= $model->estadoClase() ?>"><?= Html::encode($model->estadoNombre()) ?></span></p>
                        <?php if ($estado === 'aprobado'): ?><p>Áreas: <strong><?= Html::encode(implode(', ', array_column($model->areas, 'nombre'))) ?></strong></p><?php endif; ?>
                        <p class="text-muted">La aprobación la da el distribuidor.</p>
                    <?php else: ?>
                        <?php if ($estado === 'rechazado'): ?>
                            <p class="huk-aviso-contacto"><i class="fa fa-ban"></i> Rechazado. Motivo: <?= Html::encode($model->motivo_rechazo) ?></p>
                        <?php endif; ?>
                        <?= Html::beginForm([$estado === 'aprobado' ? 'areas' : 'aprobar', 'id' => $model->idusuario], 'post') ?>
                        <p><strong><?= $estado === 'aprobado' ? 'Áreas que puede ver' : 'Aprobar con estas áreas' ?></strong></p>
                        <?php foreach ($ecosistemas as $id => $nombre): ?>
                            <div class="checkbox huk-check-area">
                                <label>
                                    <?= Html::checkbox('areas[]', in_array((int) $id, $marcadas, true), ['value' => $id]) ?>
                                    <?= Html::encode($nombre) ?>
                                    <?= in_array((int) $id, $intereses, true) ? '<small class="huk-pidio">pidió</small>' : '' ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <?= Html::submitButton($estado === 'aprobado' ? '<i class="fa fa-save"></i> Guardar áreas' : '<i class="fa fa-check"></i> Aprobar',
                            ['class' => 'btn btn-block ' . ($estado === 'aprobado' ? 'btn-info' : 'btn-success')]) ?>
                        <?= Html::endForm() ?>
                        <?php if ($estado !== 'aprobado'): ?>
                            <p class="huk-ayuda">Al aprobar le llega un correo. La primera vez que entre, acepta el acuerdo de confidencialidad.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-id-badge"></i> Documento de identidad</h3></div>
                <div class="box-body">
                    <?php if ($model->documento_archivo): ?>
                        <?= Html::a('<i class="fa fa-eye"></i> Ver documento', ['documento', 'id' => $model->idusuario], ['class' => 'btn btn-default btn-block', 'target' => '_blank', 'rel' => 'noopener']) ?>
                        <p class="huk-ayuda"><?= Html::encode($model->documento_nombre) ?> · cada vez que se abre queda en el historial.</p>
                    <?php else: ?>
                        <p class="text-muted">Sin documento cargado.</p>
                    <?php endif; ?>
                    <?php if ($puede['editar']): ?>
                        <?= Html::beginForm(['documento-subir', 'id' => $model->idusuario], 'post', ['enctype' => 'multipart/form-data', 'class' => 'huk-subir-documento']) ?>
                        <?= Html::fileInput('documento', null, ['class' => 'form-control input-sm', 'accept' => '.jpg,.jpeg,.png,.webp,.pdf', 'required' => true]) ?>
                        <?= Html::submitButton('<i class="fa fa-upload"></i> ' . ($model->documento_archivo ? 'Reemplazar' : 'Subir'), ['class' => 'btn btn-default btn-sm']) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-user-circle"></i> Cuenta</h3></div>
                <div class="box-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped detail-view'],
                        'attributes' => [
                            ['label' => 'Estado', 'format' => 'raw', 'value' => $activa ? '<span class="label label-success">Activa</span>' : '<span class="label label-danger">Bloqueada</span>'],
                            ['label' => 'Correo confirmado', 'value' => $fecha($model->email_verificado)],
                            ['label' => 'Términos aceptados', 'value' => $model->terminos_fecha ? $fecha($model->terminos_fecha) . ' (IP ' . $model->terminos_ip . ')' : '—'],
                            ['label' => 'Aprobado', 'value' => $fecha($model->fecha_aprobacion)],
                            ['label' => 'Último acceso', 'value' => $u->ultimo_acceso ? $fecha($u->ultimo_acceso) . ' (IP ' . $u->ultimo_acceso_ip . ')' : 'Nunca entró'],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</section>
