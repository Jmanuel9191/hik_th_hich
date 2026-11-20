<?php

/**
 * Una fila de un campo "lista" (ej. una foto de la galería, un botón): sus campos y los botones subir, bajar y quitar.
 * $i puede ser "__i__" (molde para las filas nuevas): huk-pagina-proyecto.js renumera todo antes de guardar.
 *
 * @var yii\web\View $this
 * @var string $nombre
 * @var array $c
 * @var array $fila
 * @var int|string $i
 * @var app\components\MediosProyecto $medios
 * @var array|null $fuentes
 */

use yii\helpers\Html;
?>
<div class="huk-pe-fila" data-pe-fila>
    <div class="huk-pe-fila-cab">
        <span class="huk-pe-fila-num" data-pe-fila-num><?= Html::encode($c['item'] ?? 'Ítem') ?></span>
        <span class="huk-pe-herramientas">
            <button type="button" class="btn btn-default btn-xs" data-pe-fila-mover="-1" title="Subir"><i class="fa fa-arrow-up"></i></button>
            <button type="button" class="btn btn-default btn-xs" data-pe-fila-mover="1" title="Bajar"><i class="fa fa-arrow-down"></i></button>
            <button type="button" class="btn btn-danger btn-xs" data-pe-fila-quitar title="Quitar"><i class="fa fa-times"></i></button>
        </span>
    </div>
    <div class="huk-pe-fila-campos">
        <?php foreach ($c['campos'] as $sub => $cs): ?>
            <div class="huk-pe-fila-campo is-<?= $cs['tipo'] ?>">
                <?= $this->render('_campo', [
                    'nombre' => $nombre, 'c' => $cs, 'valor' => $fila[$sub] ?? ($cs['defecto'] ?? ''),
                    'prefijo' => 'Bloque[' . $nombre . '][' . $i . '][' . $sub . ']', 'ruta' => $nombre . '.' . $i . '.' . $sub, 'medios' => $medios, 'fuentes' => $fuentes ?? null,
                ]) ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
