<?php
use yii\helpers\Html;
?>
<table width="100%" style="border-collapse:collapse; font-family:arimo; color:#555; font-size:11px;">
    <tr>
        <td width="100%" style="padding:0; margin-bottom:10px; text-align:center;">

        <?php
            // NUEVO: sello/firma de quien cargó y validó los resultados
            // (distinto del "Usuario" de arriba, que es quién registró
            // la orden/facturación). Si no hay nadie asignado todavía o
            // esa persona no subió sello, esta sección simplemente no
            // se muestra — no se fuerza nada.
            $usuarioResultados = $model->usuarioResultados ?? null;
            ?>
            <?php if ($usuarioResultados): ?>
            <table width="100%" style="border-collapse:collapse; margin-top:2px; margin-bottom:6px;">
                <tr>
                    <td width="100%" style="text-align:right; padding:0 2px;">
                        <?php if (!empty($usuarioResultados->foto_sello)): ?>
                            <img src="<?= Yii::$app->request->baseUrl . $usuarioResultados->foto_sello ?>"
                                 style="max-height:105px; max-width:200px;">
                            <br>
                        <?php endif; ?>
                        
                    </td>
                </tr>
            </table>
            <?php endif; ?>

            <div style="text-align:center; font-size:12px; margin-bottom:10px;">
                <strong style="color:#0071c0;">Sucursal Bajío:</strong>
                <span>Zona el Bajío (Frente al Hospital del Bajío del Oriente) &nbsp; Telf. 627 37369 – 780 83843</span>
            </div>

            <table width="100%" style="border-collapse:collapse; font-size:11px; color:#555; margin-top:5px; margin-bottom:5px">
                <tr>
                    <td width="33%" style="text-align:left; padding:0 4px;">
                        Fecha: <?= date('d/m/Y H:i:s') ?>
                    </td>
                    <td width="34%" style="text-align:center; padding:0 4px; color:#0071c0; font-weight:bold;">
                        ATENCIÓN DOMINGOS Y FERIADOS
                    </td>
                    <td width="33%" style="text-align:right; padding:0 4px;">
                        Usuario: <?= Html::encode($model->usuarioRegistro->usuario ?? '—') ?>
                    </td>
                </tr>
            </table>

            
        </td>
    </tr>
    <tr>
        <td width="100%" style="background:#0071c0; height:5px; padding:0; line-height:0; font-size:0;">&nbsp;</td>
    </tr>
</table>