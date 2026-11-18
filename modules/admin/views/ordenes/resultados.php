<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Resultados - Orden #' . ($model->numero_referencia ?: $model->idorden);

$paramData = [];
foreach ($parametrosDisponibles as $idp => $p) {
    $paramData[$idp] = [
        'idparametro'      => $p['idparametro'],
        'nombre'           => $p['nombre'],
        'unidad'           => $p['unidad'] ?? '',
        'referencia_texto' => strip_tags($p['referencia_texto'] ?? ''),
        'producto'         => $p['producto'],
        'tipo_campo'       => $p['tipo_campo'] ?? 'texto',
        'opciones'         => $p['opciones'] ?? '',
        'valor_default'    => $p['valor_default'] ?? '',
    ];
}
$porProducto = [];
foreach ($parametrosDisponibles as $idp => $p) {
    $porProducto[$p['producto']][$idp] = $p['nombre'];
}

$fechaToma = '';
if (!empty($secciones) && $secciones[0]->fecha_toma_muestra) {
    $fechaToma = date('Y-m-d\TH:i', strtotime($secciones[0]->fecha_toma_muestra));
}
function renderFilaParametro($si, $ri, $idparametro, $nombre, $unidad, $referencia, $valor = '', $esSubtitulo = false, $tipoCampo = 'texto', $opcionesTexto = '', $esAlerta = false) {
    ob_start();
    ?>
    <tr class="resultado-fila">
        <td>
            <input type="hidden" name="secciones[<?= $si ?>][parametros][<?= $ri ?>][idparametro]"
                   value="<?= $idparametro ?>">
            <?php if ($esSubtitulo): ?>
                <strong><em><?= Html::encode($nombre) ?></em></strong>
            <?php elseif ($unidad): ?>
                <strong><?= Html::encode($nombre) ?></strong>
                <small class="text-muted">(<?= Html::encode($unidad) ?>)</small>
            <?php else: ?>
                <?= Html::encode($nombre) ?>
            <?php endif; ?>
        </td>
        <td>
            <?php if (!$esSubtitulo): ?>
                <?php if ($tipoCampo === 'select'): ?>
                    <select name="secciones[<?= $si ?>][parametros][<?= $ri ?>][valor]" class="form-control input-sm valor-resultado <?= $esAlerta ? 'valor-alerta' : '' ?>">
                        <option value="">-- Elegir --</option>
                        <?php foreach (array_filter(array_map('trim', explode("\n", $opcionesTexto))) as $opcion): ?>
                            <option value="<?= Html::encode($opcion) ?>" <?= ($valor === $opcion) ? 'selected' : '' ?>>
                                <?= Html::encode($opcion) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif ($unidad): ?>
                <div class="input-group input-group-sm">
                    <input type="text"
                           name="secciones[<?= $si ?>][parametros][<?= $ri ?>][valor]"
                           class="form-control valor-resultado <?= $esAlerta ? 'valor-alerta' : '' ?>"
                           value="<?= Html::encode($valor) ?>"
                           placeholder="—">
                    <span class="input-group-addon"><?= Html::encode($unidad) ?></span>
                </div>
                <?php else: ?>
                <input type="text"
                       name="secciones[<?= $si ?>][parametros][<?= $ri ?>][valor]"
                       class="form-control input-sm valor-resultado <?= $esAlerta ? 'valor-alerta' : '' ?>"
                       value="<?= Html::encode($valor) ?>"
                       placeholder="—">
                <?php endif; ?>
            <?php else: ?>
                <input type="hidden" name="secciones[<?= $si ?>][parametros][<?= $ri ?>][valor]" value="">
            <?php endif; ?>
        </td>
        <td style="font-size:12px; color:#555">
            <?= !$esSubtitulo ? ($referencia ?: '—') : '' ?>
        </td>
        <td class="text-center">
            <?php if (!$esSubtitulo): ?>
                <input type="checkbox" name="secciones[<?= $si ?>][parametros][<?= $ri ?>][alerta]"
                       value="1" class="chk-alerta" title="Resaltar en rojo" <?= $esAlerta ? 'checked' : '' ?>>
            <?php endif; ?>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-xs btn-danger btn-quitar-fila">
                <i class="fa fa-times"></i>
            </button>
        </td>
    </tr>
    <?php
    return ob_get_clean();
}

function renderHeaderSeccion($si, $titulo, $mostrarCabecera) {
    ob_start();
    ?>
    <div class="box-header" style="background:#f8f9fa; padding:8px 15px">
        <div class="row">
            <div class="col-sm-7">
                <input type="text"
       name="secciones[<?= $si ?>][titulo]"
       class="form-control input-titulo"
       placeholder="Título de la sección (ej: HEMATOLOGIA)"
       value="<?= Html::encode($titulo) ?>"
       style="font-weight:bold; text-transform:uppercase"
       required>
            </div>
            <div class="col-sm-3" style="padding-top:6px;">
                <label style="font-weight:normal; font-size:12px;">
                    <input type="checkbox"
                           name="secciones[<?= $si ?>][mostrar_cabecera]"
                           value="1"
                           <?= $mostrarCabecera ? 'checked' : '' ?>>
                    Mostrar cabecera
                </label>
            </div>
            <div class="col-sm-2 text-right">
                <button type="button" class="btn btn-danger btn-xs btn-eliminar-seccion">
                    <i class="fa fa-trash"></i> Eliminar sección
                </button>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderFooterSeccion($porProducto) {
    ob_start();
    ?>
    <div class="box-footer" style="background:#f8f9fa">
        <div class="row">
            <div class="col-sm-5">
                <select class="form-control select-producto" style="width:100%">
                    <option value="">-- Elegí un producto --</option>
                    <?php foreach ($porProducto as $nombreProd => $params): ?>
                    <option value="<?= Html::encode($nombreProd) ?>">
                        <?= Html::encode($nombreProd) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-5">
                <select class="form-control select-parametro" style="width:100%" disabled>
                    <option value="">-- Primero elegí un producto --</option>
                </select>
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-info btn-block btn-agregar-param">
                    <i class="fa fa-plus"></i> Agregar
                </button>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderObsConclusionSeccion($si, $observaciones, $conclusion) {
    ob_start();
    ?>
    <div class="box-footer" style="background:#fff; border-top:1px solid #eee;">
        <div class="row">
            <div class="col-sm-6">
                <label style="font-weight:bold; font-size:12px;">
                    <i class="fa fa-comment"></i> Observaciones (de esta sección)
                </label>
                <textarea name="secciones[<?= $si ?>][observaciones]"
                          class="form-control observaciones-editor" rows="3"><?= Html::encode($observaciones ?? '') ?></textarea>
            </div>
            <div class="col-sm-6">
                <label style="font-weight:bold; font-size:12px;">
                    <i class="fa fa-check-circle"></i> Conclusión (de esta sección)
                </label>
                <textarea name="secciones[<?= $si ?>][conclusion]"
                          class="form-control conclusion-editor" rows="3"><?= Html::encode($conclusion ?? '') ?></textarea>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>



<section class="content-header">
    <h1>
        <i class="fa fa-flask"></i> <?= Html::encode($this->title) ?> /
        <?= Html::encode($model->paciente_nombre) ?> / <?= Html::encode($model->fecha_registro) ?>
    </h1>
</section>


<div class="row">

<div style="width:100%; font-family: Arial, sans-serif; color:#000;">

<div class="col-xs-12">
<div class="box box-primary">
<div class="box-header with-border">
    <a href="<?= Url::to(['view', 'id' => $model->idorden]) ?>" class="btn btn-warning btn-sm">
        <i class="fa fa-arrow-left"></i> Volver a la orden
    </a>
    <button type="submit" form="form-resultados" name="accion" value="guardar" class="btn btn-success btn-sm">
        <i class="fa fa-save"></i> Guardar
    </button>
    <?php if (!empty($secciones)): ?>
   <!-- <a href="<?= Url::to(['imprimir-resultados', 'id' => $model->idorden]) ?>"
       target="_blank" class="btn btn-info btn-sm">
        <i class="fa fa-print"></i> Imprimir ficha
    </a>-->
    <?php endif; ?>
    

    <?php if (!empty($model->paciente_telefono)): ?>
    <?php
        $numeroLimpio = preg_replace('/\D/', '', $model->paciente_telefono);
        if (substr($numeroLimpio, 0, 3) !== '591') {
            $numeroLimpio = '591' . $numeroLimpio;
        }

        $nombrePacienteSlug = strtoupper(trim(preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9]+/', '_', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $model->paciente_nombre))), '_'));
        $nombreCarpetaOrden = 'PACIENTE_' . $nombrePacienteSlug . '_ORDEN_' . $model->idorden;
        $nombreArchivo      = 'RESULTADO_ORDEN_N' . $model->idorden . '.pdf';

        $urlPdf = Yii::$app->request->hostInfo . '/imagen/' . $nombreCarpetaOrden . '/' . $nombreArchivo;
        // Saludo según la hora — con zona horaria de Bolivia fija
        // (America/La_Paz, UTC-4, sin horario de verano), para que no
        // dependa de cómo esté configurado el reloj del servidor.
        $horaBolivia = (int)(new \DateTime('now', new \DateTimeZone('America/La_Paz')))->format('G');
        if ($horaBolivia >= 1 && $horaBolivia < 12) {
            $saludo = 'Buenos días';
        } elseif ($horaBolivia >= 12 && $horaBolivia < 19) {
            $saludo = 'Buenas tardes';
        } else {
            // Cubre 19:00–23:59 y también la hora 0 (00:00–00:59)
            $saludo = 'Buenas noches';
        }

         $mensaje = "{$saludo}. Le saludamos del Laboratorio Bajío Santa Cruz.\n\n"
                 . "Le informamos que los resultados de sus estudios de laboratorio ya se encuentran listos. Se los estamos enviando en formato PDF por este medio y, si lo desea, también puede pasar a recoger su copia física en nuestras instalaciones.\n\n"
                 . "Muchas gracias por confiar en nosotros. Ha sido un placer atenderle. Quedamos a su disposición para cualquier consulta.";
        $linkWhatsapp = "https://wa.me/{$numeroLimpio}?text=" . urlencode($mensaje);
    ?>
    <?= Html::a('<i class="fa fa-whatsapp"></i> Enviar por WhatsApp', $linkWhatsapp, [
        'class'  => 'btn btn-sm whasw',
        'target' => '_blank',
    ]) ?>
<?php endif; ?>

<?php
    $nombrePacienteSlug = strtoupper(trim(preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9]+/', '_', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $model->paciente_nombre))), '_'));
    $nombreCarpetaOrden = 'PACIENTE_' . $nombrePacienteSlug . '_ORDEN_' . $model->idorden;
    $nombreArchivo      = 'RESULTADO_ORDEN_N' . $model->idorden . '.pdf';
    $rutaFisica = 'C:/xampp/htdocs/LABORATORIOOSINAGA/imagen/resultados/' . $nombreCarpetaOrden . '/' . $nombreArchivo;
?>
<?php if (file_exists($rutaFisica)): ?>
    <?= Html::a('<i class="fa fa-download"></i> Descargar PDF', 
        Url::to(['descargar-pdf', 'id' => $model->idorden]), 
        ['class' => 'btn btn-sm btn-default lesiq']
    ) ?>
<?php else: ?>
    <span class="btn btn-sm btn-default disabled lesiq" style="opacity:0.5; cursor:not-allowed;" title="Primero tenés que guardar los resultados para generar el PDF">
        <i class="fa fa-download"></i> Descargar PDF
    </span>
<?php endif; ?>

</div>

<div class="box-body">
<form method="post" action="<?= Url::to(['resultados', 'id' => $model->idorden]) ?>" id="form-resultados">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <div class="row" style="margin-bottom:20px">
        <div class="col-sm-4">
            <div class="form-group">
                <label><i class="fa fa-clock-o"></i> Fecha y hora de toma de muestra <span style="font-size: 11px;">(Si no se llena nada se colocara la fecha y hora de cuando se registro la orden) </span></label>
                <input type="datetime-local" name="fecha_toma_muestra"
                       class="form-control"
                       value="<?= Html::encode($fechaToma) ?>">
            </div>
        </div>
    </div>

    <div id="secciones-container">

        <?php
        // NUEVO: ahora se muestran las secciones EXISTENTES y, a
        // continuación, las que se auto-arman para completar (análisis
        // que todavía no tienen ningún resultado cargado) — antes era
        // una cosa o la otra, nunca las dos juntas, así que un análisis
        // agregado por "Editar Orden" a una orden que ya tenía
        // resultados quedaba invisible acá. El índice $si sigue siendo
        // único en todo el formulario, aunque venga de dos arrays.
        $totalMostradas = 0;
        ?>

        <?php if (!empty($secciones)): ?>
            <?php foreach ($secciones as $si => $sec): $totalMostradas++; ?>
            <div class="seccion-bloque" data-index="<?= $si ?>">
                <div class="box box-default" style="border-left:4px solid #3c8dbc">
                    <?= renderHeaderSeccion($si, $sec->titulo, $sec->mostrar_cabecera ?? 1) ?>
                    <div class="box-body no-padding">
                        <table class="table table-bordered table-condensed resultado-tabla">
                            <thead style="background:#dce8f3">
                                <tr>
                                    <th style="width:30%">Examen</th>
                                    <th style="width:20%">Resultado</th>
                                    <th style="width:32%">Valores de Referencia</th>
                                    <th style="width:8%" title="Resaltar en rojo">Alerta</th>
                                    <th style="width:10%"></th>
                                </tr>
                            </thead>
                            <tbody class="resultado-filas">
                                <?php foreach ($sec->resultados as $ri => $res): ?>
                                <?php if (!$res->parametro) continue; ?>
                                <?= renderFilaParametro(
    $si, $ri,
    $res->idparametro,
    $res->parametro->nombre,
    $res->parametro->unidad ?? '',
    $res->parametro->referencia_texto ? strip_tags($res->parametro->referencia_texto) : '',
    $res->valor ?? '',
    (bool)($res->parametro->es_subtitulo ?? false),
    $res->parametro->tipo_campo ?? 'texto',
    $res->parametro->opciones ?? '',
    (bool)($res->es_alerta ?? false)
) ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?= renderFooterSeccion($porProducto) ?>
                        <?= renderObsConclusionSeccion($si, $sec->observaciones, $sec->conclusion) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($seccionesPreCargadas)): ?>
    <?php foreach ($seccionesPreCargadas as $i => $sec): $si = count($secciones) + $i; $totalMostradas++; ?>
    <div class="seccion-bloque" data-index="<?= $si ?>">
        <div class="box box-default" style="border-left:4px solid #27ae60">
            <?= renderHeaderSeccion($si, $sec['titulo'], 1) ?>
            <div class="box-body no-padding">
                <table class="table table-bordered table-condensed resultado-tabla">
                    <thead style="background:#dce8f3">
                        <tr>
                            <th style="width:30%">Examen</th>
                            <th style="width:20%">Resultado</th>
                            <th style="width:32%">Valores de Referencia</th>
                            <th style="width:8%" title="Resaltar en rojo">Alerta</th>
                            <th style="width:10%"></th>
                        </tr>
                    </thead>
                    <tbody class="resultado-filas">
                        <?php
                        $ri = 0;
                        foreach ($sec['productos'] as $prod):
                        ?>
                            <!-- Separador visual del producto — solo guía, no se imprime -->
                            <tr class="fila-separador-producto">
                                <td colspan="5" style="
                                    background:#e8f4e8;
                                    color:#2d6a2d;
                                    font-weight:bold;
                                    font-size:11px;
                                    padding:4px 10px;
                                    border-left:3px solid #27ae60;
                                ">
                                    <i class="fa fa-flask"></i> <?= Html::encode($prod['nombre']) ?>
                                </td>
                            </tr>

                            <?php foreach ($prod['parametros'] as $param):
                                if (!$param) continue;
                            ?>
                                <?= renderFilaParametro(
    $si, $ri,
    $param->idparametro,
    $param->nombre,
    $param->unidad ?? '',
    $param->referencia_texto ? strip_tags($param->referencia_texto) : '',
    $param->valor_default ?? '',
    (bool)($param->es_subtitulo ?? false),
    $param->tipo_campo ?? 'texto',
    $param->opciones ?? ''
) ?>
                            <?php
                                $ri++;
                            endforeach; ?>

                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?= renderFooterSeccion($porProducto) ?>
                <?= renderObsConclusionSeccion($si, '', '') ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($totalMostradas === 0): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                No hay parámetros configurados para los análisis de esta orden.
                Podés agregar secciones manualmente con el botón de abajo.
            </div>
        <?php endif; ?>

    </div>

    

    <div style="margin:15px 0">
        <button type="button" id="btn-nueva-seccion" class="btn btn-default btn-lg btn-block"
                style="border:2px dashed #aaa; color:#555">
            <i class="fa fa-plus-circle"></i> Agregar nueva sección
        </button>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <button type="submit" name="accion" value="guardar" class="btn btn-success" style="padding: 10px 25px; font-size:20px">
                <i class="fa fa-save"></i> Guardar
            </button>
           
        </div>
    </div>

</form>
</div>
</div>
</div>
</div>
</section>

<?php
$paramDataJson   = json_encode($paramData, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE);
$porProductoJson = json_encode($porProducto, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE);

$this->registerJs(
    "var paramData   = {$paramDataJson};" .
    "var porProducto = {$porProductoJson};",
    \yii\web\View::POS_HEAD
);

$this->registerJs("
var seccionIndex = \$('.seccion-bloque').length;
function nuevaFila(si, ri, param) {
    var ref = param.referencia_texto || '—';
    var und = param.unidad ? ' <small class=\"text-muted\">('+param.unidad+')</small>' : '';
    var inputResultado;

   if (param.tipo_campo === 'select') {
    var opts = '<option value=\"\">-- Elegir --</option>';
    (param.opciones || '').split('\\n').forEach(function(op) {
            op = op.trim();
            if (op) opts += '<option value=\"'+op+'\">'+op+'</option>';
        });
        inputResultado = '<select name=\"secciones['+si+'][parametros]['+ri+'][valor]\" class=\"form-control input-sm valor-resultado\">' + opts + '</select>';
    } else if (param.unidad) {
        inputResultado = '<div class=\"input-group input-group-sm\">' +
          '<input type=\"text\" name=\"secciones['+si+'][parametros]['+ri+'][valor]\" class=\"form-control valor-resultado\" placeholder=\"—\" value=\"'+(param.valor_default || '')+'\">' +
          '<span class=\"input-group-addon\">'+param.unidad+'</span>' +
          '</div>';
    } else {
        inputResultado = '<input type=\"text\" name=\"secciones['+si+'][parametros]['+ri+'][valor]\" class=\"form-control input-sm valor-resultado\" placeholder=\"—\" value=\"'+(param.valor_default || '')+'\">';
    }

    var chkAlerta = '<input type=\"checkbox\" name=\"secciones['+si+'][parametros]['+ri+'][alerta]\" value=\"1\" class=\"chk-alerta\" title=\"Resaltar en rojo\">';

    return '<tr class=\"resultado-fila\">' +
        '<td>' +
        '<input type=\"hidden\" name=\"secciones['+si+'][parametros]['+ri+'][idparametro]\" value=\"'+param.idparametro+'\">' +
        '<strong>'+param.nombre+'</strong>'+und+
        '</td>' +
        '<td>'+inputResultado+'</td>' +
        '<td style=\"font-size:12px;color:#555\">'+ref+'</td>' +
        '<td class=\"text-center\">'+chkAlerta+'</td>' +
        '<td class=\"text-center\"><button type=\"button\" class=\"btn btn-xs btn-danger btn-quitar-fila\"><i class=\"fa fa-times\"></i></button></td>' +
        '</tr>';
}

function nuevaSeccion(si) {
    var opciones = '<option value=\"\">-- Elegí un producto --</option>';
    \$.each(porProducto, function(nombre) { opciones += '<option value=\"'+nombre+'\">'+nombre+'</option>'; });
    return '<div class=\"seccion-bloque\" data-index=\"'+si+'\">' +
        '<div class=\"box box-default\" style=\"border-left:4px solid #3c8dbc\">' +
        '<div class=\"box-header\" style=\"background:#f8f9fa;padding:8px 15px\">' +
        '<div class=\"row\">' +
        '<div class=\"col-sm-7\">' +
        '<input type=\"text\" name=\"secciones['+si+'][titulo]\" class=\"form-control input-titulo\" ' +
'placeholder=\"Título de la sección (ej: HEMATOLOGIA)\" style=\"font-weight:bold;text-transform:uppercase\" required>' +
        '</div>' +
        '<div class=\"col-sm-3\" style=\"padding-top:6px;\">' +
        '<label style=\"font-weight:normal;font-size:12px;\">' +
        '<input type=\"checkbox\" name=\"secciones['+si+'][mostrar_cabecera]\" value=\"1\" checked> Mostrar cabecera' +
        '</label>' +
        '</div>' +
        '<div class=\"col-sm-2 text-right\">' +
        '<button type=\"button\" class=\"btn btn-danger btn-xs btn-eliminar-seccion\"><i class=\"fa fa-trash\"></i> Eliminar sección</button>' +
        '</div></div></div>' +
        '<div class=\"box-body no-padding\">' +
        '<table class=\"table table-bordered table-condensed resultado-tabla\">' +
        '<thead style=\"background:#dce8f3\"><tr>' +
        '<th style=\"width:30%\">Examen</th><th style=\"width:20%\">Resultado</th>' +
        '<th style=\"width:32%\">Valores de Referencia</th><th style=\"width:8%\" title=\"Resaltar en rojo\">Alerta</th><th style=\"width:10%\"></th>' +
        '</tr></thead><tbody class=\"resultado-filas\"></tbody></table>' +
        '<div class=\"box-footer\" style=\"background:#f8f9fa\"><div class=\"row\">' +
        '<div class=\"col-sm-5\"><select class=\"form-control select-producto\" style=\"width:100%\">' + opciones + '</select></div>' +
        '<div class=\"col-sm-5\"><select class=\"form-control select-parametro\" style=\"width:100%\" disabled><option value=\"\">-- Primero elegí un producto --</option></select></div>' +
        '<div class=\"col-sm-2\"><button type=\"button\" class=\"btn btn-info btn-block btn-agregar-param\"><i class=\"fa fa-plus\"></i> Agregar</button></div>' +
        '</div></div>' +
        '<div class=\"box-footer\" style=\"background:#fff;border-top:1px solid #eee;\"><div class=\"row\">' +
        '<div class=\"col-sm-6\"><label style=\"font-weight:bold;font-size:12px;\"><i class=\"fa fa-comment\"></i> Observaciones (de esta sección)</label>' +
        '<textarea name=\"secciones['+si+'][observaciones]\" class=\"form-control observaciones-editor\" rows=\"3\"></textarea></div>' +
        '<div class=\"col-sm-6\"><label style=\"font-weight:bold;font-size:12px;\"><i class=\"fa fa-check-circle\"></i> Conclusión (de esta sección)</label>' +
        '<textarea name=\"secciones['+si+'][conclusion]\" class=\"form-control conclusion-editor\" rows=\"3\"></textarea></div>' +
        '</div></div>' +
        '</div></div></div>';
}

\$('#btn-nueva-seccion').on('click', function() {
    var \$nuevo = \$(nuevaSeccion(seccionIndex));
    \$('#secciones-container').append(\$nuevo);
    initEditoresRicos(\$nuevo);
    seccionIndex++;
});

\$(document).on('click', '.btn-eliminar-seccion', function() {
    if (confirm('¿Eliminar esta sección completa?')) {
        \$(this).closest('.seccion-bloque').remove();
    }
});

\$(document).on('click', '.btn-quitar-fila', function() {
    var fila   = \$(this).closest('tr');
    var bloque = fila.closest('.seccion-bloque');
    var footer = bloque.find('.box-footer');
    var idp    = fila.find('input[type=hidden]').val();
    var nombre = fila.find('strong').first().text().trim();
    var selPar = footer.find('.select-parametro');
    selPar.append('<option value=\"' + idp + '\">' + nombre + '</option>');
    selPar.prop('disabled', false);
    footer.find('.btn-agregar-param')
          .prop('disabled', false)
          .html('<i class=\"fa fa-plus\"></i> Agregar');
    fila.remove();
});

\$(document).on('change', '.select-producto', function() {
    var prod   = \$(this).val();
    var footer = \$(this).closest('.box-footer');
    var bloque = \$(this).closest('.seccion-bloque');
    var selPar = footer.find('.select-parametro');

    selPar.empty().append('<option value=\"\">-- Elegí un parámetro --</option>');
    if (!prod) { selPar.prop('disabled', true); return; }

    var idsPresentes = {};
    bloque.find('tbody tr input[type=hidden]').each(function() {
        idsPresentes[\$(this).val()] = true;
    });

    var agregados = 0;
    \$.each(porProducto[prod], function(idp, nombre) {
        if (!idsPresentes[idp]) {
            selPar.append('<option value=\"'+idp+'\">'+nombre+'</option>');
            agregados++;
        }
    });

    if (agregados === 0) {
        selPar.prop('disabled', true);
        footer.find('.btn-agregar-param')
              .prop('disabled', true)
              .html('<i class=\"fa fa-check\"></i> Todos agregados');
    } else {
        selPar.prop('disabled', false);
        footer.find('.btn-agregar-param')
              .prop('disabled', false)
              .html('<i class=\"fa fa-plus\"></i> Agregar');
    }
});

\$(document).on('click', '.btn-agregar-param', function() {
    var footer = \$(this).closest('.box-footer');
    var bloque = \$(this).closest('.seccion-bloque');
    var si     = bloque.data('index');
    var idp    = footer.find('.select-parametro').val();
    if (!idp) { alert('Elegí un parámetro primero.'); return; }
    var param  = paramData[idp];
    if (!param) { alert('Parámetro no encontrado.'); return; }
    var tbody  = bloque.find('.resultado-filas');
    var ri     = tbody.find('tr').length;
    tbody.append(nuevaFila(si, ri, param));
    footer.find('.select-parametro option[value=\"' + idp + '\"]').remove();
    footer.find('.select-parametro').val('');
    if (footer.find('.select-parametro option').length <= 1) {
        footer.find('.select-parametro').prop('disabled', true);
        footer.find('.btn-agregar-param')
              .prop('disabled', true)
              .html('<i class=\"fa fa-check\"></i> Todos agregados');
    }
});

\$('button[value=\"guardar_imprimir\"]').on('click', function(e) {
    e.preventDefault();
    \$('#form-resultados').attr('action', \$('#form-resultados').attr('action') + '&accion=guardar_imprimir');
    \$('#form-resultados').submit();
});

// Alerta: pinta en vivo el input/select de rojo cuando se marca el
// checkbox, y lo saca cuando se desmarca. Un solo listener sirve para
// todas las filas, incluidas las que se agregan después.
\$(document).on('change', '.chk-alerta', function() {
    var campo = \$(this).closest('tr').find('.valor-resultado');
    campo.toggleClass('valor-alerta', this.checked);
});

// ============================================
// TABLA LOOKUP HEMOGRAMA COMPLETO (Excel)
// ============================================
var tablaHemograma = {
    9:  { gr: 990000,  hgb: 3.0,  vcm: 90.9, hcm: 30.3, chcm: 33.3 },
    10: { gr: 1110000, hgb: 3.3,  vcm: 90.9, hcm: 30.0, chcm: 33.0 },
    11: { gr: 1210000, hgb: 3.6,  vcm: 90.9, hcm: 29.8, chcm: 32.7 },
    12: { gr: 1320000, hgb: 4.0,  vcm: 90.9, hcm: 30.3, chcm: 33.3 },
    13: { gr: 1430000, hgb: 4.6,  vcm: 90.9, hcm: 29.9, chcm: 32.9 },
    14: { gr: 1540000, hgb: 4.6,  vcm: 90.9, hcm: 29.9, chcm: 32.9 },
    15: { gr: 1650000, hgb: 5.0,  vcm: 90.9, hcm: 30.3, chcm: 33.3 },
    16: { gr: 1760000, hgb: 5.3,  vcm: 90.9, hcm: 30.1, chcm: 33.1 },
    17: { gr: 1870000, hgb: 5.6,  vcm: 90.9, hcm: 29.9, chcm: 32.9 },
    18: { gr: 2000000, hgb: 5.9,  vcm: 90.0, hcm: 29.5, chcm: 32.8 },
    19: { gr: 2100000, hgb: 6.3,  vcm: 90.5, hcm: 30.0, chcm: 33.2 },
    20: { gr: 2200000, hgb: 6.6,  vcm: 90.9, hcm: 30.0, chcm: 33.0 },
    21: { gr: 2400000, hgb: 6.9,  vcm: 87.5, hcm: 28.8, chcm: 32.9 },
    22: { gr: 2500000, hgb: 7.3,  vcm: 88.0, hcm: 29.2, chcm: 33.2 },
    23: { gr: 2600000, hgb: 7.5,  vcm: 88.5, hcm: 29.2, chcm: 33.1 },
    24: { gr: 2700000, hgb: 7.8,  vcm: 88.9, hcm: 29.3, chcm: 32.9 },
    25: { gr: 2800000, hgb: 8.1,  vcm: 89.3, hcm: 29.6, chcm: 33.2 },
    26: { gr: 3000000, hgb: 8.7,  vcm: 86.7, hcm: 28.7, chcm: 33.1 },
    27: { gr: 3100000, hgb: 9.0,  vcm: 87.1, hcm: 28.7, chcm: 33.0 },
    28: { gr: 3200000, hgb: 9.3,  vcm: 87.5, hcm: 28.8, chcm: 32.9 },
    29: { gr: 3300000, hgb: 9.6,  vcm: 87.9, hcm: 29.1, chcm: 33.1 },
    30: { gr: 3400000, hgb: 9.8,  vcm: 88.2, hcm: 29.1, chcm: 33.0 },
    31: { gr: 3600000, hgb: 10.4, vcm: 86.1, hcm: 28.3, chcm: 32.9 },
    32: { gr: 3700000, hgb: 10.7, vcm: 86.5, hcm: 28.6, chcm: 33.1 },
    33: { gr: 3800000, hgb: 11.0, vcm: 86.8, hcm: 28.7, chcm: 33.0 },
    34: { gr: 3900000, hgb: 11.3, vcm: 87.2, hcm: 28.7, chcm: 32.9 },
    35: { gr: 4000000, hgb: 11.6, vcm: 87.5, hcm: 29.0, chcm: 33.1 },
    36: { gr: 4100000, hgb: 11.9, vcm: 87.8, hcm: 29.0, chcm: 33.1 },
    37: { gr: 4300000, hgb: 12.4, vcm: 86.0, hcm: 28.4, chcm: 33.0 },
    38: { gr: 4400000, hgb: 12.8, vcm: 86.4, hcm: 28.4, chcm: 32.9 },
    39: { gr: 4500000, hgb: 13.1, vcm: 86.7, hcm: 28.7, chcm: 33.1 },
    40: { gr: 4600000, hgb: 13.3, vcm: 87.0, hcm: 28.7, chcm: 33.0 },
    41: { gr: 4700000, hgb: 13.6, vcm: 87.2, hcm: 28.7, chcm: 32.9 },
    42: { gr: 4800000, hgb: 13.9, vcm: 87.5, hcm: 29.0, chcm: 33.1 },
    43: { gr: 4900000, hgb: 14.2, vcm: 87.8, hcm: 29.0, chcm: 33.0 },
    44: { gr: 5100000, hgb: 14.8, vcm: 86.3, hcm: 28.4, chcm: 33.0 },
    45: { gr: 5200000, hgb: 15.1, vcm: 86.5, hcm: 28.7, chcm: 33.1 },
    46: { gr: 5300000, hgb: 15.4, vcm: 86.8, hcm: 28.7, chcm: 33.0 },
    47: { gr: 5400000, hgb: 15.7, vcm: 87.0, hcm: 28.7, chcm: 33.0 },
    48: { gr: 5500000, hgb: 16.0, vcm: 87.3, hcm: 28.7, chcm: 32.9 },
    49: { gr: 5600000, hgb: 16.2, vcm: 87.5, hcm: 28.9, chcm: 33.1 },
    50: { gr: 5700000, hgb: 16.5, vcm: 87.7, hcm: 28.9, chcm: 33.0 },
};

\$(document).on('input', 'input[name*=\"[valor]\"]', function() {
    var fila    = \$(this).closest('tr');
    var seccion = \$(this).closest('.seccion-bloque');
    var nombre  = fila.find('strong').first().text().trim()
                      .toLowerCase().replace(/\./g, '').replace(/\s+/g, ' ');

    if (nombre !== 'hematocrito') return;

    var hto = parseInt(\$(this).val());
    if (!hto || isNaN(hto) || hto < 9 || hto > 50) return;

    var vals = tablaHemograma[hto];
    if (!vals) return;

    seccion.find('tbody tr').each(function() {
        var n = \$(this).find('strong').first().text().trim()
                       .toLowerCase().replace(/\./g, '').replace(/\s+/g, ' ');
        var input = \$(this).find('input[name*=\"[valor]\"]');
        if (!input.length) return;

        if (n === 'glóbulos rojos' || n === 'globulos rojos') input.val(vals.gr);
        if (n === 'hemoglobina')                               input.val(vals.hgb);
        if (n === 'vcm' || n === 'v c m')                     input.val(vals.vcm);
        if (n === 'hcm' || n === 'h c m')                     input.val(vals.hcm);
        if (n === 'chcm' || n === 'c h c m')                  input.val(vals.chcm);
    });
});

// Observaciones/Conclusión: ahora hay una por sección, no una sola
// para toda la orden — por eso esto es una función reutilizable por
// clase, no un ID fijo. 'scope' es dónde buscar (todo el documento al
// cargar la página, o solo el bloque recién agregado cuando se crea
// una sección nueva a mano).
function initEditoresRicos(scope) {
    scope.find('.observaciones-editor, .conclusion-editor').each(function() {
        if (!\$(this).next('.note-editor').length) {
            \$(this).summernote({
                height: 100,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline']],
                    ['para', ['ul', 'ol', 'paragraph']]
                ]
            });
        }
    });
}
initEditoresRicos(\$(document));


", \yii\web\View::POS_READY);
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/summernote/0.9.1/summernote.min.css');
$this->registerJsFile(
    'https://cdnjs.cloudflare.com/ajax/libs/summernote/0.9.1/summernote.min.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);
?>

<style>
.chk-alerta {
    transform: scale(1.2);
    cursor: pointer;
}
.valor-alerta {
    color: #ff1800 !important;
    font-weight: bold !important;
    border-color: #ff1800 !important;
}
</style>