<?php

use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;

//$model = \app\models\Promociones::find()->one();


/* @var $this yii\web\View */
/* @var $model app\models\Productos */

$this->title = 'Inventario';

$id="";
@$nombre = $_GET['id'];
$cac = \app\models\Inventario::find()->where(['idproducto' => $producto['idproducto']])->orderBy(['idinventario' => SORT_DESC])->limit(1)->all();
?>

<style>
    .bg-red, .callout.callout-danger, .alert-danger, .alert-error, .label-danger, .modal-danger .modal-body {
        background-color: #2196f3 !important;
    }


    .btn-danger {
        background-color: #2196f3!important;
        border-color: #2196f3!important;
    }
</style>


<section class="content-header">

    <h1><?= Html::encode($this->title) ?> de <?= $producto['titulo'] ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
</section>

<!-- Main content -->
<section class="content">

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-sm-4">
            <div class="box">

                <div class="box-body " align="center">
                    <?php

                    $form = ActiveForm::begin([
                        'type' => ActiveForm::TYPE_HORIZONTAL,
                        'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL]
                    ]); ?>
                    <?= $form->field($model, 'movimiento')->textInput(['maxlength' => true, 'value' => 0, 'placeholder' => '0']) ?>
                    <?= $form->field($model, 'estado')->dropDownList(\app\models\Inventario::tipos()) ?>

                    <div class="form-group field-fecha_vencimiento">
                        <label class="control-label col-sm-3">Vence (solo entradas)</label>
                        <div class="col-sm-9">
                            <input type="date" name="fecha_vencimiento" class="form-control">
                        </div>
                    </div>

                    <div class="form-group field-idlote_forzado">
    <label class="control-label col-sm-3">Lote a usar (solo salidas)</label>
    <div class="col-sm-9">
        <select name="idlote_forzado" class="form-control">
            <option value="">Automático (recomendado - FEFO)</option>
            <?php foreach ($lotes as $lote): ?>
                <option value="<?= $lote->idlote ?>">
                    <?= $lote->codigo_lote ?> — vence <?= $lote->fecha_vencimiento ?: 'sin fecha' ?> — <?= $lote->cantidad_actual ?> disponibles
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

                    <?= Html::a('Cancelar', Yii::$app->request->referrer, ['class' => 'btn btn-default']) ?>
                    <?= Html::submitButton('Guardar', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    <?php ActiveForm::end(); ?>

                </div>
            </div>
<div class="info-box">
    <span class="info-box-icon bg-red"><i class="fa fa-archive"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">Saldo</span>
        <span class="info-box-number">
            <?= \app\models\InventarioLote::stockTotal($producto['idproducto']) ?>
        </span>
    </div>
</div>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Lotes activos (Stock: <?= $stockTotal ?>)</h3>
                </div>
                <div class="box-body">
                    <?php if (empty($lotes)): ?>
                        <p class="text-muted">No hay lotes activos registrados.</p>
                    <?php else: ?>
                        <table class="table table-bordered table-condensed">
                            <thead>
                                <tr>
                                    <th>Lote</th>
                                    <th>Cant.</th>
                                    <th>Vence</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($lotes as $lote):
                                $alerta = $lote->getAlerta();
                                $clase = $alerta == 'vencido' ? 'danger' : ($alerta == 'por_vencer' ? 'warning' : '');
                            ?>
                                <tr class="<?= $clase ?>">
                                    <td><?= $lote->codigo_lote ?></td>
                                    <td><?= $lote->cantidad_actual ?></td>
                                    <td><?= $lote->fecha_vencimiento ?: '-' ?></td>
                                    <td>
                                        <?php if ($alerta == 'vencido'): ?>
                                            <span class="label label-danger">Vencido</span>
                                        <?php elseif ($alerta == 'por_vencer'): ?>
                                            <span class="label label-warning">Por vencer</span>
                                        <?php else: ?>
                                            <span class="label label-success">OK</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>


        </div>


        <div class="col-sm-8">
            <div class="box">

                <div class="box-header with-border">
                    <h3 class="box-title">Historial de Movimientos</h3>
                </div>
                <div class="box-body">
                    <?= \yii\grid\GridView::widget([
                        'dataProvider' => $lista,
                        //'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            'fecha_registro',
                            'movimiento',

                            'detalle',
                            [
                                'header' => 'Lote(s)',
                                'value' => function ($model) {
                                    return $model->loteInfo;
                                },
                            ],
                            [
                                'attribute' => 'estado',
                                'value' => function ($model) {
                                    return \app\models\Inventario::tipos($model->estado);
                                },
                            ],
                        ],
                    ]); ?>

                </div>
            </div>

        </div>
    </div>
</section>