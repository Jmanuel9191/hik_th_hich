<?php

/**
 * @var yii\web\View $this
 * @var app\models\Proyecto $model
 * @var array $ecosistemas
 * @var array $gerentes
 */

use yii\helpers\Html;

$this->title = 'Editar: ' . $model->titulo;
?>

<section class="content-header">
    <h1><i class="fa fa-briefcase"></i> <?= Html::encode($this->title) ?></h1>
    <p class="huk-inicio-sub"><?= Html::encode($model->codigo) ?> · <span class="label <?= $model->estadoClase() ?>"><?= Html::encode($model->estadoNombre()) ?></span></p>
</section>

<section class="content">
    <?= $this->render('_form', ['model' => $model, 'ecosistemas' => $ecosistemas, 'gerentes' => $gerentes]) ?>
</section>
