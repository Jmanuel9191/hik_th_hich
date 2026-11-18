




<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Productos */

$this->title = 'Actualizar: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Analisis', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<section class="content-header">
    <h1><?= Html::encode($this->title) ?></h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <?= $this->render('_form', [
                    'model' => $model,
                    
                ]) ?>
            </div>
        </div>
    </div>
</section>
