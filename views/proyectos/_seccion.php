<?php

/**
 * Una sección: fondo blanco, gris o negro; encabezado (número · antetítulo + título) arriba o en la
 * columna izquierda; y sus filas de bloques: de ancho completo o de dos columnas (se pueden mezclar).
 *
 * @var yii\web\View $this
 * @var app\models\ProyectoSeccion $seccion
 * @var array $filas    ProyectoSeccion::filas()
 * @var array $contexto ['proyecto', 'medios', 'biblioteca']
 */

use app\models\ProyectoSeccion;
use yii\helpers\Html;

$s = $seccion;
$enColumna = ProyectoSeccion::tituloEnColumna($s, $filas);
$encabezado = $s->tieneEncabezado() ? $this->render('_encabezado', ['seccion' => $s]) : '';
?>
<section id="<?= Html::encode($s->ancla) ?>" class="huk-pro-sec is-<?= Html::encode($s->fondo) ?>" data-pro-seccion>
    <div class="container">
        <?php if (!$enColumna): ?>
            <?= $encabezado ?>
        <?php endif; ?>

        <?php foreach ($filas as $k => $fila): ?>
            <?php if (isset($fila['completo'])): ?>
                <div class="huk-pro-fila">
                    <?= $this->render('_bloque', ['bloque' => $fila['completo'], 'lado' => null] + $contexto) ?>
                </div>
            <?php else: ?>
                <div class="huk-pro-fila huk-pro-dos">
                    <div class="huk-pro-col">
                        <?php if ($k === 0 && $enColumna): ?>
                            <?= $encabezado ?>
                        <?php endif; ?>
                        <?php foreach ($fila['izquierda'] as $b): ?>
                            <?= $this->render('_bloque', ['bloque' => $b, 'lado' => 'izquierda'] + $contexto) ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="huk-pro-col">
                        <?php foreach ($fila['derecha'] as $b): ?>
                            <?= $this->render('_bloque', ['bloque' => $b, 'lado' => 'derecha'] + $contexto) ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>
