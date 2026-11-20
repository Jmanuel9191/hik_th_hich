<?php

/**
 * Formulario de un bloque (se carga en el modal). Los campos salen de la clase del tipo (campos()),
 * cada uno lo dibuja _campo.php. Abajo, el ancho: completo o columna izquierda / derecha.
 *
 * @var yii\web\View $this
 * @var app\models\ProyectoBloque $bloque
 * @var app\models\bloques\Bloque $contenido
 * @var app\models\ProyectoSeccion $seccion
 * @var app\components\MediosProyecto $medios
 * @var array|null $fuentes  fotos, videos y documentos del proyecto para los campos "elegir" (null en plantillas)
 */

use app\components\SubidaArchivo;
use app\models\ProyectoBloque;
use yii\helpers\Html;
use yii\helpers\Url;

$b = $bloque;
$c = $contenido;
$accion = Url::to($b->isNewRecord
    ? ['/admin/paginas-proyecto/bloque-guardar', 'idseccion' => $seccion->idseccion, 'tipo' => $b->tipo]
    : ['/admin/paginas-proyecto/bloque-guardar', 'id' => $b->idbloque]);
?>
<form data-pe-form action="<?= $accion ?>" enctype="multipart/form-data" novalidate data-post-max="<?= (int) SubidaArchivo::limiteServidor() ?>">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-<?= $c::ICONO ?>"></i> <?= $b->isNewRecord ? 'Nuevo bloque: ' : '' ?><?= Html::encode($c::NOMBRE) ?></h4>
        <p class="huk-pe-ayuda"><?= Html::encode($c::DESCRIPCION) ?> · Sección «<?= Html::encode($seccion->nombrePanel()) ?>»</p>
    </div>
    <div class="modal-body">
        <div class="alert alert-danger" data-pe-errores hidden></div>
        <?php if ($c::AUTOMATICO): ?>
            <div class="callout callout-info huk-pe-auto"><i class="fa fa-magic"></i> <?= Html::encode([
                'galeria_proyecto' => 'Este bloque muestra las fotos de la pestaña «Fotos» del proyecto. Si no hay fotos, no aparece en la web.',
                'videos' => 'Este bloque muestra los videos de la pestaña «Videos» y los enlaces que pegue aquí. Se ven, pero no se pueden descargar.',
                'documentos' => 'Este bloque muestra los documentos de la pestaña «Documentos» marcados «lo ven los inversores». Los PDF y las fotos se abren en el navegador; Word, Excel y los demás se descargan.',
            ][$b->tipo] ?? 'Este bloque se arma solo con los datos del proyecto.') ?></div>
        <?php endif; ?>
        <?php foreach ($c::campos() as $nombre => $campo): ?>
            <?= $this->render('_campo', ['nombre' => $nombre, 'c' => $campo, 'valor' => $c->$nombre, 'prefijo' => 'Bloque[' . $nombre . ']', 'ruta' => $nombre, 'medios' => $medios, 'fuentes' => $fuentes]) ?>
        <?php endforeach; ?>

        <div class="form-group huk-pe-ancho">
            <label class="control-label">Ancho</label>
            <div class="huk-pe-opciones">
                <?php foreach (ProyectoBloque::ANCHOS as $valor => $nombre): ?>
                    <label class="huk-pe-opcion is-ancho">
                        <?= Html::radio('ancho', ($b->ancho ?? 'completo') === $valor, ['value' => $valor]) ?>
                        <span class="huk-pe-anchito is-<?= $valor ?>"><i></i><i></i></span><?= Html::encode($nombre) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="help-block">Los bloques de columna que van seguidos quedan uno al lado del otro. Una «izquierda» después de una «derecha» empieza otra fila.</p>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success" data-pe-guardar><i class="fa fa-check"></i> <?= $b->isNewRecord ? 'Agregar bloque' : 'Guardar' ?></button>
    </div>
</form>
