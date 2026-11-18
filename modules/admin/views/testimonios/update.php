<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Noticias */

$this->title = 'Testimonio: ' . $model->titulo;
$this->params['breadcrumbs'][] = ['label' => 'Testimonio', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->idtestimonio, 'url' => ['view', 'id' => $model->idtestimonio]];
$this->params['breadcrumbs'][] = 'Update';
?>
<section class="content-header">
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <h1><?= Html::encode($this->title)  ?></h1>
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</section>