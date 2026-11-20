<?php

/**
 * Editar un bloque fijo de una página. Los campos salen de PaginaSeccion::PAGINAS.
 * Imágenes (foto, firma…; PaginaSeccion::IMAGENES): app\widgets\CampoImagen, una caja por imagen.
 * Texto con formato: editor (EditorAsset).
 *
 * @var yii\web\View $this
 * @var app\models\PaginaSeccion $model
 * @var array $volver  ruta de vuelta (pestaña del bloque)
 */

use app\models\PaginaSeccion;
use app\widgets\CampoImagen;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\PaginasAdminAsset::register($this);
$this->params['pagina'] = $model->pagina; // menú lateral

$cfg = $model->config();
$campos = $cfg['campos'];
$pagina = PaginaSeccion::PAGINAS[$model->pagina];
$this->title = 'Editar: ' . $cfg['nombre'];

$hint = fn($campo) => $campos[$campo][1] ?? '';
$imagenes = array_values(array_intersect(PaginaSeccion::IMAGENES, array_keys($campos)));
$textos = array_diff(array_keys($campos), array_merge($imagenes, ['texto']));
?>

<section class="content-header">
    <h1><i class="fa fa-<?= Html::encode($cfg['icono'] ?? 'pencil') ?>"></i> <?= Html::encode($this->title) ?></h1>
    <p class="huk-inicio-sub"><?= Html::encode($pagina['nombre'] . ' → ' . $pagina['pestanas'][$cfg['pestana']]['nombre']) ?></p>
</section>

<section class="content">
    <?php if (!empty($cfg['ayuda'])): ?>
        <p class="huk-aviso-contacto huk-aviso-arriba"><i class="fa fa-info-circle"></i> <?= Html::encode($cfg['ayuda']) ?></p>
    <?php endif; ?>

    <?php $form = ActiveForm::begin([
        'id' => 'seccion-form',
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <div class="row">
        <div class="<?= $imagenes ? 'col-md-8' : 'col-md-12' ?>">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-font"></i> Textos</h3></div>
                <div class="box-body">
                    <?php foreach ($textos as $campo): ?>
                        <?php if ($campo === 'frase'): ?>
                            <?= $form->field($model, 'frase')->textarea(['rows' => 2, 'maxlength' => 300])->hint($hint('frase')) ?>
                        <?php else: ?>
                            <?= $form->field($model, $campo)->textInput(['maxlength' => $campo === 'subtitulo' ? 300 : ($campo === 'titulo' ? 200 : 150)])->hint($hint($campo)) ?>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (isset($campos['texto'])): ?>
                        <?php if ($model->conEditor()): ?>
                            <?= $form->field($model, 'texto')->textarea([
                                'data-editor' => true,
                                'data-editor-alto' => 380,
                                'data-tinymce-base' => Url::to('@web/assets_b/lib/tinymce'),
                                'rows' => 10,
                            ])->hint($hint('texto')) ?>
                        <?php else: ?>
                            <?= $form->field($model, 'texto')->textarea(['rows' => 5])->hint($hint('texto') ?: 'Texto simple. Cada renglón se muestra como un párrafo.') ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($imagenes): ?>
            <div class="col-md-4">
                <?php foreach ($imagenes as $campo): ?>
                    <div class="box box-primary">
                        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-picture-o"></i> <?= Html::encode($campos[$campo][0]) ?></h3></div>
                        <div class="box-body">
                            <?= $form->field($model, $campo)->widget(CampoImagen::class, [
                                'url' => PaginaSeccion::url($model->$campo),
                                'medida' => $campo === 'imagen' ? ($cfg['medida'] ?? '') : ($cfg['medidas'][$campo] ?? ''),
                            ])->label(false)->hint($campos[$campo][1] ?: ($campos[$campo][2] ? '' : 'Opcional.')) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="huk-form-acciones text-center">
        <?= Html::submitButton('<i class="fa fa-save"></i> Guardar cambios', ['class' => 'btn btn-success btform']) ?>
        <?= Html::a('Cancelar', $volver, ['class' => 'btn btn-default btform']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</section>
