<?php

/**
 * Botón "Exportar" (Excel / CSV / PDF) con los filtros que tenga la página.
 *
 * @var yii\web\View $this
 * @var string|array $ruta acción de exportar (ej. 'exportar' o ['/admin/inversiones/exportar'])
 * @var array $extra parámetros fijos (ej. ['tab' => 'aprobado'])
 */

use yii\helpers\Html;

$q = array_merge(Yii::$app->request->getQueryParams(), $extra ?? []);
unset($q['formato'], $q['page'], $q['_pjax']);
$base = (array) $ruta;
$url = fn($f) => array_merge($base, $q, ['formato' => $f]);
?>
<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            title="Exporta lo que ve en la tabla (con los filtros que tenga). Sin filtros = todos.">
        <i class="fa fa-download"></i> Exportar <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        <li><?= Html::a('<i class="fa fa-file-excel-o"></i> Excel (.xlsx)', $url('xlsx'), ['data-pjax' => '0']) ?></li>
        <li><?= Html::a('<i class="fa fa-file-text-o"></i> CSV', $url('csv'), ['data-pjax' => '0']) ?></li>
        <li><?= Html::a('<i class="fa fa-file-pdf-o"></i> PDF', $url('pdf'), ['data-pjax' => '0', 'target' => '_blank']) ?></li>
    </ul>
</div>
<small class="text-muted">Exporta con los filtros aplicados · sin filtros = todos</small>
