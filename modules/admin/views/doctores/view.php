<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Usuarios */

$this->title = $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Doctor', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php
use yii\helpers\Url;
$user = Yii::$app->session->get('user');
$originalDatefechan = $model->fecha_nacimiento ?? null;
$newDatefn = date("d/m/Y", strtotime($originalDatefechan));
$tiposUsuario = \app\models\Usuarios::getTipoUsuario();
$originalDatefechreg = $model->fecha_registro ?? null;
$newDatefreg = date("d/m/Y", strtotime($originalDatefechreg));
?>

<?php
$ordenes = \app\models\Ordenes::find()
    ->where([
        'iddoctor'   => $model['iddoctor'],
        'tipo_orden' => 'orden',
    ])
    ->orderBy(['fecha_registro' => SORT_DESC])
    ->limit(10)
    ->all();

$totalOrdenes = \app\models\Ordenes::find()
    ->where(['iddoctor' => $model['iddoctor'], 'tipo_orden' => 'orden'])
    ->count();

$totalVendido = \app\models\Ordenes::find()
    ->where(['iddoctor' => $model['iddoctor'], 'tipo_orden' => 'orden'])
    ->sum('monto_total') ?? 0;
?>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">


        <div class="col-xs-12">
            <h1 class="app-page-title"><?= $model['nombre'] ?>
</h1>
        </div>
        

      <div class="col-xs-12">
            <div class="app-card-footer p-4 mt-auto">
                       <a class="btn btn-success" href="<?= Url::to(['update?id='. $model['iddoctor']]) ?>">Editar</a>
                </div><!--//app-card-footer-->
        </div>
        <div class="row gy-4">
            <div class="col-xs-12">
                <div class="app-card-account shadow-sm d-flex flex-column align-items-start">
                    <div class="app-card-body px-4 w-100">
                        
                         <?= $this->render('appcuenta', ['label' => 'Nombre Completo', 'datos' => $model['nombre'] ?: '—']) ?>
<?= $this->render('appcuenta', ['label' => 'Email',           'datos' => $model['email'] ?: '—']) ?>
<?= $this->render('appcuenta', ['label' => 'Telefono',        'datos' => $model['telefono'] ?: '—']) ?>
<?= $this->render('appcuenta', ['label' => 'fecha registro',  'datos' => $newDatefreg ?: '—']) ?>
<?= $this->render('appcuenta', ['label' => 'Algun detalle',   'datos' => $model['descripcion'] ?: '—']) ?>
                    </div><!--//app-card-body-->


                </div><!--//app-card-->
            </div><!--//col-->



        </div><!--//row-->

    </div><!--//container-fluid-->




        </div><!--//row-->










<?php
$ordenes = \app\models\Ordenes::find()
    ->where([
        'iddoctor'   => $model['iddoctor'],
        'tipo_orden' => 'orden',
    ])
    ->orderBy(['fecha_registro' => SORT_DESC])
    ->limit(10)
    ->all();

$totalOrdenes = \app\models\Ordenes::find()
    ->where(['iddoctor' => $model['iddoctor'], 'tipo_orden' => 'orden'])
    ->count();

$totalVendido = \app\models\Ordenes::find()
    ->where(['iddoctor' => $model['iddoctor'], 'tipo_orden' => 'orden'])
    ->sum('monto_total') ?? 0;
?>

<div class="col-xs-12">
<hr style="
    margin-top: 0px;
">
<div class="box">
<div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-list"></i> Órdenes asociadas a: <?= $model['nombre'] ?> </h3>
</div>
<div class="box-body">

    <div class="row cjafera" style="margin-bottom:15px">
        <div class="col-sm-4">
            <div class="info-box">
                <span class="info-box-icon bg-blue"><i class="fa fa-file-text"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total órdenes</span>
                    <span class="info-box-number"><?= $totalOrdenes ?></span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total vendido</span>
                    <span class="info-box-number">Bs. <?= number_format($totalVendido, 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($ordenes)): ?>
    <table class="table table-bordered table-condensed table-hover">
        <thead>
            <tr>
                <th>N° Ref</th>
                <th>Paciente</th>
                <th>Total (Bs)</th>
                <th>Estado</th>
                <th>Tipo pago</th>
                <th>Registrado por</th>
                <th>Fecha</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($ordenes as $o): ?>
        <tr>
            <td><strong><?= \yii\helpers\Html::encode($o->numero_referencia ?: '#'.$o->idorden) ?></strong></td>
            <td><?= \yii\helpers\Html::encode($o->paciente_nombre) ?></td>
            <td>Bs. <?= number_format($o->monto_total, 2) ?></td>
            <td>
                <?php $cls = $o->estado_pago === 'pagado' ? 'success' : 'warning'; ?>
                <span class="label label-<?= $cls ?>"><?= $o->estadoPagoLabel ?></span>
            </td>
            <td><?= $o->tipoPagoLabel ?></td>
            <td><?= \yii\helpers\Html::encode($o->usuarioRegistro->nombrecompleto ?? '—') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($o->fecha_registro)) ?></td>
            <td>
                <?= \yii\helpers\Html::a(
                    '<i class="fa fa-eye"></i>',
                    \yii\helpers\Url::to(['/admin/ordenes/view', 'id' => $o->idorden]),
                    ['class' => 'btn btn-xs btn-info']
                ) ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($totalOrdenes > 10): ?>
        <p class="text-muted text-right">
            <small>Mostrando las últimas 10 de <?= $totalOrdenes ?> órdenes.</small>
        </p>
    <?php endif; ?>
    <?php else: ?>
        <p class="text-muted">Este doctor no tiene órdenes asociadas todavía.</p>
    <?php endif; ?>

</div>
</div>
</div>





        

    </div><!--//container-fluid-->
</section><!--//app-content-->
