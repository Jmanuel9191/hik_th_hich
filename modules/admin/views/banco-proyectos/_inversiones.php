<?php

/**
 * Pestaña Inversiones del proyecto: cuánto se juntó de la ronda, cuánto puso cada inversor y todos los pagos.
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $model
 * @var array $inversiones resumen (InversionService::resumenProyecto)
 */

use app\models\InversionPago;
use yii\helpers\Html;

\app\assets_b\InversionesAdminAsset::register($this);

$r = $inversiones;
$moneda = $model->moneda;
$objetivo = (float) $model->monto_objetivo;
$pct = $r['porcentaje'];
?>

<div class="row">
    <div class="col-lg-4">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-pie-chart"></i> La ronda</h3></div>
            <div class="box-body">
                <?php if (!$moneda): ?>
                    <p class="text-muted">El proyecto no tiene moneda definida. Editalo y elegí la moneda para poder registrar pagos.</p>
                <?php else: ?>
                    <p class="huk-ronda-dato"><span>Juntado</span><strong><?= Html::encode(InversionPago::dinero($r['recaudado'], $moneda)) ?></strong></p>
                    <?php if ($pct !== null): ?>
                        <p class="huk-ronda-dato"><span><?= Html::encode($model->getAttributeLabel('monto_objetivo')) ?></span><?= Html::encode(InversionPago::dinero($objetivo, $moneda)) ?></p>
                        <div class="progress huk-progreso-avance">
                            <div class="progress-bar<?= $pct >= 100 ? ' progress-bar-success' : '' ?>" role="progressbar" data-ancho="<?= (int) min(100, $pct) ?>"><?= Yii::$app->formatter->asDecimal($pct, 1) ?> %</div>
                        </div>
                        <p class="huk-ayuda"><?= $pct >= 100
                            ? 'La ronda está completa. Si corresponde, pasá el proyecto a "Inversión completa".'
                            : 'Falta ' . Html::encode(InversionPago::dinero(max(0, $objetivo - $r['recaudado']), $moneda)) . '.' ?></p>
                    <?php else: ?>
                        <p class="text-muted">Sin "<?= Html::encode($model->getAttributeLabel('monto_objetivo')) ?>" cargado: no se puede calcular el %.</p>
                    <?php endif; ?>
                    <p class="huk-ayuda">Suman solo los pagos vigentes. Los pagos en otra moneda suman lo que se indicó como equivalente.</p>
                    <?php if (count($r['totales']) > 1 || ($r['totales'] && (int) $r['totales'][0]['moneda']->idmoneda !== (int) $model->idmoneda)): ?>
                        <p class="huk-ronda-dato"><span>Pagado por moneda</span><?= Html::encode(InversionPago::totalesTexto($r['totales'])) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($r['puedeRegistrar']): ?>
                    <p class="text-center"><?= Html::a('<i class="fa fa-plus"></i> Registrar pago', ['inversiones/create', 'idproyecto' => $model->idproyecto, 'volver' => 'proyecto'], ['class' => 'btn btn-success']) ?></p>
                <?php elseif (!in_array($model->estado, InversionPago::ESTADOS_PROYECTO, true)): ?>
                    <p class="huk-ayuda">Los pagos se registran cuando el proyecto está publicado o con la inversión completa.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-users"></i> Inversores (<?= count($r['porInversor']) ?>)</h3></div>
            <div class="box-body table-responsive">
                <?php if (!$r['porInversor']): ?>
                    <p class="text-muted">Todavía nadie invirtió en este proyecto.</p>
                <?php else: ?>
                    <table class="table table-striped">
                        <thead><tr><th>Inversor</th><th>Pagó</th><th>Equivale a</th><th>Pagos</th><th>De la ronda</th></tr></thead>
                        <tbody>
                        <?php foreach ($r['porInversor'] as $id => $fila): ?>
                            <tr>
                                <td><?= Html::a(Html::encode($fila['inversor'] ? $fila['inversor']->nombreVisible() : '#' . $id), ['inversores/view', 'id' => $id]) ?></td>
                                <td><?= Html::encode(InversionPago::totalesTexto($fila['totales'])) ?></td>
                                <td><?= Html::encode(InversionPago::dinero($fila['equivalente'], $moneda)) ?></td>
                                <td><?= $fila['pagos'] ?></td>
                                <td><?= $objetivo ? Yii::$app->formatter->asDecimal($fila['equivalente'] * 100 / $objetivo, 1) . ' %' : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list"></i> Pagos</h3>
        <?php if ($r['anulados']): ?><small class="text-muted">· <?= $r['anulados'] ?> anulado<?= $r['anulados'] > 1 ? 's' : '' ?> (tachados, no suman)</small><?php endif; ?>
    </div>
    <div class="box-body">
        <?= $this->render('/inversiones/_pagos', ['dataProvider' => $r['dataProvider'], 'conProyecto' => false,
            'puedeAnular' => $r['puedeAnular'], 'puedeRegistrar' => $r['puedeRegistrar']]) ?>
    </div>
</div>
