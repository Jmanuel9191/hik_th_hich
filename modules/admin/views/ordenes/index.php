<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;
use kartik\date\DatePicker;
$this->title = 'Órdenes / Cotizaciones';
?>



  <section class="content-header">
    <h1><i class="fa fa-file-text"></i> <?= Html::encode($this->title) ?></h1>
</section>



<section class="content">
<div class="row">
<div class="col-xs-12">
<div class="box">

<div class="box-header">
    <?= Html::a('<i class="fa fa-plus"></i> Nueva Orden', ['carrito', 'nueva' => 1], ['class' => 'btn btn-success']) ?>
</div>

<div class="box-body">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'columns'      => [
        ['class' => 'yii\grid\SerialColumn'],
        
           'numero_referencia',
           
        'paciente_nombre',
        
        [
            'attribute' => 'iddoctor',
            'header'    => 'Doctor',
            'value'     => fn($m) => $m->doctor->nombre ?? '—',
            'filter'    => Select2::widget([
                'model'         => $searchModel,
                'attribute'     => 'iddoctor',
                'data'          => \yii\helpers\ArrayHelper::map(
                    \app\models\Doctores::find()->orderBy('nombre')->all(),
                    'iddoctor', 'nombre'
                ),
                'options'       => ['placeholder' => 'Buscar doctor...'],
                'pluginOptions' => ['allowClear' => true],
            ]),
        ],
        [
    'header' => 'Resultados',
    'format' => 'raw',
    'value'  => function($m) {
        if (empty($m->detalles)) return '<span class="text-muted">—</span>';

        $nombres = array_map(function($d) {
            return $d->producto->titulo ?? '(desconocido)';
        }, $m->detalles);

        $textoCompleto = implode(', ', array_unique($nombres));

        // Corta a 100 caracteres (sin partir una palabra a la mitad).
        // Si entra completo, no hace falta ningún "ver todo".
        $limite = 100;
        if (mb_strlen($textoCompleto) <= $limite) {
            return '<span class="col-resultados-texto">' . Html::encode($textoCompleto) . '</span>';
        }

        $truncado = mb_substr($textoCompleto, 0, $limite);
        $ultimoEspacio = mb_strrpos($truncado, ' ');
        if ($ultimoEspacio !== false) {
            $truncado = mb_substr($truncado, 0, $ultimoEspacio);
        }

        return '<span class="col-resultados-texto resultados-wrap">'
             . '<span class="res-corto">' . Html::encode($truncado) . '… '
             . '<a href="javascript:void(0)" class="res-toggle">ver todo</a></span>'
             . '<span class="res-completo" style="display:none">' . Html::encode($textoCompleto) . ' '
             . '<a href="javascript:void(0)" class="res-toggle">ver menos</a></span>'
             . '</span>';
    },
    'filter' => Select2::widget([
        'model'         => $searchModel,
        'attribute'     => 'productos_ids',
        'data'          => \yii\helpers\ArrayHelper::map(
            \app\models\Productos::find()->orderBy('titulo')->all(),
            'idproducto', 'titulo'
        ),
        'options'       => [
            'placeholder' => 'Filtrar por examen...',
           
            'multiple'    => true, // <-- clave: permite seleccionar varios
        ],
        'pluginOptions' => ['allowClear' => true,  'class' => 'orogando',],
    ]),
],

        

[
    'attribute'     => 'fecha_registro',
    'header'        => $searchModel->fecha_registro
        ? 'Fecha: ' . date('d/m/Y', strtotime($searchModel->fecha_registro))
        : 'Fecha',
    'headerOptions' => ['style' => 'min-width:150px; white-space:nowrap'],
    'value'         => fn($m) => date('d/m/Y H:i', strtotime($m->fecha_registro)),
    'filter'        => DatePicker::widget([
        'model'         => $searchModel,
        'attribute'     => 'fecha_registro',
        'value'         => $searchModel->fecha_registro,
        'language'      => 'es',
        'options'       => [
            'placeholder' => 'dd/mm/aaaa',
            'style'       => 'width:110px',
        ],
        'pluginOptions' => [
            'autoclose' => true,
            'format'    => 'yyyy-mm-dd',
            'todayBtn'  => 'linked',
        ],
    ]),
],


       
       
        
        [
    'header' => 'Desc.',
    'format' => 'raw',
    'value'  => function($m) {
        if ($m->esCotizacion()) return '<span class="text-muted">—</span>';
        if (($m->descuento ?? 0) > 0)
            return '<span class="text-warning">'.number_format($m->descuento, 2).'</span>';
        return number_format(0, 2);
    },
],
/*[
    'header' => 'A pagar',
    'format' => 'raw',
    'value'  => function($m) {
        if ($m->esCotizacion()) return '<span class="text-muted">—</span>';
        return number_format($m->monto_total - ($m->descuento ?? 0), 2);
    },
],  */

[
            'header' => 'Total',
            'value'  => fn($m) => number_format($m->monto_total, 2),
        ],

        
        [
            'header' => 'Saldo',
            'format' => 'raw',
            'value'  => function($m) {
                if ($m->esCotizacion()) return '<span class="text-muted">—</span>';
                if ($m->saldo > 0)
                    return '<span class="text-danger"><strong>'.number_format($m->saldo,2).'</strong></span>';
                return '<span class="text-success">0.00</span>';
            },
        ],



        [
    'attribute'      => 'monto_efectivo',
    'header'         => 'Efectivo',
    'headerOptions'  => ['style' => 'min-width:110px; white-space:nowrap'],
    'contentOptions' => ['style' => 'text-align:right; white-space:nowrap'],
    'format'         => 'raw',
    'value'          => function($m) {
        if ($m->esCotizacion()) return '<span class="text-muted">—</span>';
        return $m->monto_efectivo > 0
            ? number_format($m->monto_efectivo, 2)
            : '<span class="text-muted">—</span>';
    },
    'filter' => Select2::widget([
        'model'         => $searchModel,
        'attribute'     => 'con_efectivo',
        'data'          => ['1' => 'Sí', '0' => 'No'],
        'options'       => ['placeholder' => 'Todos'],
        'pluginOptions' => ['allowClear' => true],
    ]),
],
[
    'attribute'      => 'monto_qr',
    'header'         => 'QR',
    'headerOptions'  => ['style' => 'min-width:110px; white-space:nowrap'],
    'contentOptions' => ['style' => 'text-align:right; white-space:nowrap'],
    'format'         => 'raw',
    'value'          => function($m) {
        if ($m->esCotizacion()) return '<span class="text-muted">—</span>';
        return $m->monto_qr > 0
            ? number_format($m->monto_qr, 2)
            : '<span class="text-muted">—</span>';
    },
    'filter' => Select2::widget([
        'model'         => $searchModel,
        'attribute'     => 'con_qr',
        'data'          => ['1' => 'Sí', '0' => 'No'],
        'options'       => ['placeholder' => 'Todos'],
        'pluginOptions' => ['allowClear' => true],
    ]),
],

        

 [
            'attribute' => 'estado_pago',
            'format'    => 'raw',
            'value'     => function($m) {
                $cls = match($m->estado_pago) {
                    'pagado'     => 'success',
                    'pendiente'  => 'warning',
                    'cotizacion' => 'default',
                    default      => 'default',
                };
                return '<span class="label label-'.$cls.'">'.$m->estadoPagoLabel.'</span>';
            },
            'filter' => Select2::widget([
                'model'         => $searchModel,
                'attribute'     => 'estado_pago',
                'data'          => \app\models\Ordenes::getEstadosPago(),
                'options'       => ['placeholder' => 'Estado'],
                'pluginOptions' => ['allowClear' => true],
            ]),
        ],

        
     [
    'class'    => 'yii\grid\ActionColumn',
    'header'   => 'Op.',
    'template' => '{acciones}',
    'buttons'  => [
        'acciones' => function($url, $m) {
            $ruta = $m->esCotizacion() ? ['ver-cotizacion', 'id' => $m->idorden] : ['view', 'id' => $m->idorden];

            // Mismo criterio que PermisosService::tieneRol('administrador'),
            // pero sin llamar a verificarAdministrador() acá — esa función
            // redirige y corta la ejecución si falla, no sirve para armar
            // un simple booleano dentro de una vista.
            $esAdmin = !Yii::$app->user->isGuest
                && Yii::$app->user->identity->rol
                && Yii::$app->user->identity->rol->alias === 'administrador';

            $itemVer = Html::a('<i class="fa fa-eye"></i> Ver', Url::to($ruta), ['tabindex' => '-1']);

            $itemEditar = '';
            if (!$m->esCotizacion() && $esAdmin) {
                $itemEditar = Html::a('<i class="fa fa-pencil"></i> Editar', Url::to(['update', 'id' => $m->idorden]), ['tabindex' => '-1']);
            }

            $itemResultados = '';
            if (!$m->esCotizacion()) {
                $itemResultados = Html::a('<i class="fa fa-flask"></i> Cargar Resultados', Url::to(['resultados', 'id' => $m->idorden]), ['tabindex' => '-1']);
            }

            $deleteUrl = Url::to(['delete', 'id' => $m->idorden]);
            $itemDelete = Html::a('<i class="fa fa-trash"></i> Eliminar', '#', [
                'tabindex' => '-1',
                'onclick'  => "if(confirm('¿Eliminar?')){
                    fetch('{$deleteUrl}',{method:'POST',headers:{'X-CSRF-Token':document.querySelector('meta[name=csrf-token]').content}})
                    .then(r=>r.json()).then(d=>{if(d.exito)location.reload();else alert(d.mensaje)});
                }return false;",
            ]);

            return '<div class="dropdown">
                        <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                            <i class="fa fa-list"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>'.$itemVer.'</li>'
                            .($itemEditar ? '<li>'.$itemEditar.'</li>' : '').'
                            '.($itemResultados ? '<li>'.$itemResultados.'</li>' : '').'
                            <li role="separator" class="divider"></li>
                            <li>'.$itemDelete.'</li>
                        </ul>
                    </div>';
        },
    ],
],
    ],
]); ?>
</div>
</div>
</div>
</div>
</section> 

<style>
    .table-bordered>tbody>tr>td{
        padding: 5px;
        font-size: 12px;
    }

    .table-bordered>thead>tr>th, .table-bordered>thead>tr>td {
    font-size: 12px;
}

.select2-container--krajee-bs3 .select2-selection--single .select2-selection__placeholder {
    color: #999;
    font-size: 13px;
}
.table>thead>tr>td{ padding: 5px!important;}
.input-group-addon {
    padding: 6px 5px;
    font-size: 10px;
}
.form-control.krajee-datepicker { padding: 7px!important;}

.btn-group.reds .btn {
    padding: 4px 5px;
}
.box-body  { padding-left: 5px!important;}

/* Columna "Resultados": es la que más texto tiene (toda la lista de
   análisis de la orden), así que le bajo la letra bastante más que al
   resto y le pongo un ancho tope, para que no sea la que más estire
   la fila y la tabla entre completa. */
.col-resultados-texto {
    font-size: 11px;
    line-height: 1.35;
    display: inline-block;
    max-width: 320px;
}
.res-toggle {
    white-space: nowrap;
    font-weight: bold;
}
</style>

<?php
$this->registerJs("
    // Ver todo / ver menos en la columna Resultados — un solo listener
    // para toda la tabla, funciona para las filas que ya están y las
    // que se recarguen (Pjax, paginación, etc.)
    \$(document).on('click', '.res-toggle', function(e) {
        e.preventDefault();
        var wrap = \$(this).closest('.resultados-wrap');
        wrap.find('.res-corto, .res-completo').toggle();
    });
");
?>