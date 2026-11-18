<?php
use yii\helpers\Html;
?>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: arimo, sans-serif; font-size: 12px; color: #000; }
.seccion { margin-bottom: 25px; page-break-inside: avoid; }

.contenido-rico ul, .contenido-rico ol {
    margin-left: 20px;
    padding-left: 20px;
    margin-bottom: 6px;
    list-style-position: outside;
}
.contenido-rico ul { list-style-type: disc; }
.contenido-rico ol { list-style-type: decimal; }
.contenido-rico li { margin-bottom: 2px; display: list-item; }
</style>



<?php foreach ($secciones as $si => $sec): ?>
<div class="seccion" <?= $si > 0 ? 'style="margin-top:14px"' : '' ?>>

      <div style="text-align:center; font-weight:bold;     font-family:arimo; font-size:18px; text-transform:uppercase; margin-bottom:5px;">
                <?= Html::encode($sec->titulo) ?>
     </div>
            <div style=" border-top:1px solid black; margin-bottom:6px; width:100%"> </div>

    <?php if ($sec->mostrar_cabecera ?? 1): ?>
    <table width="100%" style="border-collapse:collapse; margin-bottom:4px;">
        <tr>
            <td width="35%" style="font-weight:bold; font-size:15px;     font-family:arimo; text-transform:uppercase; padding:0 0 2px 0;">EXAMEN</td>
            <td width="30%" style="font-weight:bold; font-size:15px;     font-family:arimo; text-transform:uppercase; text-align:center; padding:0 0 2px 0;">RESULTADO</td>
            <td width="35%" style="font-weight:bold; font-size:15px;      font-family:arimo; text-transform:uppercase; text-align:center; padding:0 0 2px 10px;">VALORES DE REFERENCIAS</td>
        </tr>
    </table>
    <div style=" border-top:1px dotted black; margin-bottom:6px; width:100%; margin-bottom:8px;"> </div>
    <?php endif; ?>

    <?php
        // 1. Agrupar TODOS los resultados (el "head" de un grupo puede no tener valor propio
        // y actuar solo como etiqueta, ej: "Medidas")
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
        $head        = $grupo[0];
        $followers   = array_slice($grupo, 1);
        $esSubtitulo = (bool)($head->parametro->es_subtitulo ?? false);

        $tieneAlgo = $esSubtitulo || !empty($head->valor);
        foreach ($followers as $f) {
            if (!empty($f->valor)) $tieneAlgo = true;
        }
        if (!$tieneAlgo) continue;
    ?>

        <?php if (empty($followers)):
            // ---- Grupo de 1 solo parámetro: igual que siempre (3 columnas) ----
            $res = $head;
            $esNegrita = (bool)($res->parametro->es_negrita ?? false);
            $esAlerta  = (bool)($res->es_alerta ?? false);
            $referencia = $res->parametro->referencia_texto ?? '';
            if ($referencia && !preg_match('/<[^>]+>/', $referencia)) {
                $referencia = nl2br(Html::encode($referencia));
            }

            // NUEVO: detectar categoría ecografías también en el caso de 1 solo parámetro
            $esEcografiaSolo = false;
            foreach ($res->parametro->productos as $prodRel) {
                if ($prodRel->categoria && $prodRel->categoria->alias === 'ecografias') {
                    $esEcografiaSolo = true;
                    break;
                }
            }
        ?>
        <table width="100%" style="border-collapse:collapse; margin-bottom:4px;">
            <tr>
                <td width="35%" style="padding:<?= ($esSubtitulo) ? '10px 0 2px 0' : '2px 0 2px 2px' ?>; font-size:14px;  font-family:arimo; vertical-align:top;">
                    <?php if ($esSubtitulo): ?>
                        <strong><em><?= Html::encode($res->parametro->nombre) ?></em></strong>
                    <?php elseif ($esNegrita): ?>
                        <strong><?= Html::encode($res->parametro->nombre) ?></strong>
                    <?php else: ?>
                        <?= Html::encode($res->parametro->nombre) ?>
                    <?php endif; ?>
                </td>
                <td width="30%" style="padding:2px 0; font-weight:bold; font-size:14px; vertical-align:top; font-family:arimo; <?= $esEcografiaSolo ? 'text-align:left; padding-left:0px;' : 'text-align:center;' ?> <?= $esAlerta ? 'color:#ff1800;' : '' ?>">
                    <?php if (!$esSubtitulo && $res->valor): ?>
                        <?= Html::encode($res->valor) ?>
                        <?php if ($res->parametro->unidad): ?>
                            <em><?= Html::encode($res->parametro->unidad) ?></em>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td width="35%" style="padding:2px 0 2px 10px; font-size:11.5px; color:#333; line-height:1.4; font-family:arimo; vertical-align:top; text-align:justify;">
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

            $piezas = [];
            if (!empty($head->valor)) {
                $val = Html::encode($head->valor);
                if (!empty($head->es_alerta)) $val = '<span style="color:#ff1800;">' . $val . '</span>';
                if ($head->parametro->unidad) $val .= ' <em>' . Html::encode($head->parametro->unidad) . '</em>';
                $piezas[] = $val;
            }
            foreach ($followers as $f) {
                if (empty($f->valor)) continue;
                $nomF = trim($f->parametro->nombre ?? '');
                $esNegritaF = (bool)($f->parametro->es_negrita ?? false);
                $val = Html::encode($f->valor);
                if ($esNegritaF) $val = '<strong>' . $val . '</strong>';
                if (!empty($f->es_alerta)) $val = '<span style="color:#ff1800;">' . $val . '</span>';
                if ($f->parametro->unidad) $val .= ' <em>' . Html::encode($f->parametro->unidad) . '</em>';
                $piezas[] = $nomF !== '' ? Html::encode($nomF) . ': ' . $val : $val;
            }
            $lineaResultado = implode('&nbsp; ', $piezas);
        ?>
        <table width="100%" style="border-collapse:collapse; margin-bottom:4px;">
            <tr>
                <td width="35%" style="padding:2px 0 2px 2px; font-size:14px; font-family:arimo; vertical-align:top;">
                    <?php if ($nombreHead !== ''): ?>
                        <?php if ($esNegritaHead): ?><strong><?= Html::encode($nombreHead) ?>:</strong>
                        <?php else: ?><?= Html::encode($nombreHead) ?>:
                        <?php endif; ?>
                    <?php else: ?>&nbsp;<?php endif; ?>
                </td>
                <td width="<?= $esEcografia ? '65%' : '30%' ?>" style="padding:2px 0; font-size:14px; font-family:arimo; vertical-align:top;">
                    <?= $lineaResultado ?: '&nbsp;' ?>
                </td>
                <?php if (!$esEcografia): ?>
                <td width="35%" style="padding:2px 0 2px 10px; font-size:11.5px; color:#333; line-height:1.4; font-family:arimo; vertical-align:top; text-align:justify;">
                    &nbsp;
                </td>
                <?php endif; ?>
            </tr>
        </table>
        <?php endif; ?>

    <?php endforeach; ?>

    <?php if (!empty($sec->observaciones)): ?>
    <div style="margin-top:15px; border-top:1px solid #333; padding-top:8px;">
        <div style="font-weight:bold; font-size:13px; font-family:arimo; text-transform:uppercase; margin-bottom:1px;">
            Observaciones
        </div>
        <div class="contenido-rico" style="font-size:12px; line-height:1.5; font-family:arimo;">
            <?= $sec->observaciones ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($sec->conclusion)): ?>
    <div style="margin-top:15px; border-top:1px solid #333; padding-top:8px;">
        <div style="font-weight:bold; font-size:13px; font-family:arimo; text-transform:uppercase; margin-bottom:1px;">
            Conclusión
        </div>
        <div class="contenido-rico" style="font-size:12px; line-height:1.5; font-family:arimo;">
            <?= $sec->conclusion ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>