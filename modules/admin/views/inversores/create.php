<?php

/**
 * @var yii\web\View $this
 * @var app\models\SolicitudInversorForm $model
 * @var array $ecosistemas
 * @var bool $puedeAprobar
 */

use yii\helpers\Html;

$this->title = 'Cargar inversor';
?>

<section class="content-header">
    <h1><i class="fa fa-user-plus"></i> <?= Html::encode($this->title) ?></h1>
    <p class="huk-inicio-sub">La misma solicitud que se llena en la web. No hace falta contraseña: le enviamos un correo para que la cree.</p>
</section>

<section class="content">
    <?= $this->render('_form', ['model' => $model, 'ecosistemas' => $ecosistemas, 'puedeAprobar' => $puedeAprobar]) ?>
</section>
