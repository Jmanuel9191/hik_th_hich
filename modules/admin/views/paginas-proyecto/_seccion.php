<?php

/**
 * Tarjeta de una sección en el editor: número, antetítulo y título, etiquetas (menú, fondo, diseño, oculta),
 * botones (subir, bajar, editar, duplicar, ocultar, eliminar, plegar) y sus bloques acomodados como en la web
 * (los de columna, uno al lado del otro).
 *
 * @var yii\web\View $this
 * @var app\models\ProyectoSeccion $seccion
 * @var int|null $numero
 * @var bool $primera
 * @var bool $ultima
 * @var bool $puedeEditar
 * @var app\components\MediosProyecto $medios
 */

use app\models\ProyectoSeccion;
use yii\helpers\Html;
use yii\helpers\Url;

$s = $seccion;
$id = (int) $s->idseccion;
$filas = ProyectoSeccion::filas($s->bloques);
$tituloEnColumna = ProyectoSeccion::tituloEnColumna($s, $filas);
$u = fn($accion, $extra = []) => Url::to(['/admin/paginas-proyecto/' . $accion, 'id' => $id] + $extra);
?>
<div class="box huk-pe-sec is-<?= Html::encode($s->fondo) ?><?= $s->visible === '1' ? '' : ' is-oculta' ?>" id="pe-sec-<?= $id ?>" data-pe-seccion="<?= $id ?>">
    <div class="box-header huk-pe-sec-cab">
        <button type="button" class="huk-pe-plegar" data-pe-plegar aria-expanded="true" title="Plegar / desplegar"><i class="fa fa-chevron-down"></i></button>
        <span class="huk-pe-num"><?= $numero !== null ? sprintf('%02d', $numero) : '—' ?></span>
        <div class="huk-pe-sec-tit">
            <?php if ($s->antetitulo !== null): ?><small><?= Html::encode($s->antetitulo) ?></small><?php endif; ?>
            <strong><?= Html::encode($s->titulo ?? '(sin título)') ?></strong>
            <div class="huk-pe-etiquetas">
                <?php if ($s->en_menu === '1'): ?><span class="huk-pe-tag is-menu" title="En el menú de anclas"><i class="fa fa-bookmark"></i> <?= Html::encode($s->nombreMenu()) ?></span><?php endif; ?>
                <span class="huk-pe-tag is-fondo-<?= Html::encode($s->fondo) ?>">Fondo <?= Html::encode(mb_strtolower(ProyectoSeccion::FONDOS[$s->fondo] ?? $s->fondo)) ?></span>
                <?php if ($s->diseno === 'columna'): ?><span class="huk-pe-tag"><i class="fa fa-columns"></i> Título en columna</span><?php endif; ?>
                <?php if ($s->visible !== '1'): ?><span class="huk-pe-tag is-oculta"><i class="fa fa-eye-slash"></i> Oculta</span><?php endif; ?>
                <span class="huk-pe-tag is-ancla">#<?= Html::encode($s->ancla) ?></span>
            </div>
        </div>
        <?php if ($puedeEditar): ?>
            <div class="huk-pe-herramientas">
                <button type="button" class="btn btn-default btn-sm" data-pe-accion="<?= $u('seccion-mover', ['direccion' => -1]) ?>" title="Subir"<?= $primera ? ' disabled' : '' ?>><i class="fa fa-arrow-up"></i></button>
                <button type="button" class="btn btn-default btn-sm" data-pe-accion="<?= $u('seccion-mover', ['direccion' => 1]) ?>" title="Bajar"<?= $ultima ? ' disabled' : '' ?>><i class="fa fa-arrow-down"></i></button>
                <button type="button" class="btn btn-info btn-sm" data-pe-abrir="<?= Url::to(['/admin/paginas-proyecto/seccion-form', 'tipo' => $s->idproyecto ? 'proyecto' : 'plantilla', 'id' => $s->idproyecto ?: $s->idplantilla, 'idseccion' => $id]) ?>" title="Editar sección"><i class="fa fa-pencil"></i></button>
                <button type="button" class="btn btn-default btn-sm" data-pe-accion="<?= $u('seccion-duplicar') ?>" title="Duplicar (con sus bloques)"><i class="fa fa-files-o"></i></button>
                <button type="button" class="btn btn-default btn-sm" data-pe-accion="<?= $u('seccion-visible') ?>" title="<?= $s->visible === '1' ? 'Ocultar en la web' : 'Mostrar en la web' ?>"><i class="fa fa-<?= $s->visible === '1' ? 'eye-slash' : 'eye' ?>"></i></button>
                <button type="button" class="btn btn-danger btn-sm" data-pe-accion="<?= $u('seccion-eliminar') ?>"
                        data-confirmar="¿Eliminar la sección «<?= Html::encode($s->nombrePanel()) ?>» con sus <?= count($s->bloques) ?> bloques? No se puede deshacer." title="Eliminar"><i class="fa fa-trash"></i></button>
            </div>
        <?php endif; ?>
    </div>
    <div class="box-body huk-pe-sec-cuerpo" data-pe-cuerpo>
        <?php if (!$filas): ?>
            <p class="huk-pe-sin-bloques">Sin bloques todavía.</p>
        <?php endif; ?>
        <?php foreach ($filas as $k => $fila): ?>
            <?php if (isset($fila['completo'])): ?>
                <?= $this->render('_bloque', ['bloque' => $fila['completo'], 'puedeEditar' => $puedeEditar, 'medios' => $medios, 'hermanos' => $s->bloques]) ?>
            <?php else: ?>
                <div class="huk-pe-dos">
                    <div><?php if ($k === 0 && $tituloEnColumna): ?><p class="huk-pe-hueco-titulo"><i class="fa fa-header"></i> Aquí va el título de la sección</p><?php endif; ?><?php foreach ($fila['izquierda'] as $b): ?><?= $this->render('_bloque', ['bloque' => $b, 'puedeEditar' => $puedeEditar, 'medios' => $medios, 'hermanos' => $s->bloques]) ?><?php endforeach; ?></div>
                    <div><?php foreach ($fila['derecha'] as $b): ?><?= $this->render('_bloque', ['bloque' => $b, 'puedeEditar' => $puedeEditar, 'medios' => $medios, 'hermanos' => $s->bloques]) ?><?php endforeach; ?></div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($puedeEditar): ?>
            <button type="button" class="huk-pe-agregar" data-pe-catalogo="<?= $id ?>"><i class="fa fa-plus-circle"></i> Agregar bloque</button>
        <?php endif; ?>
    </div>
</div>
