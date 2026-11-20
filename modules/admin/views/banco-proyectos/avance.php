<?php

/**
 * Agregar / editar un avance. Al elegir etapa se sugiere su % (huk-proyectos.js: data-porcentajes-etapa).
 *
 * @var yii\web\View $this
 * @var app\models\ProyectoAvance $model
 * @var array $volver
 */

use app\models\Proyecto;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

\app\assets_b\ProyectosAdminAsset::register($this);

$p = $model->proyecto;
$this->title = $model->isNewRecord ? 'Nuevo avance' : 'Editar avance';
$conObra = $p->tipo === 'financiamiento';
?>

<section class="content-header">
    <h1><i class="fa fa-tasks"></i> <?= Html::encode($this->title) ?></h1>
    <p class="huk-inicio-sub"><?= Html::encode($p->codigo . ' · ' . $p->titulo) ?></p>
</section>

<section class="content">
    <?php $form = ActiveForm::begin(['id' => 'avance-form']); ?>
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-4"><?= $form->field($model, 'fecha')->input('date') ?></div>
                        <div class="col-sm-8"><?= $form->field($model, 'titulo')->textInput(['maxlength' => 160, 'placeholder' => 'Ej.: Inicio de obra civil']) ?></div>
                    </div>
                    <?= $form->field($model, 'texto')->textarea([
                        'data-editor' => true,
                        'data-editor-alto' => 340,
                        'data-tinymce-base' => Url::to('@web/assets_b/lib/tinymce'),
                        'rows' => 8,
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <?php if ($conObra): ?>
                <div class="box box-primary">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-tasks"></i> Etapa y avance (opcional)</h3></div>
                    <div class="box-body">
                        <?= $form->field($model, 'etapa')->dropDownList(Proyecto::ETAPAS, [
                            'prompt' => 'Sin cambio de etapa',
                            'data-porcentajes-etapa' => Json::encode(Proyecto::PORCENTAJE_ETAPA),
                        ])->hint('Etapa actual del proyecto: ' . ($p->etapaNombre() ?? 'sin definir') . '.') ?>
                        <?= $form->field($model, 'porcentaje', ['addon' => ['append' => ['content' => '%']]])
                            ->textInput(['type' => 'number', 'min' => 0, 'max' => 100, 'data-porcentaje-avance' => true])
                            ->hint('Al elegir etapa se sugiere su %; ajústelo si hace falta (ej. 62 si la construcción va por la mitad). Vacío = el de la etapa.') ?>
                    </div>
                </div>
            <?php endif; ?>
            <p class="huk-aviso-contacto"><i class="fa fa-paperclip"></i> Las fotos, videos y documentos del avance se agregan después de guardar, en la pestaña Avances.</p>
        </div>
    </div>
    <div class="huk-form-acciones text-center">
        <?= Html::submitButton('<i class="fa fa-save"></i> ' . ($model->isNewRecord ? 'Agregar avance' : 'Guardar cambios'), ['class' => 'btn btn-success btform']) ?>
        <?= Html::a('Cancelar', $volver, ['class' => 'btn btn-default btform']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</section>
