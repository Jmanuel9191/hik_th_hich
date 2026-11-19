<?php

/**
 * Inversiones: todos los pagos (el gerente, los de los proyectos de sus áreas), con filtros y totales por moneda.
 *
 * @var yii\web\View $this
 * @var app\models\InversionSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $totales  por moneda (vigentes, con los filtros)
 * @var array $proyectos id => código
 * @var array $monedas  id => código
 * @var array $puede    registrar, anular, exportar
 */

use app\models\InversionPago;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

\app\assets_b\InversionesAdminAsset::register($this);

$this->title = 'Inversiones';
?>

<section class="content-header">
    <h1><i class="fa fa-money"></i> Inversiones</h1>
    <p class="huk-inicio-sub">Cada pago de cada inversor. Un inversor puede pagar varias veces: se suma. Los anulados quedan tachados y no suman.</p>
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
            <div class="huk-barra-acciones">
                <?php if ($puede['registrar']): ?>
                    <?= Html::a('<i class="fa fa-plus"></i> Registrar pago', ['create'], ['class' => 'btn btn-success']) ?>
                <?php endif; ?>
                <?php if ($puede['exportar']): ?>
                    <?= $this->render('/_exportar', ['ruta' => 'exportar']) ?>
                <?php endif; ?>
            </div>

            <?php $f = ActiveForm::begin(['method' => 'get', 'action' => ['index'], 'options' => ['class' => 'huk-filtros-pagos']]); ?>
            <div class="row">
                <div class="col-md-3 col-sm-12"><?= $f->field($searchModel, 'texto')->textInput(['placeholder' => 'Inversor, empresa, correo o referencia'])->label('Buscar') ?></div>
                <div class="col-md-3 col-sm-6"><?= $f->field($searchModel, 'idproyecto')->dropDownList($proyectos, ['prompt' => 'Todos'])->label('Proyecto') ?></div>
                <div class="col-md-2 col-sm-6"><?= $f->field($searchModel, 'tipo_pago')->dropDownList(InversionPago::TIPOS_PAGO, ['prompt' => 'Todos'])->label('Tipo de pago') ?></div>
                <div class="col-md-2 col-sm-6"><?= $f->field($searchModel, 'idmoneda')->dropDownList($monedas, ['prompt' => 'Todas'])->label('Moneda') ?></div>
                <div class="col-md-2 col-sm-6"><?= $f->field($searchModel, 'estado')->dropDownList(InversionPago::ESTADOS, ['prompt' => 'Todos'])->label('Estado') ?></div>
            </div>
            <div class="row">
                <div class="col-md-5 col-sm-8">
                    <label class="control-label">Fecha del pago (desde / hasta)</label>
                    <div class="huk-filtro-fechas">
                        <?= Html::activeInput('date', $searchModel, 'desde', ['class' => 'form-control', 'aria-label' => 'Desde']) ?>
                        <?= Html::activeInput('date', $searchModel, 'hasta', ['class' => 'form-control', 'aria-label' => 'Hasta']) ?>
                    </div>
                </div>
                <div class="col-md-7 col-sm-4 huk-filtros-botones">
                    <?= Html::submitButton('<i class="fa fa-filter"></i> Filtrar', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Limpiar', ['index'], ['class' => 'btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>

            <div class="huk-totales-pagos">
                <span class="huk-totales-titulo">Total (vigentes, con estos filtros):</span>
                <?php if (!$totales): ?>
                    <span class="text-muted">—</span>
                <?php endif; ?>
                <?php foreach ($totales as $t): ?>
                    <span class="huk-total-moneda"><strong><?= Html::encode(InversionPago::dinero($t['total'], $t['moneda'])) ?></strong>
                        <small><?= $t['pagos'] ?> <?= $t['pagos'] === 1 ? 'pago' : 'pagos' ?></small></span>
                <?php endforeach; ?>
            </div>

            <?= $this->render('_pagos', ['dataProvider' => $dataProvider, 'conProyecto' => true, 'puedeAnular' => $puede['anular'], 'puedeRegistrar' => $puede['registrar']]) ?>
        </div>
    </div>
</section>
