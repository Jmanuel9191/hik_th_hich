<?php

/**
 * Formulario de una sección (se carga en el modal). Fondo y diseño con dibujitos para elegir de un vistazo.
 *
 * @var yii\web\View $this
 * @var app\models\ProyectoSeccion $seccion
 * @var string $tipo
 * @var int $id
 */

use app\models\ProyectoSeccion;
use yii\helpers\Html;
use yii\helpers\Url;

$s = $seccion;
$accion = Url::to(['/admin/paginas-proyecto/seccion-guardar', 'tipo' => $tipo, 'id' => $id] + ($s->isNewRecord ? [] : ['idseccion' => $s->idseccion]));
$campo = fn($attr) => 'ProyectoSeccion[' . $attr . ']';
?>
<form data-pe-form action="<?= $accion ?>" novalidate>
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-list-alt"></i> <?= $s->isNewRecord ? 'Nueva sección' : 'Editar sección' ?></h4>
    </div>
    <div class="modal-body">
        <div class="alert alert-danger" data-pe-errores hidden></div>
        <div class="row">
            <div class="col-sm-6 form-group" data-campo="antetitulo">
                <label class="control-label" for="pe-s-ante"><?= $s->getAttributeLabel('antetitulo') ?></label>
                <?= Html::textInput($campo('antetitulo'), $s->antetitulo, ['id' => 'pe-s-ante', 'class' => 'form-control', 'maxlength' => 120, 'placeholder' => 'Síntesis ejecutiva']) ?>
                <p class="help-block">Va en dorado arriba del título; el número (01, 02…) se pone solo.</p>
            </div>
            <div class="col-sm-6 form-group" data-campo="menu">
                <label class="control-label" for="pe-s-menu"><?= $s->getAttributeLabel('menu') ?></label>
                <?= Html::textInput($campo('menu'), $s->menu, ['id' => 'pe-s-menu', 'class' => 'form-control', 'maxlength' => 40, 'placeholder' => 'Síntesis']) ?>
                <p class="help-block">Corto (1 o 2 palabras). Si lo deja vacío se usa el título.</p>
            </div>
        </div>
        <div class="form-group" data-campo="titulo">
            <label class="control-label" for="pe-s-titulo"><?= $s->getAttributeLabel('titulo') ?></label>
            <?= Html::textInput($campo('titulo'), $s->titulo, ['id' => 'pe-s-titulo', 'class' => 'form-control input-lg', 'maxlength' => 200, 'placeholder' => 'El proyecto en una página']) ?>
            <p class="help-block">Sin título ni antetítulo la sección no lleva encabezado (ej. el aviso legal del final).</p>
        </div>

        <div class="row">
            <div class="col-sm-6 form-group">
                <label class="control-label">Fondo</label>
                <div class="huk-pe-opciones">
                    <?php foreach (ProyectoSeccion::FONDOS as $valor => $nombre): ?>
                        <label class="huk-pe-opcion">
                            <?= Html::radio($campo('fondo'), $s->fondo === $valor, ['value' => $valor]) ?>
                            <span class="huk-pe-swatch is-<?= $valor ?>"></span><?= Html::encode($nombre) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-sm-6 form-group">
                <label class="control-label">Diseño</label>
                <div class="huk-pe-opciones">
                    <?php foreach (ProyectoSeccion::DISENOS as $valor => $nombre): ?>
                        <label class="huk-pe-opcion is-diseno" title="<?= Html::encode($nombre) ?>">
                            <?= Html::radio($campo('diseno'), $s->diseno === $valor, ['value' => $valor]) ?>
                            <span class="huk-pe-diseno is-<?= $valor ?>"><i></i><b></b><b></b></span>
                            <?= $valor === 'arriba' ? 'Título arriba' : 'Título a la izquierda' ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="help-block">«A la izquierda»: el título queda arriba de la columna izquierda y los bloques de la derecha al lado.</p>
            </div>
        </div>

        <div class="huk-pe-casillas">
            <?php foreach (['en_menu', 'numerada', 'visible'] as $casilla): ?>
                <label><?= Html::checkbox($campo($casilla), $s->$casilla === '1', ['value' => '1']) ?> <?= $s->getAttributeLabel($casilla) ?></label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success" data-pe-guardar><i class="fa fa-check"></i> Guardar sección</button>
    </div>
</form>
