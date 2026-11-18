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
?>

<section class="content-header">
    <h1>
        <i class="fa fa-flask"></i> <?= Html::encode($this->title) ?> / 
        <?= Html::encode($model->paciente_nombre) ?> / <?= Html::encode($model->fecha_registro) ?>
    </h1>
</section>

<section class="content">
<div class="row">
<div class="col-xs-12">
<div class="box box-primary">
<div class="box-header with-border">
    <a href="<?= Url::to(['view', 'id' => $model->idorden]) ?>" class="btn btn-warning btn-sm">
        <i class="fa fa-arrow-left"></i> Volver a la orden
    </a>
    <?php if (!empty($secciones)): ?>
    <a href="<?= Url::to(['imprimir-resultados', 'id' => $model->idorden]) ?>"
       target="_blank" class="btn btn-success btn-sm">
        <i class="fa fa-print"></i> Imprimir ficha
    </a>
    <?php endif; ?>
</div>

<div class="box-body">
<form method="post" action="<?= Url::to(['resultados', 'id' => $model->idorden]) ?>" id="form-resultados">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

    <div class="row" style="margin-bottom:20px">
        <div class="col-sm-4">
            <div class="form-group">
                <label><i class="fa fa-clock-o"></i> Fecha y hora de toma de muestra</label>
                <input type="datetime-local" name="fecha_toma_muestra"
                       class="form-control"
                       value="<?= Html::encode($fechaToma) ?>">
            </div>
        </div>
    </div>

    <div id="secciones-container">
        <?php foreach ($secciones as $si => $sec): ?>
        <div class="seccion-bloque" data-index="<?= $si ?>">
            <div class="box box-default" style="border-left:4px solid #3c8dbc">
                <div class="box-header" style="background:#f8f9fa; padding:8px 15px">
                    <div class="row">
                        <div class="col-sm-8">
                            <input type="text"
                                   name="secciones[<?= $si ?>][titulo]"
                                   class="form-control input-titulo"
                                   placeholder="Título de la sección (ej: HEMATOLOGIA)"
                                   value="<?= Html::encode($sec->titulo) ?>"
                                   style="font-weight:bold; text-transform:uppercase">
                        </div>
                        <div class="col-sm-4 text-right">
                            <button type="button" class="btn btn-danger btn-xs btn-eliminar-seccion">
                                <i class="fa fa-trash"></i> Eliminar sección
                            </button>
                        </div>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <table class="table table-bordered table-condensed resultado-tabla">
                        <thead style="background:#dce8f3">
                            <tr>
                                <th style="width:35%">Examen</th>
                                <th style="width:20%">Resultado</th>
                                <th style="width:35%">Valores de Referencia</th>
                                <th style="width:10%"></th>
                            </tr>
                        </thead>
                        <tbody class="resultado-filas">
                            <?php foreach ($sec->resultados as $ri => $res): ?>
                            <tr class="resultado-fila">
                                <td>
                                    <input type="hidden" name="secciones[<?= $si ?>][parametros][<?= $ri ?>][idparametro]"
                                           value="<?= $res->idparametro ?>">
                                    <strong><?= Html::encode($res->parametro->nombre ?? '—') ?></strong>
                                    <?php if ($res->parametro->unidad): ?>
                                        <small class="text-muted">(<?= Html::encode($res->parametro->unidad) ?>)</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($res->parametro->unidad): ?>
                                    <div class="input-group input-group-sm">
                                        <input type="text"
                                               name="secciones[<?= $si ?>][parametros][<?= $ri ?>][valor]"
                                               class="form-control"
                                               value="<?= Html::encode($res->valor ?? '') ?>"
                                               placeholder="—">
                                        <span class="input-group-addon"><?= Html::encode($res->parametro->unidad) ?></span>
                                    </div>
                                    <?php else: ?>
                                    <input type="text"
                                           name="secciones[<?= $si ?>][parametros][<?= $ri ?>][valor]"
                                           class="form-control input-sm"
                                           value="<?= Html::encode($res->valor ?? '') ?>"
                                           placeholder="—">
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:12px; color:#555">
                                    <?= $res->parametro->referencia_texto ? strip_tags($res->parametro->referencia_texto) : '—' ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs btn-danger btn-quitar-fila">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

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
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="margin:15px 0">
        <button type="button" id="btn-nueva-seccion" class="btn btn-default btn-lg btn-block"
                style="border:2px dashed #aaa; color:#555">
            <i class="fa fa-plus-circle"></i> Agregar nueva sección
        </button>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <button type="submit" name="accion" value="guardar" class="btn btn-success">
                <i class="fa fa-save"></i> Guardar
            </button>
            <button type="submit" name="accion" value="guardar_imprimir" class="btn btn-primary">
                <i class="fa fa-print"></i> Guardar e Imprimir
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

$urlImprimir = Url::to(['imprimir-resultados', 'id' => $model->idorden]);

$this->registerJs("
var seccionIndex = \$('.seccion-bloque').length;

function nuevaFila(si, ri, param) {
    var ref = param.referencia_texto || '—';
    var und = param.unidad ? ' <small class=\"text-muted\">('+param.unidad+')</small>' : '';
    var inputResultado = param.unidad
        ? '<div class=\"input-group input-group-sm\">' +
          '<input type=\"text\" name=\"secciones['+si+'][parametros]['+ri+'][valor]\" class=\"form-control\" placeholder=\"—\">' +
          '<span class=\"input-group-addon\">'+param.unidad+'</span>' +
          '</div>'
        : '<input type=\"text\" name=\"secciones['+si+'][parametros]['+ri+'][valor]\" class=\"form-control input-sm\" placeholder=\"—\">';
    return '<tr class=\"resultado-fila\">' +
        '<td>' +
        '<input type=\"hidden\" name=\"secciones['+si+'][parametros]['+ri+'][idparametro]\" value=\"'+param.idparametro+'\">' +
        '<strong>'+param.nombre+'</strong>'+und+
        '</td>' +
        '<td>'+inputResultado+'</td>' +
        '<td style=\"font-size:12px;color:#555\">'+ref+'</td>' +
        '<td class=\"text-center\"><button type=\"button\" class=\"btn btn-xs btn-danger btn-quitar-fila\"><i class=\"fa fa-times\"></i></button></td>' +
        '</tr>';
}

function nuevaSeccion(si) {
    var opciones = '<option value=\"\">-- Elegí un producto --</option>';
    \$.each(porProducto, function(nombre) { opciones += '<option value=\"'+nombre+'\">'+nombre+'</option>'; });
    return '<div class=\"seccion-bloque\" data-index=\"'+si+'\">' +
        '<div class=\"box box-default\" style=\"border-left:4px solid #3c8dbc\">' +
        '<div class=\"box-header\" style=\"background:#f8f9fa;padding:8px 15px\">' +
        '<div class=\"row\"><div class=\"col-sm-8\">' +
        '<input type=\"text\" name=\"secciones['+si+'][titulo]\" class=\"form-control input-titulo\" ' +
        'placeholder=\"Título de la sección (ej: HEMATOLOGIA)\" style=\"font-weight:bold;text-transform:uppercase\">' +
        '</div><div class=\"col-sm-4 text-right\">' +
        '<button type=\"button\" class=\"btn btn-danger btn-xs btn-eliminar-seccion\"><i class=\"fa fa-trash\"></i> Eliminar sección</button>' +
        '</div></div></div>' +
        '<div class=\"box-body no-padding\">' +
        '<table class=\"table table-bordered table-condensed resultado-tabla\">' +
        '<thead style=\"background:#dce8f3\"><tr>' +
        '<th style=\"width:35%\">Examen</th><th style=\"width:20%\">Resultado</th>' +
        '<th style=\"width:35%\">Valores de Referencia</th><th style=\"width:10%\"></th>' +
        '</tr></thead><tbody class=\"resultado-filas\"></tbody></table>' +
        '<div class=\"box-footer\" style=\"background:#f8f9fa\"><div class=\"row\">' +
        '<div class=\"col-sm-5\"><select class=\"form-control select-producto\" style=\"width:100%\">' + opciones + '</select></div>' +
        '<div class=\"col-sm-5\"><select class=\"form-control select-parametro\" style=\"width:100%\" disabled><option value=\"\">-- Primero elegí un producto --</option></select></div>' +
        '<div class=\"col-sm-2\"><button type=\"button\" class=\"btn btn-info btn-block btn-agregar-param\"><i class=\"fa fa-plus\"></i> Agregar</button></div>' +
        '</div></div></div></div></div>';
}

\$('#btn-nueva-seccion').on('click', function() {
    \$('#secciones-container').append(nuevaSeccion(seccionIndex));
    seccionIndex++;
});

\$(document).on('click', '.btn-eliminar-seccion', function() {
    if (confirm('¿Eliminar esta sección completa?')) {
        \$(this).closest('.seccion-bloque').remove();
    }
});

// ACTUALIZADO: al quitar una fila, devuelve el parámetro al select
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
    var selPar = \$(this).closest('.box-footer').find('.select-parametro');
    selPar.empty().append('<option value=\"\">-- Elegí un parámetro --</option>');
    if (!prod) { selPar.prop('disabled', true); return; }
    \$.each(porProducto[prod], function(idp, nombre) {
        selPar.append('<option value=\"'+idp+'\">'+nombre+'</option>');
    });
    selPar.prop('disabled', false);
});

// ACTUALIZADO: al agregar, saca el parámetro del select para que no se repita
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

    // Sacar del select para que no se pueda repetir
    footer.find('.select-parametro option[value=\"' + idp + '\"]').remove();
    footer.find('.select-parametro').val('');

    // Si no quedan opciones, deshabilitar
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
", \yii\web\View::POS_READY);
?>