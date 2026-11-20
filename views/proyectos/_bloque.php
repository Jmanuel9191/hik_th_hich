<?php

/**
 * Un bloque: lo envuelve con su efecto de entrada y dibuja la vista de su tipo (bloques/<tipo>.php).
 * Efecto: los de la columna izquierda entran desde la izquierda, los de la derecha desde la derecha,
 * las fotos con zoom y el resto subiendo.
 *
 * @var yii\web\View $this
 * @var app\models\ProyectoBloque $bloque
 * @var string|null $lado  izquierda | derecha | null (ancho completo)
 * @var app\models\Proyecto $proyecto
 * @var app\components\MediosProyecto $medios
 * @var array $biblioteca  ['fotos', 'videos', 'documentos'] (ProyectoWebService::biblioteca)
 */

$contenido = $bloque->contenido();
if ($contenido === null) {
    return;
}
$tipo = $bloque->tipo;
// las fotos entran con zoom; las columnas desde su costado; el resto subiendo
$efecto = in_array($tipo, ['imagen', 'galeria', 'galeria_proyecto', 'videos', 'minerales'], true) ? 'zoom' : ($lado === 'izquierda' ? 'left' : ($lado === 'derecha' ? 'right' : ''));
$html = trim($this->render('bloques/' . $tipo, [
    'b' => $contenido,
    'proyecto' => $proyecto,
    'medios' => $medios,
    'biblioteca' => $biblioteca,
    'sizes' => $lado === null ? '(max-width: 1199.98px) 100vw, 1140px' : '(max-width: 991.98px) 100vw, 560px',
]));
if ($html === '') {
    return; // ej. documentos, fotos o videos sin nada para mostrar
}
?>
<div class="huk-pro-b huk-pro-b--<?= $tipo ?>" data-reveal="<?= $efecto ?>"><?= $html ?></div>
