<?php

/**
 * Formulario de ecosistema (crear / editar).
 * Editor y etiquetas (agregar / quitar): assets_b/js/huk-ecosistemas.js. Imágenes: app\widgets\CampoImagen.
 *
 * @var yii\web\View $this
 * @var app\models\Ecosistema $model
 * @var array $paises  código => enfoque (Panel → Páginas → Ejes estratégicos → Expansión)
 */

use app\models\Ecosistema;
use app\widgets\MapaPaises;
use app\widgets\CampoImagen;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\EcosistemasAdminAsset::register($this);
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'class' => 'huk-eco-form']]); ?>

<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Contenido</h3></div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-3">
                        <?= $form->field($model, 'numero')->input('number', ['min' => 0, 'max' => 999])->hint('Orden en la web (01, 02…)') ?>
                    </div>
                    <div class="col-sm-5">
                        <?= $form->field($model, 'nombre')->textInput(['maxlength' => 120]) ?>
                    </div>
                    <div class="col-sm-4">
                        <?= $form->field($model, 'subtitulo')->textInput(['maxlength' => 120, 'placeholder' => 'Ej.: Natural Resources'])
                            ->hint('Opcional. Sale chico debajo del título en las tarjetas.') ?>
                    </div>
                </div>

                <?= $form->field($model, 'resumen')->textarea(['rows' => 3, 'maxlength' => 500])
                    ->hint('Una o dos líneas. Se muestra en las tarjetas de la web.') ?>

                <?= $form->field($model, 'descripcion')->textarea([
                    'data-editor' => true,
                    'data-tinymce-base' => Url::to('@web/assets_b/lib/tinymce'),
                    'rows' => 14,
                ])->hint('Puede pegar tablas desde Excel: se mantienen los colores, bordes y negritas. Las listas numeradas salen en la web de 2 en 2 con su número (01, 02…); las de viñetas, con un cuadrito dorado.') ?>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-window-maximize"></i> Página del ecosistema en la web</h3></div>
            <div class="box-body">
                <div class="form-group">
                    <label class="control-label">Etiquetas del banner</label>
                    <p class="help-block">Palabras cortas que salen debajo del resumen (la primera, en dorado). Ej.: Minerales estratégicos, Exploración. Hasta <?= Ecosistema::ETIQUETAS_MAX ?>.</p>
                    <?= Html::hiddenInput('Ecosistema[etiquetas][]', '') /* si se borran todas, igual llega el campo */ ?>
                    <div class="huk-etiquetas" data-etiquetas data-max="<?= Ecosistema::ETIQUETAS_MAX ?>">
                        <?php foreach ($model->listaEtiquetas() as $etiqueta): ?>
                            <div class="input-group huk-etiqueta">
                                <?= Html::textInput('Ecosistema[etiquetas][]', $etiqueta, ['class' => 'form-control', 'maxlength' => Ecosistema::ETIQUETA_LARGO]) ?>
                                <span class="input-group-btn"><button type="button" class="btn btn-default" data-etiqueta-quitar title="Quitar"><i class="fa fa-times"></i></button></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <template data-etiqueta-modelo>
                        <div class="input-group huk-etiqueta">
                            <?= Html::textInput('Ecosistema[etiquetas][]', '', ['class' => 'form-control', 'maxlength' => Ecosistema::ETIQUETA_LARGO, 'placeholder' => 'Ej.: Exploración']) ?>
                            <span class="input-group-btn"><button type="button" class="btn btn-default" data-etiqueta-quitar title="Quitar"><i class="fa fa-times"></i></button></span>
                        </div>
                    </template>
                    <button type="button" class="btn btn-default btn-sm" data-etiqueta-agregar><i class="fa fa-plus"></i> Agregar etiqueta</button>
                </div>

                <?= $form->field($model, 'enfoque')->textarea(['rows' => 4, 'maxlength' => 1500])
                    ->hint('Sale en el recuadro negro, al lado de los países. Vacío = no se muestra.') ?>

                <div class="form-group">
                    <label class="control-label"><?= $model->getAttributeLabel('paises') ?></label>
                    <?= Html::hiddenInput('Ecosistema[paises][]', '') ?>
                    <?php if ($paises): ?>
                        <p class="help-block">Marque en qué países está este ecosistema: salen con el enfoque de cada país.</p>
                        <div class="huk-paises-check">
                            <?php foreach ($paises as $codigo => $enfoque): ?>
                                <label class="huk-pais-check">
                                    <?= Html::checkbox('Ecosistema[paises][]', in_array($codigo, $model->listaPaises(), true), ['value' => $codigo]) ?>
                                    <span><b><?= Html::encode(MapaPaises::nombre($codigo)) ?></b><?php if ($enfoque !== ''): ?><small><?= Html::encode($enfoque) ?></small><?php endif; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="help-block">Todavía no hay países: se cargan en Páginas → Ejes estratégicos → Expansión.</p>
                    <?php endif; ?>
                </div>

                <hr>
                <?= $form->field($model, 'mostrar_oportunidades')->checkbox(['uncheck' => '0', 'value' => '1'])
                    ->hint('Recuadro "Cartera de proyectos en originación" con el botón "Proponer un proyecto" (lleva a Contacto).') ?>
                <?= $form->field($model, 'oportunidades')->textarea(['rows' => 3, 'maxlength' => 1000, 'placeholder' => 'Vacío = un texto general invitando a proponer proyectos.']) ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Imágenes</h3></div>
            <div class="box-body">
                <?php foreach (Ecosistema::IMAGENES as $campo => $etiqueta): ?>
                    <?= $form->field($model, $campo)->widget(CampoImagen::class, $campo === 'icono'
                        ? ['url' => $model->url($model->$campo), 'medida' => 'Cuadrado, fondo transparente', 'extensiones' => ['svg', 'png', 'webp'], 'maxMb' => 1, 'icono' => true]
                        : ['url' => $model->url($model->$campo), 'medida' => trim(strstr(Ecosistema::MEDIDAS[$campo], '(', true))]
                    )->label($etiqueta) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="huk-form-acciones">
    <?= Html::submitButton('<i class="fa fa-save"></i> Guardar', ['class' => 'btn btn-success']) ?>
    <?= Html::a('Cancelar', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->idecosistema], ['class' => 'btn btn-default']) ?>
</div>

<?php ActiveForm::end(); ?>
