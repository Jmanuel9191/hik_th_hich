<?php

/**
 * @var yii\web\View $this
 * @var app\models\Noticia $model
 * @var array $categorias
 * @var bool $puedeCategorias
 */

use yii\helpers\Html;

$this->title = 'Editar: ' . $model->titulo;
?>

<section class="content-header">
    <h1><i class="fa fa-newspaper-o"></i> <?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
    <?= $this->render('_form', ['model' => $model, 'categorias' => $categorias, 'puedeCategorias' => $puedeCategorias]) ?>
</section>
