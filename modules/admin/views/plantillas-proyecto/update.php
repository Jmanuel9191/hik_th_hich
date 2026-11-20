<?php

/**
 * @var yii\web\View $this
 * @var app\models\ProyectoPlantilla $model
 * @var array $ecosistemas
 */

use yii\helpers\Html;

$this->title = 'Plantilla: ' . $model->nombre;
?>
<section class="content-header"><h1><i class="fa fa-clone"></i> <?= Html::encode($model->nombre) ?></h1></section>
<section class="content"><?= $this->render('_form', ['model' => $model, 'ecosistemas' => $ecosistemas]) ?></section>
