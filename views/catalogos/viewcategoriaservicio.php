<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'CATEGORÍA DE SERVICIO';

$originalDate = $model->fecha_registro ?? null;
$newDate = $originalDate ? date("d/m/Y", strtotime($originalDate)) : '-';
?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">

        <div class="col-12 col-sm-6">
            <h1 class="app-page-title">DETALLE DE CATEGORÍA DE SERVICIO</h1>
        </div>

        <div class="col-12 col-sm-6">
            <div class="app-card-footer p-4 mt-auto">
                <a class="btn app-btn-secondary" href="<?= Url::to(['catalogos/categoria-servicio-update', 'id' => $model->idcategoria]) ?>">Editar Categoría</a>
                <a class="btn app-btn-secondary" href="<?= Url::to(['catalogos/categoria-servicio-listar']) ?>">Volver a la lista</a>
            </div>
        </div>

        <div class="row gy-4">
            <div class="col-12">
                <div class="app-card-account shadow-sm d-flex flex-column align-items-start">
                    <div class="app-card-body px-4 w-100">
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Nombre', 'datos' => $model->nombre]) ?>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Alias', 'datos' => $model->alias]) ?>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Padre', 'datos' => ($model->padre ? $model->padre->nombre : 'Sin padre')]) ?>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Estado', 'datos' => ($model->estado == 1 ? 'Activo' : 'Inactivo')]) ?>
                        <?= $this->render('../widgets/appcuenta', ['label' => 'Fecha de Registro', 'datos' => $newDate]) ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
