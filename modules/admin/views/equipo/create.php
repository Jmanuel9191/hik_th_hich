<?php

/**
 * @var yii\web\View $this
 * @var app\models\Equipo $model
 */

use yii\helpers\Html;

$this->title = 'Agregar persona al equipo';
?>

<section class="content-header">
    <h1><i class="fa fa-id-badge"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
    <?= $this->render('_form', ['model' => $model]) ?>
</section>
