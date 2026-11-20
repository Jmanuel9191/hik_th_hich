<?php

/**
 * @var yii\web\View $this
 * @var app\models\ProyectoPlantilla $model
 * @var array $ecosistemas
 */

$this->title = 'Nueva plantilla';
?>
<section class="content-header"><h1><i class="fa fa-clone"></i> Nueva plantilla de proyecto</h1></section>
<section class="content"><?= $this->render('_form', ['model' => $model, 'ecosistemas' => $ecosistemas]) ?></section>
