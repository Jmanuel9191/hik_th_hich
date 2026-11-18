<?php

/**
 * @var yii\web\View $this
 * @var app\models\Banners $model
 */

use yii\helpers\Html;

$this->title = 'Editar: ' . $model->nombre();
?>

<section class="content-header">
    <h1><i class="fa fa-picture-o"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
    <?= $this->render('_form', ['model' => $model]) ?>
</section>
