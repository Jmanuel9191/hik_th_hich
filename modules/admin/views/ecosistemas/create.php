<?php

/** @var yii\web\View $this @var app\models\Ecosistema $model */

use yii\helpers\Html;

$this->title = 'Nuevo ecosistema';
?>
<section class="content-header">
    <h1><i class="fa fa-th-large"></i> <?= Html::encode($this->title) ?></h1>
</section>
<section class="content">
    <?= $this->render('_form', ['model' => $model, 'paises' => $paises]) ?>
</section>
