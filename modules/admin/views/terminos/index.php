<?php

/**
 * Configuración → Términos y privacidad (los que acepta el inversor al registrarse, en el modal del formulario).
 * Las versiones no se editan: se publica una nueva. Los inversores que ya están no tienen que volver a aceptar.
 *
 * @var yii\web\View $this
 * @var app\models\TerminosVersion[] $versiones
 * @var array $aceptaciones número de versión => cantidad de inversores
 * @var app\models\TerminosVersion|null $vigente
 * @var app\models\TerminosVersion $borrador
 * @var app\models\TerminosVersion|null $vista versión elegida para leer (?ver=N)
 * @var bool $puedeEditar
 */

use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

\app\assets_b\InversoresAdminAsset::register($this);

$this->title = 'Términos y privacidad';
$mostrar = $vista ?? $vigente;
?>

<section class="content-header">
    <h1><i class="fa fa-balance-scale"></i> Términos de uso y política de privacidad</h1>
    <p class="huk-inicio-sub">Los lee y acepta quien envía la solicitud en «Quiero ser socio» (se abren en una ventana). Cada cambio es una versión nueva: se guarda cuál aceptó cada inversor.</p>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= $mostrar ? Html::encode($mostrar->titulo) . ' · versión ' . $mostrar->numero : 'Sin términos' ?></h3>
                    <?php if ($mostrar && $vigente && $mostrar->idversion === $vigente->idversion): ?>
                        <span class="label label-success">Vigente</span>
                    <?php elseif ($mostrar): ?>
                        <span class="label label-default">Anterior</span> <?= Html::a('Ver la vigente', ['index'], ['class' => 'btn btn-default btn-xs']) ?>
                    <?php endif; ?>
                </div>
                <div class="box-body huk-rich huk-nda-texto">
                    <?= $mostrar ? $mostrar->texto /* HTML limpio (HtmlEditor) */ : '<p class="text-muted">Todavía no hay términos publicados.</p>' ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-body">
                    <p class="huk-nda-dato"><i class="fa fa-external-link"></i> Se ven en el formulario de registro, enlace «Leer los términos».</p>
                    <p class="huk-ayuda">Publicar una versión nueva no les pide nada a los inversores que ya están: la aceptan los que se registran desde ese momento.</p>
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
                                <td class="text-center"><?= (int) ($aceptaciones[$v->numero] ?? 0) ?></td>
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
            <?php $form = ActiveForm::begin(['id' => 'terminos-form']); ?>
            <div class="box-body">
                <p class="huk-aviso-contacto"><i class="fa fa-info-circle"></i> Al publicar, es la versión que ven y aceptan <strong>los que se registran desde ese momento</strong>.
                    Las versiones anteriores quedan guardadas tal cual (cada inversor tiene anotada la que aceptó).</p>
                <?= $form->field($borrador, 'titulo')->textInput(['maxlength' => 200]) ?>
                <?= $form->field($borrador, 'texto')->textarea([
                    'data-editor' => true,
                    'data-editor-alto' => 460,
                    'data-tinymce-base' => Url::to('@web/assets_b/lib/tinymce'),
                    'rows' => 14,
                ])->hint('Arranca con el texto de la versión vigente: cambie lo que haga falta.') ?>
                <?= $form->field($borrador, 'nota')->textInput(['maxlength' => 300, 'placeholder' => 'Ej.: Se agregó el tratamiento de datos para newsletters']) ?>
            </div>
            <div class="box-footer text-center">
                <?= Html::submitButton('<i class="fa fa-check"></i> Publicar versión ' . (int) $borrador->numero, [
                    'class' => 'btn btn-success btform',
                    'data-confirm' => '¿Publicar la versión ' . (int) $borrador->numero . '? Desde ahora es la que aceptan los inversores nuevos.',
                ]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    <?php endif; ?>
</section>
