<?php

/**
 * Cargar una plantilla en el proyecto: viene elegida la sugerida para su ecosistema.
 * Si el proyecto ya tiene secciones, se reemplazan (lo confirma huk-pagina-proyecto.js).
 *
 * @var yii\web\View $this
 * @var int $id idproyecto
 * @var array $plantillas id => nombre
 * @var int|null $sugerida
 * @var bool $conSecciones
 */

use yii\helpers\Html;
use yii\helpers\Url;
?>
<form class="huk-pe-plantilla" data-pe-plantilla-form action="<?= Url::to(['/admin/paginas-proyecto/aplicar-plantilla', 'id' => $id]) ?>" data-reemplaza="<?= $conSecciones ? '1' : '0' ?>">
    <div class="huk-pe-plantilla-icono"><i class="fa fa-magic"></i></div>
    <div class="huk-pe-plantilla-txt">
        <strong>Empezar desde una plantilla</strong>
        <span>Carga las secciones típicas con textos guía entre « ». Después las cambia, ordena u oculta como desee.</span>
    </div>
    <?= Html::dropDownList('idplantilla', $sugerida, $plantillas, ['class' => 'form-control', 'aria-label' => 'Plantilla']) ?>
    <button type="submit" class="btn btn-primary"><i class="fa fa-download"></i> Cargar</button>
</form>
