<?php

/**
 * @var yii\web\View $this
 * @var app\models\SolicitudInversorForm $model
 * @var app\models\Inversor $inversor
 * @var array $ecosistemas
 * @var bool $puedeAprobar
 */

use yii\helpers\Html;

$this->title = 'Editar: ' . $inversor->nombreVisible();
?>

<section class="content-header">
    <h1><i class="fa fa-user"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
    <?= $this->render('_form', ['model' => $model, 'ecosistemas' => $ecosistemas, 'puedeAprobar' => $puedeAprobar]) ?>
</section>
