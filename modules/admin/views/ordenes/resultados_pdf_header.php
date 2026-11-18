<?php
use yii\helpers\Html;
$fechaToma = '';
if (!empty($secciones) && $secciones[0]->fecha_toma_muestra) {
    $fechaToma = date('d/m/Y H:i', strtotime($secciones[0]->fecha_toma_muestra));
}
?>




<table width="100%" style="border-collapse:collapse; font-family: arimo, sans-serif; color:#000; margin-top:-5px;">

    <!-- Fila 1: la franja -->
    <tr>
        <td colspan="2" style="text-align:right; ">
            <img src="/LABORATORIOOSINAGA/assets_b/images/franja.jpg" style="width:63%; height:15px;">
        </td>
    </tr>

    <tr>
        <td colspan="2" style="height:12.5px; padding:0; line-height:0; font-size:0;">&nbsp;</td>
    </tr>

    <!-- Fila 2: logo + registro sedes -->
    <tr>
        <td width="50%" style="vertical-align:middle; padding-top:10px;">
            <img src="/LABORATORIOOSINAGA/assets_b/images/logo.jpg" style="max-height:90px; max-width:320px">
        </td>
        <td width="50%" style="vertical-align:bottom; font-family:arimo; text-align:right;">
            <div style="font-size:15px; color:#222;">
                Registro Sedes: <strong>78/RS/941/N1</strong>

               <br>          
                           
            </div>
           <div colspan="2" style="height:9px; padding:0; line-height:0; font-size:0;">&nbsp;</div>


        </td>
    </tr>
</table>

<!-- Bloque PACIENTE + ORDEN, 100% divs, sin tablas -->
<div style="width:100%; overflow:hidden; margin-bottom:14px; margin-top: 8px">

    <div style="
        float:left;
        width:76%;
        border:5px double #333;
        border-radius:10px;
        padding:3px 6px;
        font-family:arimo;
        font-size:14px;
        box-sizing:border-box;
    ">
        <div style="width: 100%;  float:left;">
            <div style="width: 70%;  float:left;"><strong>PACIENTE:</strong> &nbsp; <?= Html::encode(strtoupper($model->paciente_nombre)) ?> </div>
            <div style="width: 30%;  float:left;">
                <strong>EDAD:</strong> <?= Html::encode($model->paciente_edad ?? '—') ?>
        </div>
        </div>

    <div style="width: 100%;  float:left; margin-top:5px;">
            <div style="width: 70%;  float:left;"> <strong>DOCTOR:</strong> &nbsp;&nbsp;&nbsp;&nbsp; <?= Html::encode($model->doctor ? strtoupper($model->doctor->nombre) : 'S/N') ?> </div>
            <div style="width: 30%;  float:left;">
                 <strong>TELF.:</strong> <?= Html::encode($model->paciente_telefono ?? '—') ?>
        </div>
        </div>



            <div style="width: 100%;  float:left; margin-top:5px;">

            <strong>CENTRO:</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SUCURSAL BAJÍO
        </div>
    </div>

    <div style="
        float:right;
        width:17%;
        border:5px double #333;
        border-radius:10px;
        text-align:center;
          padding:3px 6px;
        font-family:arimo;
        font-size:14px;
        box-sizing:border-box;
    ">
        <div style="padding:3px 5px; text-align:center; border-bottom:1px solid #333; font-weight:bold; font-size:14px;     display: block;"><strong style="">ORDEN </strong> <br></div>
        <div style="display: block; padding:2px 5px; font-weight:bold; font-size:14px;"><?= Html::encode($model->numero_referencia ?: $model->idorden) ?> <br></div>
        <div style="display: block; padding:0px 4px;  font-size:14px;"><?= $fechaToma ?: date('d/m/Y H:i', strtotime($model->fecha_registro)) ?> </div>
    </div>

</div>

<div style="clear:both;"></div>