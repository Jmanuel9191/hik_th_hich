<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Parametros */

$this->title = $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Parámetros', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">

                <div class="col-xs-12">
                    <h1 class="app-page-title"><?= Html::encode($model->nombre) ?></h1>
                </div>

                <div class="col-xs-12">
                    <div class="app-card-footer p-4 mt-auto">
                        <?= Html::a('Editar', ['update', 'id' => $model->idparametro], ['class' => 'btn btn-success']) ?>
                        
                    </div>
                </div>

                <div class="row gy-4">
                    <div class="col-xs-12" style="padding-bottom: 20px;">
                        <div class="app-card-account shadow-sm d-flex flex-column align-items-start">
                            <div class="app-card-body px-4 w-100">

                                <?= $this->render('appcuenta', ['label' => 'Nombre', 'datos' => $model->nombre]) ?>
                                <?= $this->render('appcuenta', ['label' => 'Unidad', 'datos' => $model->unidad ?: '—']) ?>
                                <?= $this->render('appcuenta', ['label' => 'Fecha de Registro', 'datos' => date('d/m/Y', strtotime($model->fecha_registro))]) ?>

                                <div class="col-12 col-sm-12 mspeo" style="">
                                    <br>
                                <div class="app-card-row">
                                    <label class="app-card-label" style="font-size: 20px;">Valores de Referencia</label>
                                    <div class="app-card-value">
                                        <?= $model->referencia_texto ?: '<em>Sin completar</em>' ?>
                                    </div>
                                </div>
                                   </div>
<div class="col-12 col-sm-12 mspeo" style=""> <br>
                                <div class="app-card-row">
                                    <label class="app-card-label" style="font-size: 20px;" >Usado en</label>
                                    <div class="app-card-value">
                                        <?php if (empty($model->productos)): ?>
                                            <em>Ningún producto todavía</em>
                                        <?php else: ?>
                                            <ul class="mb-0">
                                                <?php foreach ($model->productos as $p): ?>
                                                    <li><?= Html::encode($p->titulo) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                        <br>
                                    </div>
                                </div>
   </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <br>
</section>