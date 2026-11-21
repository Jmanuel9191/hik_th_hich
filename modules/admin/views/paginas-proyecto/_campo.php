<?php

/**
 * Un campo del formulario de bloque, según su tipo (ver app\models\bloques\Bloque):
 * texto, parrafo, html (editor simple), enlace, opciones, casilla, imagen (con vista previa y "quitar"),
 * numero, lineas, tabla (editor de grilla + pegar desde Excel), elegir (casillas con miniaturas de las pestañas
 * del proyecto) y lista (filas que se agregan, ordenan y borran; "Subir varias fotos"). JS: huk-pagina-proyecto.js.
 *
 * @var yii\web\View $this
 * @var string $nombre
 * @var array $c       definición del campo
 * @var mixed $valor
 * @var string $prefijo nombre del input: Bloque[fotos][3][imagen]
 * @var string $ruta    para las fotos: fotos.3.imagen  (el archivo va en archivos[<ruta>])
 * @var app\components\MediosProyecto $medios
 * @var array|null $fuentes  lo que se puede elegir: [fotos|videos|documentos => [['id','titulo','miniatura','icono','nota'], …]]
 *                           (null en una plantilla: no hay proyecto todavía)
 */

use yii\helpers\Html;
use yii\helpers\Url;
use app\widgets\ZonaArchivo;

$id = 'pe-' . preg_replace('/[^a-z0-9_]+/i', '-', $ruta);
$etiqueta = Html::encode($c['etiqueta'] ?? $nombre) . (!empty($c['requerido']) ? ' <span class="text-danger">*</span>' : '');
$ayuda = isset($c['ayuda']) ? '<p class="help-block">' . Html::encode($c['ayuda']) . '</p>' : '';
$fuentes = $fuentes ?? null;
// 'si' => 'mostrar:elegidas,elegidos': el campo se ve solo con esa opción marcada (huk-pagina-proyecto.js)
$si = isset($c['si']) ? ' data-pe-si="' . Html::encode($c['si']) . '"' : '';
$comunes = ['id' => $id, 'class' => 'form-control', 'placeholder' => $c['placeholder'] ?? null, 'maxlength' => $c['max'] ?? null];
?>
<?php switch ($c['tipo']):
    case 'texto':
    case 'enlace': ?>
        <div class="form-group" data-campo="<?= Html::encode($nombre) ?>"<?= $si ?>>
            <label class="control-label" for="<?= $id ?>"><?= $etiqueta ?></label>
            <?= Html::textInput($prefijo, $valor, $comunes + ($c['tipo'] === 'enlace' ? ['inputmode' => 'url'] : [])) ?>
            <?= $ayuda ?>
        </div>
        <?php break;
    case 'parrafo': ?>
        <div class="form-group" data-campo="<?= Html::encode($nombre) ?>"<?= $si ?>>
            <label class="control-label" for="<?= $id ?>"><?= $etiqueta ?></label>
            <?= Html::textarea($prefijo, $valor, $comunes + ['rows' => min(8, max(2, (int) (($c['max'] ?? 300) / 160)))]) ?>
            <?= $ayuda ?>
        </div>
        <?php break;
    case 'html': ?>
        <div class="form-group" data-campo="<?= Html::encode($nombre) ?>">
            <label class="control-label" for="<?= $id ?>"><?= $etiqueta ?></label>
            <?= Html::textarea($prefijo, $valor, ['id' => $id, 'class' => 'form-control', 'rows' => 10, 'data-pe-html' => '1',
                'data-tinymce-base' => Url::to('@web/assets_b/lib/tinymce')]) ?>
            <p class="help-block">Negrita, cursiva, listas, enlaces y subtítulos. El diseño (letra, colores) lo pone la web.</p>
        </div>
        <?php break;
    case 'opciones': ?>
        <?php $valor = (string) $valor === '' ? (string) array_key_first($c['opciones']) : (string) $valor; ?>
        <div class="form-group" data-campo="<?= Html::encode($nombre) ?>">
            <label class="control-label"><?= $etiqueta ?></label>
            <div class="huk-pe-opciones is-texto">
                <?php foreach ($c['opciones'] as $v => $t): ?>
                    <label class="huk-pe-opcion"><?= Html::radio($prefijo, (string) $valor === (string) $v, ['value' => $v]) ?> <?= Html::encode($t) ?></label>
                <?php endforeach; ?>
            </div>
            <?= $ayuda ?>
        </div>
        <?php break;
    case 'casilla': ?>
        <div class="form-group huk-pe-casillas" data-campo="<?= Html::encode($nombre) ?>">
            <?= Html::hiddenInput($prefijo, '0') ?>
            <label><?= Html::checkbox($prefijo, (bool) $valor, ['value' => '1', 'id' => $id]) ?> <?= Html::encode($c['etiqueta'] ?? $nombre) ?></label>
            <?= $ayuda ?>
        </div>
        <?php break;
    case 'imagen': ?>
        <div class="form-group huk-pe-imagen" data-campo="<?= Html::encode($nombre) ?>" data-pe-imagen>
            <label class="control-label" for="<?= $id ?>"><?= $etiqueta ?></label>
            <div class="huk-pe-imagen-caja">
                <div class="huk-pe-imagen-vista" data-pe-imagen-vista>
                    <?php if ($valor): ?>
                        <?= Html::img($medios->imagen('bloques', $valor, 480), ['alt' => '']) ?>
                    <?php else: ?>
                        <i class="fa fa-picture-o"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <?= Html::hiddenInput($prefijo, $valor, ['data-pe-imagen-valor' => '1']) ?>
                    <?= ZonaArchivo::envolver(Html::fileInput('archivos[' . $ruta . ']', null, ['id' => $id, 'accept' => 'image/jpeg,image/png,image/webp', 'data-pe-imagen-archivo' => '1']),
                        '', ['compacta' => true, 'lista' => false, 'botones' => false, 'titulo' => 'Arrastre la foto aquí']) ?>
                    <button type="button" class="btn btn-link btn-xs" data-pe-imagen-quitar<?= $valor ? '' : ' hidden' ?>><i class="fa fa-times"></i> Quitar foto</button>
                    <?= $ayuda ?: '<p class="help-block">JPG, PNG o WEBP, hasta 8 MB.</p>' ?>
                </div>
            </div>
        </div>
        <?php break;
    case 'lista': ?>
        <?php $filas = (array) $valor; ?>
        <div class="form-group huk-pe-lista" data-campo="<?= Html::encode($nombre) ?>" data-pe-lista data-max="<?= (int) ($c['maxItems'] ?? 50) ?>"<?= $si ?>>
            <label class="control-label"><?= $etiqueta ?></label>
            <div data-pe-filas>
                <?php foreach ($filas as $i => $fila): ?>
                    <?= $this->render('_campo_fila', ['nombre' => $nombre, 'c' => $c, 'fila' => $fila, 'i' => $i, 'medios' => $medios, 'fuentes' => $fuentes]) ?>
                <?php endforeach; ?>
            </div>
            <template data-pe-plantilla><?= $this->render('_campo_fila', ['nombre' => $nombre, 'c' => $c, 'fila' => [], 'i' => '__i__', 'medios' => $medios, 'fuentes' => $fuentes]) ?></template>
            <button type="button" class="btn btn-default btn-sm" data-pe-fila-agregar><i class="fa fa-plus"></i> Agregar <?= Html::encode(mb_strtolower($c['item'] ?? 'ítem')) ?></button>
            <?php if (in_array('imagen', array_column($c['campos'], 'tipo'), true)): ?>
                <label class="btn btn-default btn-sm huk-pe-varias" data-pe-varias>
                    <i class="fa fa-upload"></i> Subir varias fotos
                    <input type="file" multiple accept="image/jpeg,image/png,image/webp" data-pe-varias-archivos hidden>
                </label>
            <?php endif; ?>
            <?= $ayuda ?>
        </div>
        <?php break;
    case 'numero': ?>
        <div class="form-group huk-pe-numero" data-campo="<?= Html::encode($nombre) ?>">
            <label class="control-label" for="<?= $id ?>"><?= $etiqueta ?></label>
            <?= Html::textInput($prefijo, $valor, ['id' => $id, 'class' => 'form-control', 'inputmode' => 'decimal', 'placeholder' => $c['placeholder'] ?? null, 'maxlength' => 20]) ?>
            <?= $ayuda ?>
        </div>
        <?php break;
    case 'lineas': ?>
        <div class="form-group" data-campo="<?= Html::encode($nombre) ?>">
            <label class="control-label" for="<?= $id ?>"><?= $etiqueta ?></label>
            <?= Html::textarea($prefijo, $valor, ['id' => $id, 'class' => 'form-control huk-pe-lineas', 'rows' => min(10, max(3, substr_count((string) $valor, "\n") + 2)),
                'placeholder' => $c['placeholder'] ?? null, 'maxlength' => $c['max'] ?? null]) ?>
            <?= $ayuda ?: '<p class="help-block">Una por línea. Las líneas vacías no cuentan.</p>' ?>
        </div>
        <?php break;
    case 'tabla': ?>
        <?php $tabla = \app\models\bloques\Bloque::limpiarTabla($valor); ?>
        <div class="form-group huk-pe-tabla" data-campo="<?= Html::encode($nombre) ?>" data-pe-tabla
             data-max-columnas="<?= \app\models\bloques\Bloque::TABLA_MAX_COLUMNAS ?>" data-max-filas="<?= \app\models\bloques\Bloque::TABLA_MAX_FILAS ?>"
             data-max-celda="<?= \app\models\bloques\Bloque::TABLA_MAX_CELDA ?>">
            <label class="control-label"><?= $etiqueta ?></label>
            <?= Html::hiddenInput($prefijo, json_encode($tabla, JSON_UNESCAPED_UNICODE), ['data-pe-tabla-valor' => '1']) ?>
            <div class="huk-pe-tabla-barra">
                <button type="button" class="btn btn-default btn-sm" data-pe-tabla-pegar><i class="fa fa-clipboard"></i> Pegar desde Excel</button>
                <button type="button" class="btn btn-default btn-sm" data-pe-tabla-columna><i class="fa fa-columns"></i> Columna</button>
                <button type="button" class="btn btn-default btn-sm" data-pe-tabla-fila><i class="fa fa-plus"></i> Fila</button>
                <span class="huk-pe-tabla-formato" title="Se aplica a la celda donde está escribiendo (o a lo que tenga seleccionado)">
                    <button type="button" class="btn btn-default btn-sm" data-pe-tabla-marca="**|**"><b>N</b> Negrita</button>
                    <button type="button" class="btn btn-default btn-sm" data-pe-tabla-marca="[|]"><span class="huk-pe-chip">chip</span></button>
                    <button type="button" class="btn btn-default btn-sm" data-pe-tabla-marca="[*|]"><span class="huk-pe-chip is-dorado">chip dorado</span></button>
                </span>
            </div>
            <div class="huk-pe-tabla-scroll"><table class="huk-pe-tabla-grilla" data-pe-tabla-grilla></table></div>
            <p class="help-block">La primera fila (gris) son los títulos de las columnas. En las celdas: <code>**texto**</code> negrita,
                <code>[texto]</code> chip gris, <code>[*texto]</code> chip dorado. Máximo <?= \app\models\bloques\Bloque::TABLA_MAX_COLUMNAS ?> columnas
                y <?= \app\models\bloques\Bloque::TABLA_MAX_FILAS ?> filas.</p>
        </div>
        <?php break;
    case 'elegir': ?>
        <?php $opciones = $fuentes[$c['fuente']] ?? null; $elegidos = array_map('intval', (array) $valor); ?>
        <div class="form-group huk-pe-elegir" data-campo="<?= Html::encode($nombre) ?>"<?= $si ?>>
            <label class="control-label"><?= $etiqueta ?></label>
            <?php if ($opciones === null): ?>
                <p class="huk-pe-elegir-vacio"><i class="fa fa-info-circle"></i> En una plantilla no hay proyecto todavía: cuando se use en un proyecto,
                    se muestran <b>todos</b> sus <?= Html::encode($c['fuente']) ?>. Después, en el proyecto, se puede elegir.</p>
            <?php elseif (!$opciones): ?>
                <p class="huk-pe-elegir-vacio"><i class="fa fa-info-circle"></i> El proyecto todavía no tiene <?= Html::encode($c['fuente']) ?>.
                    Súbalos en la pestaña «<?= Html::encode(ucfirst($c['fuente'])) ?>» del proyecto y vuelva a abrir este bloque.</p>
            <?php else: ?>
                <?= Html::hiddenInput($prefijo . '[]', '') ?>
                <div class="huk-pe-elegir-barra">
                    <button type="button" class="btn btn-link btn-xs" data-pe-elegir-todo="1">Marcar todos</button> ·
                    <button type="button" class="btn btn-link btn-xs" data-pe-elegir-todo="0">Ninguno</button>
                    <span class="huk-pe-elegir-cuenta" data-pe-elegir-cuenta></span>
                </div>
                <div class="huk-pe-elegir-grilla is-<?= Html::encode($c['fuente']) ?>" data-pe-elegir>
                    <?php foreach ($opciones as $o): ?>
                        <label class="huk-pe-elegible">
                            <?= Html::checkbox($prefijo . '[]', in_array((int) $o['id'], $elegidos, true), ['value' => $o['id']]) ?>
                            <span class="huk-pe-elegible-vista">
                                <?php if (!empty($o['miniatura'])): ?>
                                    <img src="<?= Html::encode($o['miniatura']) ?>" alt="" loading="lazy">
                                <?php else: ?>
                                    <i class="fa fa-<?= Html::encode($o['icono'] ?? 'file-o') ?>"></i>
                                <?php endif; ?>
                                <i class="fa fa-check huk-pe-elegible-tilde"></i>
                            </span>
                            <span class="huk-pe-elegible-texto"><?= Html::encode($o['titulo']) ?>
                                <?php if (!empty($o['nota'])): ?><small><?= Html::encode($o['nota']) ?></small><?php endif; ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?= $ayuda ?>
        </div>
        <?php break;
endswitch; ?>
