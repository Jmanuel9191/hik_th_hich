<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ROL';

$originalDate = $model->fecha_registro ?? null;
$newDate = $originalDate ? date("d/m/Y", strtotime($originalDate)) : '-';
?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">

        <div class="col-12 col-sm-6">
            <h1 class="app-page-title">DETALLE DEL ROL</h1>
        </div>

        <div class="col-12 col-sm-6">
            <div class="app-card-footer p-4 mt-auto">
                <a class="btn app-btn-secondary" href="<?= Url::to(['catalogos/rol-update', 'id' => $model->idrol]) ?>">Editar Rol</a>
                <a class="btn app-btn-secondary" href="<?= Url::to(['catalogos/rol-listar']) ?>">Volver a la lista</a>
            </div>
        </div>

        <div class="row gy-4">
            <div class="col-12">
                <div class="app-card-account shadow-sm d-flex flex-column align-items-start">
                    <div class="app-card-body px-4 w-100">
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Nombre del Rol', 'datos' => $model->nombre]) ?>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Estado', 'datos' => ($model->estado == 1 ? 'Activo' : 'Inactivo')]) ?>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Fecha de Registro', 'datos' => $newDate]) ?>


                        <div class="col-12 col-lg-12 col-md-12 col-sm-12 mspeo" style="padding-left: 0">

                        <div class="item border-bottom py-3 app-card">
                                <div class="row row text-left">
                                    <div class="col-auto">

                                        <h4 class="mt-2">Permisos Asignados:</h4>
                                        <ul class="rolesper">
                                                <?php foreach ($model->getPermisos()->all() as $permiso): ?>
                                                    <li>
                                                        <strong><?= Html::encode($permiso->alias) ?></strong><br>
                                                        <span style="margin-left: 10px;"><?= Html::encode($permiso->descripcion) ?></span>
                                                    </li>
                                                <?php endforeach; ?>

                                        </ul>
                                    </div><!--//col-->

                                </div><!--//row-->
                            </div><!--//item-->
                        </div>




                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
