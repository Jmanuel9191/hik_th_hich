<?php

/**
 * @var yii\web\View $this
 * @var app\models\Proyecto $model
 * @var array $ecosistemas
 * @var array $gerentes
 */

use yii\helpers\Html;

$this->title = 'Nuevo proyecto';
?>

<section class="content-header">
    <h1><i class="fa fa-briefcase"></i> <?= Html::encode($this->title) ?></h1>
    <p class="huk-inicio-sub">Se crea en <strong>borrador</strong>: los inversores no lo ven hasta que se publique.</p>
</section>

<section class="content">
    <?= $this->render('_form', ['model' => $model, 'ecosistemas' => $ecosistemas, 'gerentes' => $gerentes]) ?>
</section>
