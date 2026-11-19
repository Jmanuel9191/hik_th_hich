<?php

/**
 * Configuración → Acuerdo de confidencialidad. Las versiones no se editan: se publica una nueva
 * y los inversores aprobados la aceptan al volver a entrar.
 *
 * @var yii\web\View $this
 * @var app\models\NdaVersion[] $versiones
 * @var array $aceptaciones idversion => cantidad
 * @var int $pendientes inversores aprobados que no aceptaron la vigente
 * @var app\models\NdaVersion|null $vigente
 * @var app\models\NdaVersion $borrador
 * @var app\models\NdaVersion|null $vista versión elegida para leer (?ver=N)
 * @var bool $puedeEditar
 */

use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\InversoresAdminAsset::register($this);

$this->title = 'Acuerdo de confidencialidad';
$mostrar = $vista ?? $vigente;
?>

<section class="content-header">
    <h1><i class="fa fa-file-text-o"></i> Acuerdo de confidencialidad (NDA)</h1>
    <p class="huk-inicio-sub">Lo aceptan los inversores aprobados antes de ver los proyectos. Cada cambio es una versión nueva: se guarda quién aceptó cuál.</p>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= $mostrar ? Html::encode($mostrar->titulo) . ' · versión ' . $mostrar->numero : 'Sin acuerdo' ?></h3>
                    <?php if ($mostrar && $vigente && $mostrar->idversion === $vigente->idversion): ?>
                        <span class="label label-success">Vigente</span>
                    <?php elseif ($mostrar): ?>
                        <span class="label label-default">Anterior</span> <?= Html::a('Ver la vigente', ['index'], ['class' => 'btn btn-default btn-xs']) ?>
                    <?php endif; ?>
                </div>
                <div class="box-body huk-rich huk-nda-texto">
                    <?= $mostrar ? $mostrar->texto /* HTML limpio (HtmlEditor) */ : '<p class="text-muted">Todavía no hay un acuerdo publicado.</p>' ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-body">
                    <p class="huk-nda-dato"><strong><?= (int) $pendientes ?></strong> inversor(es) aprobado(s) todavía no aceptaron la versión vigente.</p>
                    <p class="huk-ayuda">Se les pide al entrar a su cuenta; sin aceptarla no ven proyectos.</p>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Versiones</h3></div>
                <div class="box-body">
                    <table class="table table-striped">
                        <thead><tr><th>Versión</th><th>Publicada</th><th>Aceptaron</th></tr></thead>
                        <tbody>
                        <?php foreach ($versiones as $v): ?>
                            <tr>
                                <td><?= Html::a('v' . $v->numero, ['index', 'ver' => $v->numero]) ?>
                                    <?php if ($v->nota): ?><div class="huk-eco-resumen"><?= Html::encode($v->nota) ?></div><?php endif; ?></td>
                                <td class="text-nowrap"><?= date('d/m/Y', strtotime($v->fecha_publicacion)) ?>
                                    <div class="huk-eco-resumen"><?= Html::encode($v->usuario->usuario ?? 'Sistema') ?></div></td>
                                <td class="text-center"><?= (int) ($aceptaciones[$v->idversion] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="huk-ayuda">La publicación de cada versión queda también en el historial de Datos generales.</p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($puedeEditar): ?>
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-plus"></i> Publicar la versión <?= (int) $borrador->numero ?></h3></div>
            <?php $form = ActiveForm::begin(['id' => 'nda-form']); ?>
            <div class="box-body">
                <p class="huk-aviso-contacto"><i class="fa fa-exclamation-triangle"></i> Al publicar, <strong>todos los inversores aprobados</strong> tienen que
                    volver a aceptar el acuerdo la próxima vez que entren. Las versiones anteriores quedan guardadas tal cual.</p>
                <?= $form->field($borrador, 'titulo')->textInput(['maxlength' => 200]) ?>
                <?= $form->field($borrador, 'texto')->textarea([
                    'data-editor' => true,
                    'data-editor-alto' => 460,
                    'data-tinymce-base' => Url::to('@web/assets_b/lib/tinymce'),
                    'rows' => 14,
                ])->hint('Arranca con el texto de la versión vigente: cambiá lo que haga falta.') ?>
                <?= $form->field($borrador, 'nota')->textInput(['maxlength' => 300, 'placeholder' => 'Ej.: Se amplió la vigencia a 3 años']) ?>
            </div>
            <div class="box-footer text-center">
                <?= Html::submitButton('<i class="fa fa-check"></i> Publicar versión ' . (int) $borrador->numero, [
                    'class' => 'btn btn-success btform',
                    'data-confirm' => '¿Publicar la versión ' . (int) $borrador->numero . '? Los inversores la van a tener que aceptar de nuevo.',
                ]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    <?php endif; ?>
</section>
