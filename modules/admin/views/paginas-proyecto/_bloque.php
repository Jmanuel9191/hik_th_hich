<?php

/**
 * Tarjeta de un bloque en el editor: ícono, tipo, ancho, un resumen del contenido y la primera foto;
 * botones (subir, bajar, editar, duplicar, ocultar, eliminar) y plegar.
 *
 * @var yii\web\View $this
 * @var app\models\ProyectoBloque $bloque
 * @var app\models\ProyectoBloque[] $hermanos  los de su sección (para saber si es el primero o el último)
 * @var bool $puedeEditar
 * @var app\components\MediosProyecto $medios
 */

use app\models\bloques\Catalogo;
use app\models\ProyectoBloque;
use yii\helpers\Html;
use yii\helpers\Url;

$b = $bloque;
$id = (int) $b->idbloque;
$c = $b->contenido();
$clase = Catalogo::TIPOS[$b->tipo] ?? null;
$fotos = $c ? $c->imagenes() : [];
$guia = $c && $c->tieneGuias();
$primero = $hermanos && $hermanos[0]->idbloque === $b->idbloque;
$ultimo = $hermanos && end($hermanos)->idbloque === $b->idbloque;
$u = fn($accion, $extra = []) => Url::to(['/admin/paginas-proyecto/' . $accion, 'id' => $id] + $extra);
?>
<div class="huk-pe-bloque<?= $b->visible === '1' ? '' : ' is-oculto' ?><?= $guia ? ' is-guia' : '' ?>" id="pe-bloque-<?= $id ?>" data-pe-bloque="<?= $id ?>">
    <div class="huk-pe-bloque-cab">
        <span class="huk-pe-bloque-icono"><i class="fa fa-<?= $clase ? $clase::ICONO : 'question' ?>"></i></span>
        <strong><?= Html::encode(Catalogo::nombre($b->tipo)) ?></strong>
        <?php if ($b->ancho !== 'completo'): ?>
            <span class="huk-pe-tag"><i class="fa fa-columns"></i> <?= Html::encode(ProyectoBloque::ANCHOS[$b->ancho]) ?></span>
        <?php endif; ?>
        <?php if ($b->visible !== '1'): ?><span class="huk-pe-tag is-oculta"><i class="fa fa-eye-slash"></i> Oculto</span><?php endif; ?>
        <?php if ($guia): ?><span class="huk-pe-tag is-guia" title="Tiene textos guía de la plantilla («…»): reemplácelos antes de publicar">« » Completar</span><?php endif; ?>
        <?php if ($puedeEditar): ?>
            <div class="huk-pe-herramientas">
                <button type="button" class="btn btn-default btn-xs" data-pe-accion="<?= $u('bloque-mover', ['direccion' => -1]) ?>" title="Subir"<?= $primero ? ' disabled' : '' ?>><i class="fa fa-arrow-up"></i></button>
                <button type="button" class="btn btn-default btn-xs" data-pe-accion="<?= $u('bloque-mover', ['direccion' => 1]) ?>" title="Bajar"<?= $ultimo ? ' disabled' : '' ?>><i class="fa fa-arrow-down"></i></button>
                <button type="button" class="btn btn-info btn-xs" data-pe-abrir="<?= $u('bloque-form') ?>" title="Editar"><i class="fa fa-pencil"></i></button>
                <button type="button" class="btn btn-default btn-xs" data-pe-accion="<?= $u('bloque-duplicar') ?>" title="Duplicar"><i class="fa fa-files-o"></i></button>
                <button type="button" class="btn btn-default btn-xs" data-pe-accion="<?= $u('bloque-visible') ?>" title="<?= $b->visible === '1' ? 'Ocultar' : 'Mostrar' ?>"><i class="fa fa-<?= $b->visible === '1' ? 'eye-slash' : 'eye' ?>"></i></button>
                <button type="button" class="btn btn-danger btn-xs" data-pe-accion="<?= $u('bloque-eliminar') ?>" data-confirmar="¿Eliminar este bloque (<?= Html::encode(Catalogo::nombre($b->tipo)) ?>)?" title="Eliminar"><i class="fa fa-trash"></i></button>
                <button type="button" class="huk-pe-plegar is-chico" data-pe-plegar aria-expanded="true" title="Plegar"><i class="fa fa-chevron-down"></i></button>
            </div>
        <?php endif; ?>
    </div>
    <div class="huk-pe-bloque-cuerpo" data-pe-cuerpo>
        <?php if ($fotos): ?>
            <div class="huk-pe-bloque-fotos">
                <?php foreach (array_slice($fotos, 0, 4) as $f): ?>
                    <?= Html::img($medios->imagen('bloques', $f, 480), ['alt' => '', 'loading' => 'lazy']) ?>
                <?php endforeach; ?>
                <?php if (count($fotos) > 4): ?><span>+<?= count($fotos) - 4 ?></span><?php endif; ?>
            </div>
        <?php endif; ?>
        <p class="huk-pe-resumen"><?= Html::encode($c ? $c->resumen() : 'Este tipo de bloque ya no existe.') ?></p>
    </div>
</div>
