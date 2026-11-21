<?php

/**
 * Ficha del inversor → Inversiones: un bloque por proyecto en el que invirtió o tocó «Quiero invertir».
 * Cabecera: proyecto, estado de su interés, cuánto pagó, a cuánto equivale y qué parte de la ronda es.
 * Debajo, cada pago con su comprobante (Ver / subir / reemplazar) y Anular (la misma tabla de Inversiones).
 *
 * @var yii\web\View $this
 * @var app\models\Inversor $model
 * @var array $inversiones InterfaceInversiones::resumenInversor()
 * @var array $puede ['registrarPago', 'anularPago' => bool]
 * @var bool $puedeRegistrar   aprobado, cuenta activa y permiso
 */

use app\models\InversionPago;
use yii\helpers\Html;

\app\assets_b\InversionesAdminAsset::register($this);
$totalPagos = array_sum(array_map(fn($f) => $f['pagos']->getTotalCount(), $inversiones));
?>
<div class="box box-primary huk-inv-inversiones">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-money"></i> Inversiones</h3>
        <?php if ($inversiones): ?>
            <span class="huk-eco-resumen"> · <?= count($inversiones) ?> <?= count($inversiones) === 1 ? 'proyecto' : 'proyectos' ?> · <?= $totalPagos ?> <?= $totalPagos === 1 ? 'pago' : 'pagos' ?></span>
        <?php endif; ?>
        <?php if ($puedeRegistrar): ?>
            <div class="box-tools"><?= Html::a('<i class="fa fa-plus"></i> Registrar pago', ['inversiones/create', 'idinversor' => $model->idusuario, 'volver' => 'inversor'], ['class' => 'btn btn-success btn-sm']) ?></div>
        <?php endif; ?>
    </div>
    <div class="box-body">
        <?php if (!$inversiones): ?>
            <p class="text-muted huk-vacio"><i class="fa fa-inbox"></i> Todavía no invirtió ni pidió invertir en ningún proyecto.</p>
        <?php endif; ?>

        <?php foreach ($inversiones as $fila): ?>
            <?php $p = $fila['proyecto']; $i = $fila['interes']; ?>
            <div class="huk-inv-proyecto">
                <div class="huk-inv-proyecto-cab">
                    <div>
                        <?= Html::a(Html::encode($p->codigo), ['banco-proyectos/view', 'id' => $p->idproyecto, 'tab' => 'inversiones'], ['class' => 'huk-inv-proyecto-codigo']) ?>
                        <strong><?= Html::encode($p->titulo) ?></strong>
                        <div class="huk-eco-resumen">
                            <?= Html::encode($p->ecosistema->nombre ?? '') ?>
                            <?php if ($i): ?>
                                · «Quiero invertir»: <?= Html::a('<span class="label ' . $i->estadoClasePanel() . '">' . Html::encode($i->estadoNombre()) . '</span>',
                                    ['banco-proyectos/view', 'id' => $p->idproyecto, 'tab' => 'interesados', '#' => 'interes-' . $i->idinteres]) ?>
                                estimó <?= Html::encode($i->montoTexto()) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="huk-inv-proyecto-cifras">
                        <div><span>Pagó</span><strong><?= $fila['totales'] ? Html::encode(InversionPago::totalesTexto($fila['totales'])) : '—' ?></strong></div>
                        <div><span>Equivale a</span><strong><?= Html::encode(InversionPago::dinero($fila['equivalente'], $p->moneda)) ?></strong></div>
                        <div><span>De la ronda</span><strong><?= (float) $p->monto_objetivo ? Yii::$app->formatter->asDecimal($fila['equivalente'] * 100 / (float) $p->monto_objetivo, 1) . ' %' : '—' ?></strong></div>
                    </div>
                </div>
                <?php if ($fila['pagos']->getTotalCount()): ?>
                    <?= $this->render('/inversiones/_pagos', [
                        'dataProvider' => $fila['pagos'], 'conProyecto' => false, 'conInversor' => false,
                        'puedeAnular' => $puede['anularPago'], 'puedeRegistrar' => $puede['registrarPago'],
                    ]) ?>
                <?php else: ?>
                    <p class="text-muted huk-inv-sin-pagos">Todavía no pagó en este proyecto.</p>
                <?php endif; ?>
                <?php if ($puedeRegistrar): ?>
                    <?= Html::a('<i class="fa fa-plus"></i> Registrar pago en este proyecto', ['inversiones/create', 'idproyecto' => $p->idproyecto, 'idinversor' => $model->idusuario, 'volver' => 'inversor'],
                        ['class' => 'btn btn-default btn-xs']) ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
