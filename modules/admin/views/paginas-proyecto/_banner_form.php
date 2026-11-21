<?php

/**
 * Banner de la página web del proyecto y de su tarjeta: fotos, antetítulo, texto, sello, elementos (79 Au…),
 * cifras grandes y "destacado". Se guarda con un formulario normal (PaginasProyectoController::actionBanner).
 * Elementos y cifras usan las mismas filas que los bloques (huk-pagina-proyecto.js, data-pe-lista).
 *
 * @var yii\web\View $this
 * @var app\models\Proyecto $proyecto
 * @var app\models\BannerProyectoForm $banner
 * @var bool $puedeEditar
 */

use app\models\BannerProyectoForm;
use app\models\Proyecto;
use yii\helpers\Html;
use yii\helpers\Url;
use app\widgets\ZonaArchivo;

$p = $proyecto;
$f = $banner;
$n = fn($attr) => 'BannerProyectoForm[' . $attr . ']';
$elementos = $f->elementos ?: [];
$cifras = $f->cifras ?: [];
$filaElemento = function ($i, array $e) use ($n) {
    return '<div class="huk-pe-fila is-linea" data-pe-fila>'
        . Html::textInput($n('elementos') . '[' . $i . '][numero]', $e['numero'] ?? '', ['class' => 'form-control huk-pe-el-num', 'placeholder' => '79', 'maxlength' => 3, 'inputmode' => 'numeric', 'aria-label' => 'Número atómico'])
        . Html::textInput($n('elementos') . '[' . $i . '][simbolo]', $e['simbolo'] ?? '', ['class' => 'form-control huk-pe-el-sim', 'placeholder' => 'Au', 'maxlength' => 4, 'aria-label' => 'Símbolo'])
        . Html::textInput($n('elementos') . '[' . $i . '][nombre]', $e['nombre'] ?? '', ['class' => 'form-control', 'placeholder' => 'Oro', 'maxlength' => 30, 'aria-label' => 'Nombre'])
        . '<span class="huk-pe-herramientas"><button type="button" class="btn btn-default btn-xs" data-pe-fila-mover="-1" title="Subir"><i class="fa fa-arrow-up"></i></button>'
        . '<button type="button" class="btn btn-danger btn-xs" data-pe-fila-quitar title="Quitar"><i class="fa fa-times"></i></button></span></div>';
};
$filaCifra = function ($i, array $c) use ($n) {
    return '<div class="huk-pe-fila is-linea" data-pe-fila>'
        . Html::textInput($n('cifras') . '[' . $i . '][valor]', $c['valor'] ?? '', ['class' => 'form-control huk-pe-cifra-valor', 'placeholder' => '7', 'maxlength' => 16, 'aria-label' => 'Número'])
        . Html::textInput($n('cifras') . '[' . $i . '][texto]', $c['texto'] ?? '', ['class' => 'form-control', 'placeholder' => 'Concesiones mineras', 'maxlength' => 60, 'aria-label' => 'Texto'])
        . '<label class="huk-pe-cifra-contar" title="El número sube desde 0 al aparecer">' . Html::checkbox($n('cifras') . '[' . $i . '][contar]', $c['contar'] ?? true, ['value' => '1']) . ' Contar</label>'
        . '<span class="huk-pe-herramientas"><button type="button" class="btn btn-default btn-xs" data-pe-fila-mover="-1" title="Subir"><i class="fa fa-arrow-up"></i></button>'
        . '<button type="button" class="btn btn-danger btn-xs" data-pe-fila-quitar title="Quitar"><i class="fa fa-times"></i></button></span></div>';
};
?>
<div class="box huk-pe-banner" id="pagina-banner">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-picture-o"></i> Banner y tarjeta del proyecto</h3>
        <div class="box-tools"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button></div>
    </div>
    <div class="box-body">
        <?php if (!$puedeEditar): ?>
            <p class="huk-pe-ayuda">Solo lectura. Lo editan quienes tienen el permiso «página web de los proyectos».</p>
        <?php endif; ?>
        <form method="post" action="<?= Url::to(['/admin/paginas-proyecto/banner', 'id' => $p->idproyecto]) ?>" enctype="multipart/form-data" data-pe-banner>
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
            <fieldset<?= $puedeEditar ? '' : ' disabled' ?>>
                <div class="row">
                    <?php foreach (Proyecto::IMAGENES as $campo => $nombre): ?>
                        <div class="col-md-6 form-group huk-pe-imagen" data-pe-imagen>
                            <label class="control-label"><?= Html::encode($nombre) ?> <small class="text-muted"><?= Proyecto::MEDIDAS[$campo] ?></small></label>
                            <div class="huk-pe-imagen-caja is-ancha">
                                <div class="huk-pe-imagen-vista" data-pe-imagen-vista>
                                    <?= $p->$campo ? Html::img($p->urlImagen($campo), ['alt' => '']) : '<i class="fa fa-picture-o"></i>' ?>
                                </div>
                                <div>
                                    <?= ZonaArchivo::envolver(Html::fileInput('imagen[' . $campo . ']', null, ['accept' => 'image/jpeg,image/png,image/webp', 'data-pe-imagen-archivo' => '1']),
                                        'JPG, PNG o WEBP', ['compacta' => true, 'lista' => false, 'botones' => false, 'titulo' => 'Arrastre la foto aquí']) ?>
                                    <p class="help-block"><?= $campo === 'imagen_banner' ? 'El fondo del banner de la página.' : 'La foto de la tarjeta (listado y carrusel del ecosistema).' ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="control-label" for="pe-rubro"><?= $f->getAttributeLabel('rubro') ?></label>
                        <?= Html::textInput($n('rubro'), $f->rubro, ['id' => 'pe-rubro', 'class' => 'form-control', 'maxlength' => 80, 'placeholder' => 'Proyecto minero']) ?>
                        <p class="help-block">Se ve: «<?= Html::encode($p->antetituloTarjeta()) ?>». Ciudad y país salen de la ficha.</p>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="control-label" for="pe-sello"><?= $f->getAttributeLabel('sello') ?></label>
                        <?= Html::textInput($n('sello'), $f->sello, ['id' => 'pe-sello', 'class' => 'form-control', 'maxlength' => 80, 'placeholder' => BannerProyectoForm::SELLO]) ?>
                        <p class="help-block">Vacío: sin sello.</p>
                    </div>
                    <div class="col-md-4 form-group huk-pe-casillas huk-pe-destacado">
                        <?= Html::hiddenInput($n('destacado'), '0') ?>
                        <label><?= Html::checkbox($n('destacado'), $f->destacado, ['value' => '1']) ?> <i class="fa fa-star"></i> <?= $f->getAttributeLabel('destacado') ?></label>
                        <p class="help-block">Si en el ecosistema no hay ninguno destacado, se muestra el último publicado.</p>
                        <?= Html::hiddenInput($n('mostrar_ficha'), '0') ?>
                        <label><?= Html::checkbox($n('mostrar_ficha'), $f->mostrar_ficha, ['value' => '1']) ?> <i class="fa fa-id-card-o"></i> <?= $f->getAttributeLabel('mostrar_ficha') ?></label>
                        <p class="help-block">Sale sola de la pestaña Ficha: es lo primero del menú.</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label" for="pe-bajada"><?= $f->getAttributeLabel('bajada') ?></label>
                    <?= Html::textarea($n('bajada'), $f->bajada, ['id' => 'pe-bajada', 'class' => 'form-control', 'rows' => 2, 'maxlength' => 400, 'placeholder' => $p->resumen]) ?>
                    <p class="help-block">Si lo deja vacío se usa el resumen de la ficha.</p>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group huk-pe-lista" data-pe-lista data-max="<?= BannerProyectoForm::MAX_ELEMENTOS ?>">
                        <label class="control-label">Elementos <small class="text-muted">número atómico · símbolo · nombre</small></label>
                        <div data-pe-filas><?php foreach ($elementos as $i => $e): ?><?= $filaElemento($i, $e) ?><?php endforeach; ?></div>
                        <template data-pe-plantilla><?= $filaElemento('__i__', []) ?></template>
                        <button type="button" class="btn btn-default btn-sm" data-pe-fila-agregar><i class="fa fa-plus"></i> Agregar elemento</button>
                        <p class="help-block">Los cuadraditos del banner (ej. 79 Au). Opcional, hasta <?= BannerProyectoForm::MAX_ELEMENTOS ?>.</p>
                    </div>
                    <div class="col-md-6 form-group huk-pe-lista" data-pe-lista data-max="<?= BannerProyectoForm::MAX_CIFRAS ?>">
                        <label class="control-label">Cifras grandes <small class="text-muted">número · texto</small></label>
                        <div data-pe-filas><?php foreach ($cifras as $i => $c): ?><?= $filaCifra($i, $c) ?><?php endforeach; ?></div>
                        <template data-pe-plantilla><?= $filaCifra('__i__', []) ?></template>
                        <button type="button" class="btn btn-default btn-sm" data-pe-fila-agregar><i class="fa fa-plus"></i> Agregar cifra</button>
                        <p class="help-block">Hasta <?= BannerProyectoForm::MAX_CIFRAS ?>. «Contar»: el número sube solo al aparecer (no lo marques en fechas como «Ago 2026»).</p>
                    </div>
                </div>
                <?php if ($puedeEditar): ?>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Guardar banner</button>
                <?php endif; ?>
            </fieldset>
        </form>
    </div>
</div>
