<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$this->title = 'Editar Orden #' . ($model->numero_referencia ?: $model->idorden);

$doctores = \yii\helpers\ArrayHelper::map(
    \app\models\Doctores::find()->orderBy('nombre')->all(),
    'iddoctor', 'nombre'
);

// Catálogo completo de análisis, igual que en carrito.php
$productosObj = \app\models\Productos::find()
    ->innerJoinWith('categoria')
    ->where(['<>', 'categorias_servicio.alias', 'productosg'])
    ->orderBy(['categorias_servicio.nombre' => SORT_ASC, 'productos.titulo' => SORT_ASC])
    ->all();

$productosData = [];
foreach ($productosObj as $p) {
    $productosData[$p->idproducto] = [
        'idproducto'  => $p->idproducto,
        'titulo'      => $p->titulo,
        'precio'      => (float)$p->precio,
        'categoria'   => $p->categoria->nombre ?? 'Sin categoría',
        'idcategoria' => $p->idcategoria,
    ];
}

$total = array_sum(array_column($detalles, 'precio'));
?>

<section class="content-header">
    <h1><?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
<div class="row">

    <div class="col-sm-7">
    <div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Datos del paciente</h3>
    </div>
    <div class="box-body">

    <?php $form = ActiveForm::begin([
        'type'       => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 4, 'deviceSize' => ActiveForm::SIZE_SMALL],
        'id'         => 'form-orden-editar',
    ]); ?>

    <?= $form->field($model, 'numero_referencia')->textInput(['placeholder' => 'Ej: 6781']) ?>

    <?= $form->field($model, 'paciente_nombre')->textInput(['placeholder' => 'Nombre completo del paciente']) ?>

    <?= $form->field($model, 'paciente_edad')->textInput(['placeholder' => 'Ej: 35 años']) ?>

    <?= $form->field($model, 'paciente_telefono')->textInput(['placeholder' => 'Ej: 70000000']) ?>

    <?= $form->field($model, 'solicitante_nombre')->textInput(['placeholder' => 'Opcional — si es distinto al paciente']) ?>

    <?= $form->field($model, 'iddoctor')->widget(Select2::classname(), [
        'data'          => $doctores,
        'value'         => $model->iddoctor,
        'options'       => ['placeholder' => 'Seleccioná el doctor (opcional)'],
        'pluginOptions' => ['allowClear' => true],
    ]) ?>

    <input type="hidden" name="Ordenes[tipo_orden]" value="orden">

    <hr>

    <!-- BUSCADOR DE ANÁLISIS: mismo mecanismo que carrito.php (buscar por
         categoría o por análisis, click = agrega directo a la tabla).
         Acá no hay sesión/carrito de por medio: la lista se arma en
         memoria a partir de lo que ya tenía la orden. -->
    <div class="form-group">
        <label class="col-sm-4 control-label">Agregar análisis</label>
        <div class="col-sm-8">
            <?= Select2::widget([
                'name'          => 'prueba_selector',
                'data'          => [],
                'options'       => ['id' => 'prueba-select', 'placeholder' => 'Escribí el nombre del análisis o de la categoría...'],
                'pluginOptions' => ['allowClear' => true, 'width' => '100%'],
            ]) ?>
        </div>
    </div>

    <div class="col-sm-offset-4 col-sm-8" style="margin-bottom:15px">
        <table class="table table-bordered table-condensed" id="tabla-items">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>Análisis</th>
                    <th width="110">Precio (Bs)</th>
                    <th width="40"></th>
                </tr>
            </thead>
            <tbody id="items-body"></tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-right">TOTAL:</th>
                    <th id="items-total">Bs. 0.00</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
        <div id="items-hidden"></div>
    </div>

    <hr>

    <!-- Total de lista: se recalcula solo según los análisis de arriba -->
    <div class="form-group">
        <label class="col-sm-4 control-label">Total (Bs)</label>
        <div class="col-sm-8">
            <p class="form-control-static">
                <strong id="total-lista-display" style="font-size:18px">Bs. <?= number_format($total, 2) ?></strong>
            </p>
        </div>
    </div>

    <?= $form->field($model, 'descuento')->textInput([
        'id'          => 'descuento',
        'placeholder' => '0.00',
        'inputmode'   => 'decimal',
        'value'       => $model->descuento ?: '0.00',
    ]) ?>

    <div class="form-group">
        <label class="col-sm-4 control-label">Total a pagar (Bs)</label>
        <div class="col-sm-8">
            <input type="text" id="total-pagar-display" class="form-control" readonly
                   value="<?= number_format($total - ($model->descuento ?? 0), 2) ?>">
        </div>
    </div>

    <?php
    // Cuánto suman los pagos "congelados" — todos menos el último
    // (ver OrdenesService::sincronizarPagoDesdeEdicion). Con 0 o 1
    // pago esto siempre da 0, así que no hay ningún piso real en el
    // caso más común — el aviso de abajo solo aparece cuando aplica.
    $pisoMinimo = 0;
    if (count($pagos) > 1) {
        $ultimoPago = end($pagos);
        foreach ($pagos as $p) {
            if ($p->idpago === $ultimoPago->idpago) continue;
            $pisoMinimo += (float)$p->monto;
        }
    }
    ?>
    <div class="form-group">
        <label class="col-sm-4 control-label">Pagado hasta ahora (Bs)</label>
        <div class="col-sm-8">
            <p class="form-control-static">Bs. <?= number_format($model->monto_pagado, 2) ?></p>
            <?php if ($pisoMinimo > 0): ?>
                <p class="help-block text-danger" style="margin-top:0">
                    <i class="fa fa-lock"></i>
                    Esta orden tiene <?= count($pagos) ?> pagos registrados. Los primeros <?= count($pagos) - 1 ?>
                    ya están congelados (suman Bs. <?= number_format($pisoMinimo, 2) ?>) — lo que pongas abajo en
                    Efectivo + QR no puede ser menor a eso. Si necesitás bajar de ahí, editá el último pago
                    desde el historial de la orden en vez de acá.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Forma de pago: mismo mecanismo que create.php/pago.php — Efectivo
         + QR, cambio a devolver, monto pagado se calcula solo, capeado
         al total a pagar antes de guardar. -->
    <div class="form-group">
        <label class="col-sm-4 control-label">Forma de pago</label>
        <div class="col-sm-8">
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-money"></i> Efectivo</span>
                        <input type="text" id="pago-efectivo" class="form-control"
                               inputmode="decimal" placeholder="0.00"
                               value="<?= $model->monto_efectivo ?: '' ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-qrcode"></i> QR</span>
                        <input type="text" id="pago-qr" class="form-control"
                               inputmode="decimal" placeholder="0.00"
                               value="<?= $model->monto_qr ?: '' ?>">
                    </div>
                </div>
            </div>
            <p class="help-block" style="margin-bottom:0">
                Si suma más que el total, abajo se calcula el cambio.
            </p>
        </div>
    </div>

    <div class="form-group" id="alerta-piso-minimo" style="display:none; margin-top:-10px; margin-bottom:15px">
        <div class="col-sm-offset-4 col-sm-8">
            <span class="text-danger">
                <i class="fa fa-lock"></i>
                Efectivo + QR no puede sumar menos de Bs. <?= number_format($pisoMinimo, 2) ?>
                (lo ya congelado en pagos anteriores).
            </span>
        </div>
    </div>

    <input type="hidden" id="monto-efectivo-hidden" name="Ordenes[monto_efectivo]" value="<?= $model->monto_efectivo ?: '0' ?>">
    <input type="hidden" id="monto-qr-hidden" name="Ordenes[monto_qr]" value="<?= $model->monto_qr ?: '0' ?>">
    <input type="hidden" id="tipo-pago-hidden" name="Ordenes[tipo_pago]" value="<?= Html::encode($model->tipo_pago ?: 'efectivo') ?>">

    <div class="form-group" id="grupo-cambio" style="display:none">
        <label class="col-sm-4 control-label">Cambio a devolver</label>
        <div class="col-sm-8">
            <p class="form-control-static" id="cambio-display">
                <strong style="font-size:18px">Bs. 0.00</strong>
            </p>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-4 control-label">Monto pagado (Bs)</label>
        <div class="col-sm-8">
            <input type="text" id="monto-pagado" name="Ordenes[monto_pagado]" class="form-control" readonly
                   value="<?= $model->monto_pagado ?: '0.00' ?>">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-4 control-label">Saldo (Bs)</label>
        <div class="col-sm-8">
            <input type="text" id="saldo-display" class="form-control" readonly
                   value="<?= number_format($model->saldo, 2) ?>">
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
            <a href="<?= Url::to(['view', 'id' => $model->idorden]) ?>" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Cancelar
            </a>
            <?= Html::submitButton('<i class="fa fa-check"></i> Guardar cambios', ['class' => 'btn btn-success', 'id' => 'btn-guardar-orden']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    </div>
    </div>
    </div>

</div>
</section>

<?php
$this->registerJs(
    "var productos = " . json_encode($productosData, JSON_HEX_TAG) . ";" .
    "var itemsIniciales = " . json_encode(array_values($detalles), JSON_HEX_TAG) . ";",
    \yii\web\View::POS_HEAD
);

$this->registerJs("
    var pisoMinimo = " . json_encode((float)$pisoMinimo) . ";
    function num(val) { return parseFloat((val || '').toString().trim().replace(',', '.')) || 0; }

    // ---------- Buscador de análisis (igual que carrito.php) ----------
    var porCategoriaTmp = {};
    \$.each(productos, function(id, prod) {
        if (!porCategoriaTmp[prod.categoria]) porCategoriaTmp[prod.categoria] = [];
        porCategoriaTmp[prod.categoria].push({ id: prod.idproducto, text: prod.titulo + ' — Bs. ' + parseFloat(prod.precio).toFixed(2) });
    });
    var gruposPrueba = [];
    \$.each(porCategoriaTmp, function(catNombre, items) { gruposPrueba.push({ text: catNombre, children: items }); });

    function matcherPruebas(params, data) {
        if (\$.trim(params.term) === '') return data;
        var term = params.term.toLowerCase();
        if (data.children) {
            if (data.text.toLowerCase().indexOf(term) > -1) return \$.extend(true, {}, data);
            var hijosFiltrados = [];
            \$.each(data.children, function(i, hijo) { if (hijo.text.toLowerCase().indexOf(term) > -1) hijosFiltrados.push(hijo); });
            if (hijosFiltrados.length === 0) return null;
            var grupoFiltrado = \$.extend(true, {}, data);
            grupoFiltrado.children = hijosFiltrados;
            return grupoFiltrado;
        }
        return data.text.toLowerCase().indexOf(term) > -1 ? data : null;
    }

    \$('#prueba-select').select2({
        data: gruposPrueba,
        placeholder: 'Escribí el nombre del análisis o de la categoría...',
        allowClear: true,
        width: '100%',
        matcher: matcherPruebas
    });

    // ---------- Lista de análisis de la orden (en memoria) ----------
    var itemsOrden = {};
    \$.each(itemsIniciales, function(i, item) { itemsOrden[item.idproducto] = item; });

    function totalListaActual() {
        var t = 0;
        \$.each(itemsOrden, function(id, item) { t += parseFloat(item.precio); });
        return t;
    }

    function renderItems() {
        var tbody  = \$('#items-body');
        var hidden = \$('#items-hidden');
        tbody.empty();
        hidden.empty();
        var contador = 1;
        \$.each(itemsOrden, function(idproducto, item) {
            tbody.append(
                '<tr><td>' + contador + '</td><td>' + item.titulo + '</td>' +
                '<td>Bs. ' + parseFloat(item.precio).toFixed(2) + '</td>' +
                '<td><button type=\"button\" class=\"btn btn-xs btn-danger btn-quitar-item\" data-id=\"' + idproducto + '\"><i class=\"fa fa-times\"></i></button></td></tr>'
            );
            hidden.append(
                '<input type=\"hidden\" name=\"detalles[' + contador + '][idproducto]\" value=\"' + idproducto + '\">' +
                '<input type=\"hidden\" name=\"detalles[' + contador + '][precio]\" value=\"' + item.precio + '\">'
            );
            contador++;
        });
        if (contador === 1) {
            tbody.append('<tr><td colspan=\"4\" class=\"text-center text-muted\"><em>Sin análisis. Buscá y agregá al menos uno.</em></td></tr>');
        }
        var total = totalListaActual();
        \$('#items-total').text('Bs. ' + total.toFixed(2));
        \$('#total-lista-display').text('Bs. ' + total.toFixed(2));
        recalcularPago();
    }

    \$('#prueba-select').on('select2:select', function(e) {
        var id = parseInt(e.params.data.id);
        \$(this).val(null).trigger('change');
        if (!id || itemsOrden[id]) return; // ya está agregado, no se duplica
        var prod = productos[id];
        if (!prod) return;
        itemsOrden[id] = { idproducto: prod.idproducto, titulo: prod.titulo, precio: prod.precio, categoria: prod.categoria };
        renderItems();
    });

    \$(document).on('click', '.btn-quitar-item', function() {
        var id = \$(this).data('id');
        delete itemsOrden[id];
        renderItems();
    });

    // ---------- Pago: mismo algoritmo que create.php/pago.php ----------
    function recalcularPago() {
        var totalLista = totalListaActual();
        var descuento  = num(\$('#descuento').val());
        var totalPagar = Math.max(0, totalLista - descuento);

        var efectivoIngresado = num(\$('#pago-efectivo').val());
        var qrIngresado       = num(\$('#pago-qr').val());
        var totalBruto        = efectivoIngresado + qrIngresado;

        if (totalBruto < pisoMinimo) {
            \$('#alerta-piso-minimo').show();
            \$('#btn-guardar-orden').prop('disabled', true);
        } else {
            \$('#alerta-piso-minimo').hide();
            \$('#btn-guardar-orden').prop('disabled', false);
        }

        var cambio           = Math.max(0, totalBruto - totalPagar);
        var efectivoAplicado = Math.max(0, efectivoIngresado - cambio);
        var cambioRestante   = Math.max(0, cambio - efectivoIngresado);
        var qrAplicado       = Math.max(0, qrIngresado - cambioRestante);
        var pagado = efectivoAplicado + qrAplicado;

        \$('#monto-efectivo-hidden').val(efectivoAplicado.toFixed(2));
        \$('#monto-qr-hidden').val(qrAplicado.toFixed(2));
        \$('#monto-pagado').val(pagado.toFixed(2));

        var tipoPago = 'efectivo';
        if (efectivoAplicado > 0 && qrAplicado > 0)       tipoPago = 'mixto';
        else if (qrAplicado > 0 && efectivoAplicado <= 0) tipoPago = 'qr';
        \$('#tipo-pago-hidden').val(tipoPago);

        if (cambio > 0) {
            \$('#grupo-cambio').show();
            \$('#cambio-display').html(
                '<strong style=\"font-size:18px; color:#c0392b\">Bs. ' + cambio.toFixed(2) + '</strong> '
                + '<span class=\"text-danger\">— Devolver cambio</span>'
            );
        } else {
            \$('#grupo-cambio').hide();
        }

        var saldo = Math.max(0, totalPagar - pagado);
        \$('#total-pagar-display').val(totalPagar.toFixed(2));
        \$('#saldo-display').val(saldo.toFixed(2));
    }

    \$('#descuento, #pago-efectivo, #pago-qr').on('input', recalcularPago);

    // Validación mínima: no dejar guardar sin análisis
    \$('#form-orden-editar').on('submit', function(e) {
        if (\$.isEmptyObject(itemsOrden)) {
            e.preventDefault();
            alert('La orden debe tener al menos un análisis.');
        }
    });

    renderItems();
");
?>