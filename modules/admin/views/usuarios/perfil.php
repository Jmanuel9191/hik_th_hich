<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Usuarios */

$this->title = $model->nombrecompleto;
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
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

$selloHtml = '<span class="text-muted">Sin sello cargado</span>';
if (!empty($model->foto_sello)) {
    $selloSrc = $model->foto_sello;
    if (strpos($selloSrc, '/imagen/') === 0) {
        $selloSrc = Yii::$app->request->baseUrl . $selloSrc;
    }
    $selloHtml = Html::img($selloSrc, [
        'style' => 'max-width:250px; max-height:150px; border:1px solid #ddd; padding:3px; background:#fff;',
    ]);
}

?>

<?php
$ordenes = \app\models\Ordenes::find()
    ->where([
        'idusuario_registro' => $model['idusuario'],
        'tipo_orden'         => 'orden',
    ])
    ->orderBy(['fecha_registro' => SORT_DESC])
    ->limit(10)
    ->all();

$totalOrdenes = \app\models\Ordenes::find()
    ->where(['idusuario_registro' => $model['idusuario'], 'tipo_orden' => 'orden'])
    ->count();

$totalVendido = \app\models\Ordenes::find()
    ->where(['idusuario_registro' => $model['idusuario'], 'tipo_orden' => 'orden'])
    ->sum('monto_total') ?? 0;
?>


<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">


        <div class="col-xs-12">
            <h1 class="app-page-title">Mi cuenta</h1>
        </div>
        

      <div class="col-xs-12">
            <div class="app-card-footer p-4 mt-auto">
                        <a class="btn btn btn-success" href="<?= Url::to(['usuarios/updateperfil']) ?>">Editar Perfil</a>
                <a class="btn btn-default" href="<?= Url::to(['usuarios/updatepasswordperfil']) ?>">Editar contraseña</a>
             </div><!--//app-card-footer-->
        </div>
        <div class="row gy-4">
            <div class="col-xs-12">
                <div class="app-card-account shadow-sm d-flex flex-column align-items-start">
                    <div class="app-card-body px-4 w-100">
                        <?= $this->render('appcuenta', ['label' => 'Nombre Completo', 'datos' =>  $model['nombrecompleto'] ?: '—']) ?>
                        <?= $this->render('appcuenta', ['label' => 'Usuario', 'datos' =>  $model['usuario']]) ?>

                        <?= $this->render('appcuenta', ['label' => 'Tipo usuario', 'datos' =>  $tiposUsuario[$model->idrol] ?: '—']) ?>
                        <?= $this->render('appcuenta', ['label' => 'Email', 'datos' =>  $model['email'] ?: '—']) ?>
                        <?= $this->render('appcuenta', ['label' => 'Telefono', 'datos' =>  $model['telefono'] ?: '—']) ?>
                        <?= $this->render('appcuenta', ['label' => 'Movil', 'datos' =>  $model['movil'] ?: '—']) ?>
                        <?= $this->render('appcuenta', ['label' => 'Sexo', 'datos' =>  $model['sexo'] ?: '—']) ?>
                        <?= $this->render('appcuenta', ['label' => 'fecha de nacimiento', 'datos' =>  $newDatefn ?: '—']) ?>
                        <?= $this->render('appcuenta', ['label' => 'fecha registro', 'datos' =>  $newDatefreg]) ?>
                        <?= $this->render('appcuenta', ['label' => 'Cargo', 'datos' =>  $model['cargo'] ?: '—']) ?>
                        <?= $this->render('appcuenta', ['label' => 'Ciudad', 'datos' =>  $model['ciudad'] ?: '—']) ?>
                        <?= $this->render('appcuenta', ['label' => 'Direccion', 'datos' =>  $model['direccion'] ?: '—']) ?>
                        <div class="col-12 flexdesl" style="padding: 0">
                        <?= $this->render('appcuenta', ['label' => 'Sello / firma', 'datos' => $selloHtml]) ?>
                                            </div>
                    </div><!--//app-card-body-->


                </div><!--//app-card-->
            </div><!--//col-->



        </div><!--//row-->

    </div><!--//container-fluid-->




        </div><!--//row-->



<div class="col-xs-12" >
<hr style="
    margin-top: 0px;
">
<div class="box">
<div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-list"></i> Mis órdenes registradas</h3>
</div>
<div class="box-body">

    <!-- Tarjetitas -->
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
            <td><?= date('d/m/Y H:i', strtotime($o->fecha_registro)) ?></td>
            <td>
                <?= \yii\helpers\Html::a(
                    '<i class="fa fa-eye"></i>',
                    \yii\helpers\Url::to(['/admin/ordenes/view', 'id' => $o->idorden]),
                    ['class' => 'btn btn-xs btn-success']
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
        <p class="text-muted">No has registrado órdenes todavía.</p>
    <?php endif; ?>

</div>
</div>
</div>











    </div><!--//container-fluid-->
</section><!--//app-content-->
