<?php
use yii\helpers\Html;

$this->title = 'RESULTADO ORDEN N° ' . ($model->numero_referencia ?: $model->idorden);

$fechaToma = '';
if (!empty($secciones) && $secciones[0]->fecha_toma_muestra) {
    $fechaToma = date('d/m/Y H:i', strtotime($secciones[0]->fecha_toma_muestra));
}


$paraPdf = $paraPdf ?? false;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= Html::encode($this->title) ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }

.print-wrapper {
    width: 100%;
    border-collapse: collapse;
}
.print-wrapper thead td { padding-bottom: 5px; }
.print-wrapper tfoot td { padding-top: 8px; }

/* Antes: position:absolute sacaba el footer del flujo de la tabla y
   no reservaba espacio, por eso el contenido lo podía pisar. Un
   <tfoot> normal (display:table-footer-group, su valor por defecto)
   se repite solo al pie de cada hoja Y reserva su espacio, igual que
   ya hace el <thead> de arriba. */
/* Antes: position:absolute lo sacaba del flujo y solo se calculaba
   una vez, así que en hojas largas el contenido lo podía pisar.
   Ahora: en pantalla (sin imprimir) se queda en flujo normal, pero
   dentro de @media print más abajo lo pasamos a position:fixed, que
   el navegador SÍ repite pegado al borde inferior en cada hoja
   impresa automáticamente — sin perder la reserva de espacio, porque
   esa la da el margin-bottom de @page (ver más abajo). */
tfoot {
    width: 100%;
}
.print-wrapper tbody td { vertical-align: top; }

.tabla-orden-box {
    border: 3px double #333;
    padding: 4px 10px;
    text-align: center; font-size: 11px; vertical-align: top;
    border-radius: 4px;
}

.tabla-paciente {
    width: 100%;
    border-collapse: separate;
    border: 3px double #333;
    border-radius: 6px;
    margin-bottom: 8px;
    font-size: 12px;
}
.tabla-paciente td { padding: 3px 10px; }

.seccion { margin-bottom: 25px; page-break-inside: avoid; }
.seccion-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}
.seccion-titulo {
    font-weight: bold;
    font-size: 15px;
    text-transform: uppercase;
}
.valores-header {
    font-weight: bold;
    font-size: 10px;
    text-transform: uppercase;
    white-space: nowrap;
}
.seccion-linea {
    border: none;
    border-top: 1px solid #333;
    margin: 2px 0 8px;
}

/* NUEVO: cada parámetro individual (una fila EXAMEN/RESULTADO/REFERENCIA)
   tampoco se puede cortar a la mitad. Si no entra completo en lo que
   queda de la hoja, salta entero a la siguiente — sin pisar el footer
   ni partirse entre dos páginas. Esto aplica DENTRO de una sección,
   independiente de que la sección entera también salte cuando
   corresponde. */
.fila-parametro { page-break-inside: avoid; }

.parametro { margin-bottom: 8px; }
.parametro-nombre {
    font-size: 12px;
    margin-bottom: 1px;
}
.parametro-fila {
    width: 100%;
    border-collapse: collapse;
}
.parametro-fila td { vertical-align: top; padding: 0; }
.col-resultado-label {
    width: 90px;
    padding-left: 12px;
}
.col-valor {
    width: 140px;
    font-weight: bold;
    font-style: italic;
    font-size: 12px;
}
.col-referencia {
    font-size: 10.5px;
    color: #333;
    line-height: 1.4;
}
.col-referencia .ref-metodo {
    font-weight: bold;
    font-style: italic;
}

.fecha-muestra { font-size: 11px; color: #555; margin-bottom: 6px; }

.footer-linea {
    border: none;
    border-top: 3px double #333;
    margin: 0 0 6px;
}

@media print {
    @page { 
        size: letter; 
        margin-top: 0.3cm;
        margin-bottom: 3cm;
        margin-left: 1.5cm;
        margin-right: 1.5cm;
    }
    .no-print { display: none !important; }
    .seccion { page-break-inside: avoid; }
    .fila-parametro { page-break-inside: avoid; }

    /* Acá está la clave: fixed (no absolute) hace que el navegador
       repita el footer pegado al borde inferior en CADA hoja impresa,
       sea cual sea el contenido — y como @page ya reserva 3cm abajo,
       el texto de arriba nunca llega a esa franja, así que no se
       superponen. Si algún día el footer queda muy alto o muy bajo,
       el margin-bottom de @page es el número a ajustar. */
    tfoot {
        position: fixed;
        bottom: 0;
        left: 0;
    }
}
.btn-print {
    display: block; margin: 20px auto; padding: 10px 30px;
    background: #3c8dbc; color: white; border: none;
    border-radius: 4px; font-size: 16px; cursor: pointer;
}

.encabezado{
    width:100%;
    height:32px;
    overflow:hidden;
}
@media print {
    .fila-separador-producto { display: none !important; }
}

.franja{
   position: relative;
    margin-left: 130px;
    height: 15px;
    background: #0071c0;
    WIDTH: 60%;
    TEXT-ALIGN: RIGHT;
    float: right;
}

.franja:before{
    content: "";
    position: absolute;
    left: -15px;
    width: 36px;
    height: 15px;
    background: #0071c0;
    transform: skewX(45deg);
    transform-origin: right;
}
</style>
</head>
<body>

<?php if (!$paraPdf): ?>
<?php endif; ?>




<table class="print-wrapper">

<thead>
    <tr>
        <td style="padding:0;">

       <div class="encabezado">
    <div class="franja"></div>
</div>

            <table width="100%" style="border-collapse:collapse; margin-top:8px; margin-bottom:0px;">
                <tr>
                    <td width="50%" style="vertical-align:middle;">
                        <img src="/LABORATORIOOSINAGA/assets_b/images/logo.jpg" style="max-height:90px; max-width:320px">
                    </td>

                    <td width="50%" style="vertical-align:bottom; text-align:right;">
                        <div style="font-size:15px; color:#222; margin-bottom:12px;">
                            Registro Sedes: <strong>78/RS/941/N1</strong>
                        </div>
                    </td>
                </tr>
            </table>

<table width="100%" style="border-collapse:collapse; margin-bottom:8px;">
    <tr>
        <td width="80%" style="vertical-align:top; padding-right:6px;">
            <table width="100%" style="border: 5px double #333333;
    border-radius: 10px;
    margin-bottom: 5px;
    font-size: 14px;
    border-collapse: separate;">
                <tr>
                    <td width="70%" style="padding:3px 3px; border-right:0px solid #333; border-bottom:0px solid #333;">
                        <strong>PACIENTE:</strong> &nbsp; <?= Html::encode(strtoupper($model->paciente_nombre)) ?>
                    </td>
                    <td width="30%" style="padding:3px 3px; border-bottom:0px solid #333;">
                        <strong>EDAD:</strong> <?= Html::encode($model->paciente_edad ?? '—') ?>
                    </td>
                </tr>
                


                <?php if ($model->doctor): ?>
                <tr>
                    <td  width="70%" style="padding:3px 3px; border-bottom:0px solid #333;">
                        <strong>DOCTOR:</strong> &nbsp;&nbsp;&nbsp;&nbsp; <?= Html::encode($model->doctor ? strtoupper($model->doctor->nombre) : 'S/N') ?>
                    </td>
                        <td width="30%"  style="padding:3px 3px;">
                        <strong>TELF.:</strong> <?= Html::encode($model->paciente_telefono ?? '—') ?>
                    </td>
                </tr>
                <?php endif; ?>


                <tr>
                    <td colspan="2" style="padding:3px 3px; border-right:0px solid #333;">
                        <strong>CENTRO:</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SUCURSAL BAJÍO
                    </td>
                   

                </tr>
            </table>
        </td>

        <td width="20%" style="vertical-align:top;">
            <table width="100%" style="border: 5px double #333333;
    border-radius: 10px;
    margin-bottom: 5px;
    font-size: 14px;
    border-collapse: separate;">
                <tr>
                    <td style="padding:4px 5px; text-align:center; border-bottom:1px solid #333; font-weight:bold; font-size:14px;">
                        ORDEN
                    </td>
                </tr>
                <tr>
                    <td style="padding:2px 5; text-align:center; font-weight:bold; font-size:14px;">
                        <?= Html::encode($model->numero_referencia ?: $model->idorden) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding:2px 5px; text-align:center; border-top:0px solid #333; font-size:14px;">
    <?= $fechaToma ?: date('d/m/Y H:i', strtotime($model->fecha_registro)) ?>
</td>
                </tr>
            </table>
        </td>
    </tr>
</table>


           

        </td>
    </tr>
</thead>

<tfoot>
    <tr>
        <td style="padding:0; padding-top: 40px;">

         

            <div style="text-align:center; font-size:12px; margin-bottom:5px;">
              <strong  style="color:#0071c0; "> Sucursal Bajío: </strong>   <span>Zona el Bajío (Frente al Hospital del Bajío del Oriente) &nbsp; Telf. 75075573</span>
            </div>

            <table width="100%" style="border-collapse:collapse; font-size:11px; color:#555;">
                <tr>
                    <td width="33%" style="text-align:left; padding:0 4px; color: #3e3c3c;">
                        Fecha: <?= date('d/m/Y H:i:s') ?>
                    </td>
                    <td width="34%" style="text-align:center; padding:0 4px; color:#0071c0; font-weight:bold;">
                        ATENCIÓN DOMINGOS Y FERIADOS
                    </td>
                    <td width="33%" style="text-align:right; padding:0 4px; color: #3e3c3c;">
                        Usuario: <?= Html::encode($model->usuarioRegistro->usuario ?? '—') ?>
                    </td>
                </tr>
            </table>

            <table width="100%" style="border-collapse:collapse; margin-top:8px; margin-bottom:4px; line-height:0; font-size:0;">
                <tr>
                    <td style="background:#0071c0; height:6px; padding:0; border:none;"></td>
                </tr>
            </table>

        </td>
    </tr>
</tfoot>

    <tbody>
<tr>
    <td>
        <?php foreach ($secciones as $si => $sec): ?>
        <div class="seccion" <?= $si > 0 ? 'style="margin-top:14px"' : '' ?>>

            <div style="text-align:center; font-weight:bold; font-size:18px; text-transform:uppercase; margin-bottom:3px;">
                <?= Html::encode($sec->titulo) ?>
            </div>
            <hr style="border:none; border-top:1px solid #333; margin-bottom:6px;">

            <?php if ($sec->mostrar_cabecera ?? 1): ?>
<table width="100%" style="border-collapse:collapse; margin-bottom:4px;">
    <tr>
        <td width="35%" style="font-weight:bold; font-size:15px; text-transform:uppercase; padding:0 0 2px 0;">EXAMEN</td>
        <td width="30%" style="font-weight:bold; font-size:15px; text-transform:uppercase; text-align:center; padding:0 0 2px 0;">RESULTADO</td>
        <td width="35%" style="font-weight:bold; font-size:15px; text-transform:uppercase; text-align:center; padding:0 0 2px 10px;">VALORES DE REFERENCIAS</td>
    </tr>
</table>
         
<svg width="100%" height="2" style="display:block;">
    <line x1="0" y1="1" x2="100%" y2="1" stroke="#333" stroke-width="1" stroke-dasharray="3,2"/>
</svg>
<hr style="border:none; border-top: 0.50px dashed #333; margin-bottom:8px;">

<?php endif; ?>

<!-- Resultados -->
<?php
    // 1. Agrupar TODOS los resultados (sin filtrar vacíos todavía, porque el "head" de un grupo
    // puede no tener valor propio y actuar solo como etiqueta, ej: "Medidas")
    $grupos = [];
    foreach ($sec->resultados as $res) {
        if (!$res->parametro) continue;
        $juntar = (bool)($res->parametro->salto_linea ?? false);
        if ($juntar && !empty($grupos)) {
            $grupos[count($grupos) - 1][] = $res;
        } else {
            $grupos[] = [$res];
        }
    }
?>

<?php foreach ($grupos as $grupo):
    $head       = $grupo[0];
    $followers  = array_slice($grupo, 1);
    $esSubtitulo = (bool)($head->parametro->es_subtitulo ?? false);

    // ¿Hay algo real que mostrar en este grupo? (el head con valor, subtítulo, o algún follower con valor)
    $tieneAlgo = $esSubtitulo || !empty($head->valor);
    foreach ($followers as $f) {
        if (!empty($f->valor)) $tieneAlgo = true;
    }
    if (!$tieneAlgo) continue; // nada que mostrar, se salta el grupo entero
?>

   <?php if (empty($followers)):
    // ---- Grupo de 1 solo parámetro: igual que siempre (3 columnas) ----
    $res = $head;
    $esNegrita = (bool)($res->parametro->es_negrita ?? false);
    $referencia = $res->parametro->referencia_texto ?? '';
    if ($referencia && !preg_match('/<[^>]+>/', $referencia)) {
        $referencia = nl2br(Html::encode($referencia));
    }

    // NUEVO: detectar categoría ecografías también acá
    $esEcografiaSolo = false;
    foreach ($res->parametro->productos as $prodRel) {
        if ($prodRel->categoria && $prodRel->categoria->alias === 'ecografias') {
            $esEcografiaSolo = true;
            break;
        }
    }
?>
<table width="100%" class="fila-parametro" style="border-collapse:collapse; margin-bottom:4px;">
    <tr>
        <td width="35%" style="padding:<?= ($esSubtitulo) ? '10px 0 2px 0' : '2px 0 2px 2px' ?>; font-size:14px; vertical-align:top;">
            <?php if ($esSubtitulo): ?>
                <strong><em><?= Html::encode($res->parametro->nombre) ?></em></strong>
            <?php elseif ($esNegrita): ?>
                <strong><?= Html::encode($res->parametro->nombre) ?></strong>
            <?php else: ?>
                <?= Html::encode($res->parametro->nombre) ?>
            <?php endif; ?>
        </td>
        <td width="30%" style="padding:2px 0; font-weight:bold; font-size:14px; vertical-align:top; <?= $esEcografiaSolo ? 'text-align:left; padding-left:0px;' : 'text-align:center;' ?>">
            <?php if (!$esSubtitulo && $res->valor): ?>
                <?= Html::encode($res->valor) ?>
                <?php if ($res->parametro->unidad): ?>
                    <em><?= Html::encode($res->parametro->unidad) ?></em>
                <?php endif; ?>
            <?php endif; ?>
        </td>
        <td width="35%" style="padding:2px 0 2px 10px; font-size:11.5px; color:#333; line-height:1.4; vertical-align:top; text-align:justify; word-wrap:break-word; overflow-wrap:break-word;">
            <?php if (!$esSubtitulo): ?>
                <?= $referencia ?: '&nbsp;' ?>
            <?php else: ?>
                &nbsp;
            <?php endif; ?>
        </td>
    </tr>
</table>

    <?php else:
        // ---- Grupo con juntados: "head" es la etiqueta fija, los followers van del lado del resultado ----
        $esEcografia = false;
        foreach ($head->parametro->productos as $prodRel) {
            if ($prodRel->categoria && $prodRel->categoria->alias === 'ecografias') {
                $esEcografia = true;
                break;
            }
        }

        $esNegritaHead = (bool)($head->parametro->es_negrita ?? false);
        $nombreHead = trim($head->parametro->nombre ?? '');

        // Armar las piezas del lado "resultado": primero el valor del head (si tiene), después cada follower con valor
        $piezas = [];
        if (!empty($head->valor)) {
            $val = Html::encode($head->valor);
            if ($head->parametro->unidad) $val .= ' <em>' . Html::encode($head->parametro->unidad) . '</em>';
            $piezas[] = $val;
        }
        foreach ($followers as $f) {
            if (empty($f->valor)) continue;
            $nomF = trim($f->parametro->nombre ?? '');
            $esNegritaF = (bool)($f->parametro->es_negrita ?? false);
            $val = Html::encode($f->valor);
            if ($esNegritaF) $val = '<strong>' . $val . '</strong>';
            if ($f->parametro->unidad) $val .= ' <em>' . Html::encode($f->parametro->unidad) . '</em>';
            $piezas[] = $nomF !== '' ? Html::encode($nomF) . ': ' . $val : $val;
        }
        $lineaResultado = implode('&nbsp; ', $piezas);
    ?>
    <table width="100%" class="fila-parametro" style="border-collapse:collapse; margin-bottom:4px;">
        <tr>
            <td width="35%" style="padding:2px 0 2px 2px; font-size:14px; vertical-align:top;">
                <?php if ($nombreHead !== ''): ?>
                    <?php if ($esNegritaHead): ?><strong><?= Html::encode($nombreHead) ?>:</strong>
                    <?php else: ?><?= Html::encode($nombreHead) ?>:
                    <?php endif; ?>
                <?php else: ?>&nbsp;<?php endif; ?>
            </td>
            <td width="<?= $esEcografia ? '65%' : '30%' ?>" style="padding:2px 0; font-size:14px; vertical-align:top;">
                <?= $lineaResultado ?: '&nbsp;' ?>
            </td>
            <?php if (!$esEcografia): ?>
            <td width="35%" style="padding:2px 0 2px 10px; font-size:11.5px; color:#333; line-height:1.4; vertical-align:top; text-align:justify; word-wrap:break-word; overflow-wrap:break-word;">
                &nbsp;
            </td>
            <?php endif; ?>
        </tr>
    </table>
    <?php endif; ?>

<?php endforeach; ?>
        </div>
        <?php endforeach; ?>



  <?php if (!empty($model->observaciones)): ?>
<div style="margin-top:20px; border-top:1px solid #333; padding-top:10px; page-break-inside:avoid;">
    <div style="font-weight:bold; font-size:14px; text-transform:uppercase; margin-bottom:6px;">
        Observaciones
    </div>
    <div class="contenido-rico" style="font-size:13px; line-height:1.6;">
        <?= $model->observaciones ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($model->conclusion)): ?>
<div style="margin-top:20px; border-top:1px solid #333; padding-top:10px; page-break-inside:avoid;">
    <div style="font-weight:bold; font-size:14px; text-transform:uppercase; margin-bottom:6px;">
        Conclusión
    </div>
    <div class="contenido-rico" style="font-size:13px; line-height:1.6;">
        <?= $model->conclusion ?>
    </div>
</div>
<?php endif; ?>

        
    </td>
</tr>
</tbody>
</table>

<?php if (!$paraPdf): ?>
<script>
    window.addEventListener('load', function() {
        window.print();
    });
</script>
<?php endif; ?>

</body>
</html>


<style>
    .contenido-rico ul, .contenido-rico ol {
    margin-left: 20px;
    padding-left: 20px;
    margin-bottom: 6px;
    list-style-position: outside;
}
.contenido-rico ul {
    list-style-type: disc;
}
.contenido-rico ol {
    list-style-type: decimal;
}
.contenido-rico li {
    margin-bottom: 2px;
    display: list-item;
}
</style>