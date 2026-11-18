<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;

$this->title = 'Nueva Orden - Seleccionar Pruebas';

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

$itemsActuales = $itemsActuales ?? [];
$soloItems     = array_values(array_filter($itemsActuales, fn($k) => is_int($k), ARRAY_FILTER_USE_KEY));
?>

<section class="content-header">
    <h1><?= Html::encode($this->title) ?></h1>
</section>

<section class="content">
<div class="row">
<div class="col-xs-12">
<div class="box">
<div class="box-body">

<!-- DATOS DEL PACIENTE -->
<div class="row" style="margin-bottom:15px">
    <div class="col-sm-6">
        <div class="form-group">
            <label>Paciente <span class="text-danger">*</span></label>
            <input type="text" id="campo-paciente" class="form-control"
                   placeholder="Nombre completo del paciente"
                   value="<?= Html::encode($itemsActuales['_paciente'] ?? '') ?>">
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label>Edad</label>
            <input type="text" id="campo-edad" class="form-control"
                   placeholder="Ej: 35 años"
                   value="<?= Html::encode($itemsActuales['_edad'] ?? '') ?>">
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" id="campo-telefono" class="form-control"
                   placeholder="Ej: 70000000"
                   value="<?= Html::encode($itemsActuales['_telefono'] ?? '') ?>">
        </div>
    </div>
</div>
<div class="row" style="margin-bottom:15px">
    <div class="col-sm-6">
        <div class="form-group">
            <label>Solicitante</label>
            <input type="text" id="campo-solicitante" class="form-control"
                   placeholder="Opcional — si es distinto al paciente"
                   value="<?= Html::encode($itemsActuales['_solicitante'] ?? '') ?>">
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label>Doctor</label>
            <?= Select2::widget([
                'name'          => 'campo_doctor_select',
                'data'          => \yii\helpers\ArrayHelper::map(
                    \app\models\Doctores::find()->orderBy('nombre')->all(),
                    'iddoctor', 'nombre'
                ),
                'value'         => $itemsActuales['_iddoctor'] ?? null,
                'options'       => ['id' => 'campo-doctor', 'placeholder' => 'Doctor (opcional)'],
                'pluginOptions' => ['allowClear' => true],
            ]) ?>
        </div>
    </div>
</div>

<hr>

<!-- BUSCADOR DE PRUEBAS -->
<div class="row">
    <div class="col-sm-4">
        <label>Categoría <small class="text-muted">(opcional, es solo un atajo)</small></label>
        <?= Select2::widget([
            'name'          => 'categoria_selector',
            'data'          => \yii\helpers\ArrayHelper::map(
                \app\models\CategoriasServicio::find()
                    ->where(['<>', 'alias', 'productosg'])
                    ->andWhere(['estado' => 1])
                    ->orderBy('nombre')
                    ->all(),
                'idcategoria', 'nombre'
            ),
            'options'       => ['id' => 'categoria-select', 'placeholder' => 'Filtrar por categoría...'],
            'pluginOptions' => ['allowClear' => true],
        ]) ?>
    </div>
    <div class="col-sm-8">
        <label>Prueba / Análisis</label>
        <?= Select2::widget([
            'name'          => 'prueba_selector',
            'data'          => [],
            'options'       => ['id' => 'prueba-select', 'placeholder' => 'Escribí el nombre del análisis o de la categoría...'],
            'pluginOptions' => ['allowClear' => true, 'width' => '100%'],
        ]) ?>
        <small class="text-muted">Al hacer click en un resultado, se agrega directo al carrito.</small>
    </div>
</div>

<hr>

<table class="table table-bordered" id="tabla-orden">
    <thead>
        <tr>
            <th width="40">#</th>
            <th>Análisis</th>
            <th width="130">Precio (Bs)</th>
            <th width="50"></th>
        </tr>
    </thead>
    <tbody id="carrito-body">
        <tr>
            <td colspan="4" class="text-center text-muted">
                <em>Todavía no hay análisis agregados.</em>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2" class="text-right">TOTAL:</th>
            <th id="carrito-total">Bs. 0.00</th>
            <th></th>
        </tr>
    </tfoot>
</table>

<div id="msg-orden" class="alert" style="display:none"></div>

<!-- BOTONES FINALES -->
<div class="row" style="margin-top:15px">
    <div class="col-sm-12">
        <a href="<?= Url::to(['index']) ?>" class="btn btn-default">
            <i class="fa fa-times"></i> Cancelar
        </a>
        <button type="button" id="btn-cotizar" class="btn btn-warning" disabled style="margin-left:5px">
            <i class="fa fa-file-text-o"></i> Guardar y Cotizar
        </button>
        <button type="button" id="btn-siguiente" class="btn btn-success pull-right" disabled>
            <i class="fa fa-check"></i> Registrar Orden <i class="fa fa-arrow-right"></i>
        </button>
    </div>
</div>

</div>
</div>
</div>
</div>
</section>

<?php
// BLOQUE 1 — datos PHP → JS
$this->registerJs(
    "var productos       = " . json_encode($productosData, JSON_HEX_TAG) . ";" .
    "var itemsExistentes = " . json_encode($soloItems, JSON_HEX_TAG) . ";",
    \yii\web\View::POS_HEAD
);

$urlAgregar      = Url::to(['agregar-item']);
$urlQuitar       = Url::to(['quitar-item']);
$urlSig          = Url::to(['create']);
$urlCotizar      = Url::to(['cotizar']);
$urlGuardarDatos = Url::to(['guardar-datos']);
$csrf            = Yii::$app->request->csrfToken;
$csrfParam       = Yii::$app->request->csrfParam;

$this->registerJs("
var csrfToken       = '{$csrf}';
var csrfParam       = '{$csrfParam}';
var urlAgregar      = '{$urlAgregar}';
var urlQuitar       = '{$urlQuitar}';
var urlSig          = '{$urlSig}';
var urlCotizar      = '{$urlCotizar}';
var urlGuardarDatos = '{$urlGuardarDatos}';

\$.ajaxSetup({ data: { [csrfParam]: csrfToken } });

function renderTabla(items, total) {
    var tbody = \$('#carrito-body');
    tbody.empty();
    if (!items || items.length === 0) {
        tbody.append('<tr><td colspan=\"4\" class=\"text-center text-muted\"><em>Todavía no hay análisis agregados.</em></td></tr>');
        \$('#carrito-total').text('Bs. 0.00');
        \$('#btn-siguiente, #btn-cotizar').prop('disabled', true);
        return;
    }
    var grupos = {};
    var orden  = [];
    \$.each(items, function(i, item) {
        if (!item || !item.idproducto) return;
        var cat = item.categoria || 'Sin categoría';
        if (!grupos[cat]) { grupos[cat] = []; orden.push(cat); }
        grupos[cat].push(item);
    });
    var num = 1;
    \$.each(orden, function(i, categoria) {
        tbody.append('<tr style=\"background:#eef2f7;\"><td colspan=\"4\"><strong><i class=\"fa fa-flask\"></i> ' + categoria + '</strong></td></tr>');
        \$.each(grupos[categoria], function(j, item) {
            tbody.append(
                '<tr><td>' + num++ + '</td><td>' + item.titulo + '</td>' +
                '<td>Bs. ' + parseFloat(item.precio).toFixed(2) + '</td>' +
                '<td><button type=\"button\" class=\"btn btn-xs btn-danger btn-quitar\" data-id=\"' + item.idproducto + '\"><i class=\"fa fa-times\"></i></button></td></tr>'
            );
        });
    });
    \$('#carrito-total').text('Bs. ' + total);
    \$('#btn-siguiente, #btn-cotizar').prop('disabled', false);
}

function mostrarMsg(texto, tipo) {
    var div = \$('#msg-orden');
    div.removeClass('alert-success alert-danger alert-warning').addClass('alert-' + tipo);
    div.text(texto).show();
    setTimeout(function() { div.fadeOut(); }, 3000);
}

// ----------------------------------------------------------------
// Construir data agrupada por categoría para el buscador de pruebas.
// Cada 'grupo' es una categoría (optgroup) y sus 'children' son los
// análisis de esa categoría. Así Select2 puede mostrar el grupo
// completo cuando el usuario busca por nombre de categoría.
// ----------------------------------------------------------------
var porCategoriaTmp = {};
\$.each(productos, function(id, prod) {
    if (!porCategoriaTmp[prod.categoria]) porCategoriaTmp[prod.categoria] = [];
    porCategoriaTmp[prod.categoria].push({
        id:   prod.idproducto,
        text: prod.titulo + ' — Bs. ' + parseFloat(prod.precio).toFixed(2)
    });
});
var gruposPrueba = [];
\$.each(porCategoriaTmp, function(catNombre, items) {
    gruposPrueba.push({ text: catNombre, children: items });
});

// Matcher personalizado: hace match tanto por el nombre del análisis
// como por el nombre de la categoría (el optgroup).
function matcherPruebas(params, data) {
    if (\$.trim(params.term) === '') return data;
    var term = params.term.toLowerCase();

    // Es un grupo (categoría)
    if (data.children) {
        // Si el nombre de la categoría matchea, mostrar todos sus análisis
        if (data.text.toLowerCase().indexOf(term) > -1) {
            return \$.extend(true, {}, data);
        }
        // Si no, filtrar los análisis hijos uno por uno
        var hijosFiltrados = [];
        \$.each(data.children, function(i, hijo) {
            if (hijo.text.toLowerCase().indexOf(term) > -1) hijosFiltrados.push(hijo);
        });
        if (hijosFiltrados.length === 0) return null;
        var grupoFiltrado = \$.extend(true, {}, data);
        grupoFiltrado.children = hijosFiltrados;
        return grupoFiltrado;
    }

    // Opción individual (por si acaso)
    return data.text.toLowerCase().indexOf(term) > -1 ? data : null;
}

\$('#prueba-select').select2({
    data:           gruposPrueba,
    placeholder:    'Escribí el nombre del análisis o de la categoría...',
    allowClear:     true,
    width:          '100%',
    matcher:        matcherPruebas,
});

// Agrega un producto al carrito (AJAX) — reutilizable
function agregarProducto(id) {
    var prod = productos[id];
    if (!prod) { mostrarMsg('Producto no encontrado.', 'danger'); return; }
    \$.post(urlAgregar, {
        idproducto: prod.idproducto,
        titulo:     prod.titulo,
        precio:     prod.precio,
        categoria:  prod.categoria,
    }, function(r) {
        if (r.exito) {
            renderTabla(r.items, r.total);
        } else {
            mostrarMsg(r.mensaje, 'warning');
        }
    });
}

// Al hacer click/seleccionar un análisis en el buscador, se agrega
// automáticamente al carrito (sin botón 'Agregar').
\$('#prueba-select').on('select2:select', function(e) {
    var id = parseInt(e.params.data.id);
    if (!id) return;
    agregarProducto(id);
    // Limpiamos la selección para poder seguir buscando/agregando
    \$(this).val(null).trigger('change');
});

// El selector de Categoría es solo un atajo: al elegir una categoría
// abre el buscador de pruebas con el nombre de esa categoría ya
// escrito, para que el usuario vea de una todos sus análisis.
\$('#categoria-select').on('change', function() {
    var idcat = \$(this).val();
    if (!idcat) return;
    var catNombre = \$(this).find('option:selected').text();
    var \$prueba   = \$('#prueba-select');
    \$prueba.select2('open');
    setTimeout(function() {
        \$('.select2-container--open .select2-search__field')
            .val(catNombre)
            .trigger('input');
    }, 30);
});

\$(document).on('click', '.btn-quitar', function() {
    var id = \$(this).data('id');
    \$.post(urlQuitar, { idproducto: id }, function(r) {
        if (r.exito) renderTabla(r.items, r.total);
    });
});

// Función común: guardar datos del paciente y luego redirigir
function guardarYRedirigir(urlDestino) {
    var paciente = \$('#campo-paciente').val().trim();
    if (!paciente) {
        mostrarMsg('El nombre del paciente es obligatorio.', 'danger');
        \$('#campo-paciente').focus();
        return;
    }
    \$.post(urlGuardarDatos, {
        paciente:    paciente,
        edad:        \$('#campo-edad').val(),
        telefono:    \$('#campo-telefono').val(),
        solicitante: \$('#campo-solicitante').val(),
        iddoctor:    \$('#campo-doctor').val(),
    }, function(r) {
        if (r.exito) window.location.href = urlDestino;
    });
}

// Cotizar — guarda directo como cotización
\$('#btn-cotizar').on('click', function() {
    guardarYRedirigir(urlCotizar);
});

// Siguiente — va al formulario de pago (orden real)
\$('#btn-siguiente').on('click', function() {
    guardarYRedirigir(urlSig);
});

// Restaurar items al volver
if (itemsExistentes && itemsExistentes.length > 0) {
    var totalExistente = 0;
    \$.each(itemsExistentes, function(i, item) {
        if (item && item.precio) totalExistente += parseFloat(item.precio);
    });
    renderTabla(itemsExistentes, totalExistente.toFixed(2));
}
", \yii\web\View::POS_READY);
?>