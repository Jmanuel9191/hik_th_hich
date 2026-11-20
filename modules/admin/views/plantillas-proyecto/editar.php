<?php

/**
 * Secciones y bloques de una plantilla: el mismo editor de la página web del proyecto.
 *
 * @var yii\web\View $this
 * @var app\models\ProyectoPlantilla $model
 * @var array $editor PaginaProyectoService::editor('plantilla', …)
 */

use yii\helpers\Html;

$this->title = 'Plantilla: ' . $model->nombre;
?>
<section class="content-header">
    <h1><i class="fa fa-clone"></i> <?= Html::encode($model->nombre) ?></h1>
    <p class="huk-inicio-sub">
        Plantilla de página de proyecto<?= $model->ecosistema ? ' · se sugiere para ' . Html::encode($model->ecosistema->nombre) : '' ?>.
        Los textos entre « » son guías: el proyecto no se puede publicar hasta reemplazarlos.
    </p>
</section>
<section class="content">
    <div class="huk-barra-acciones">
        <?= Html::a('<i class="fa fa-arrow-left"></i> Plantillas', ['index'], ['class' => 'btn btn-default']) ?>
        <?= Html::a('<i class="fa fa-pencil"></i> Nombre y ecosistema', ['update', 'id' => $model->idplantilla], ['class' => 'btn btn-info']) ?>
    </div>
    <?= $this->render('/paginas-proyecto/_editor', $editor) ?>
</section>
