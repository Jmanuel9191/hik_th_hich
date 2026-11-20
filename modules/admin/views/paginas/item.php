<?php

/**
 * Agregar o editar un elemento de una lista (valor, principio, meta, tarjeta, bloque adicional…).
 * Los campos salen de la "lista" del bloque en PaginaSeccion::PAGINAS.
 *
 * @var yii\web\View $this
 * @var app\models\PaginaItem $model
 * @var array $volver
 */

use app\models\PaginaItem;
use app\models\PaginaSeccion;
use app\widgets\CampoImagen;
use app\widgets\MapaPaises;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\PaginasAdminAsset::register($this);
$this->params['pagina'] = $model->seccion->pagina; // menú lateral

$seccion = $model->seccion;
$cfgBloque = $seccion->config();
$lista = $cfgBloque['lista'];
$campos = $lista['campos'];
$pagina = PaginaSeccion::PAGINAS[$seccion->pagina];
$this->title = $model->isNewRecord ? 'Agregar ' . $lista['singular'] : 'Editar: ' . $model->titulo;
$imagenes = array_intersect_key($campos, array_flip(PaginaItem::IMAGENES));
?>

<section class="content-header">
    <h1><i class="fa fa-<?= Html::encode($cfgBloque['icono'] ?? 'pencil') ?>"></i> <?= Html::encode($this->title) ?></h1>
    <p class="huk-inicio-sub"><?= Html::encode($pagina['nombre'] . ' → ' . $pagina['pestanas'][$cfgBloque['pestana']]['nombre'] . ' → ' . $lista['nombre']) ?></p>
</section>

<section class="content">
    <?php $form = ActiveForm::begin([
        'id' => 'item-form',
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <div class="row">
        <div class="<?= $imagenes ? 'col-md-8' : 'col-md-12' ?>">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-font"></i> Datos</h3></div>
                <div class="box-body">
                    <?php foreach (array_diff_key($campos, $imagenes) as $campo => $etiqueta): ?>
                        <?php if ($campo === 'codigo'): ?>
                            <?= $form->field($model, 'codigo')->dropDownList(MapaPaises::PAISES, ['prompt' => 'Elija el país…']) ?>
                        <?php elseif ($campo === 'cifra'): ?>
                            <?= $form->field($model, 'cifra')->textInput(['maxlength' => 60]) ?>
                        <?php elseif ($campo === 'titulo'): ?>
                            <?= $form->field($model, 'titulo')->textInput(['maxlength' => 255]) ?>
                        <?php elseif ($model->conEditor()): ?>
                            <?= $form->field($model, 'texto')->textarea([
                                'data-editor' => true,
                                'data-editor-alto' => 360,
                                'data-tinymce-base' => Url::to('@web/assets_b/lib/tinymce'),
                                'rows' => 10,
                            ]) ?>
                        <?php else: ?>
                            <?= $form->field($model, 'texto')->textarea(['rows' => 3, 'maxlength' => 600])->hint('Máx. 600 caracteres.') ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if ($imagenes): ?>
            <div class="col-md-4">
                <?php foreach ($imagenes as $campo => $etiqueta): ?>
                    <div class="box box-primary">
                        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-picture-o"></i> <?= Html::encode($etiqueta) ?></h3></div>
                        <div class="box-body">
                            <?= $form->field($model, $campo)->widget(CampoImagen::class, $campo === 'icono'
                                ? ['url' => PaginaItem::url($model->icono), 'medida' => $lista['medida'] ?? '', 'icono' => true, 'maxMb' => 2, 'extensiones' => ['png', 'svg', 'webp', 'jpg', 'jpeg']]
                                : ['url' => PaginaItem::url($model->imagen), 'medida' => $lista['medida'] ?? ''])->label(false) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="huk-form-acciones text-center">
        <?= Html::submitButton('<i class="fa fa-save"></i> ' . ($model->isNewRecord ? 'Agregar' : 'Guardar cambios'), ['class' => 'btn btn-success btform']) ?>
        <?= Html::a('Cancelar', $volver, ['class' => 'btn btn-default btform']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</section>
