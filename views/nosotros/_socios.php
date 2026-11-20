<?php

/**
 * Nosotros → Gobierno y socios · Propuesta de valor para socios (Panel → Páginas → Gobierno y socios → Propuesta para socios).
 * Una columna: texto chico, título grande con línea dorada, texto, lista "quién aporta qué" y botón al registro de inversores.
 * Efectos: la cabecera entra de la izquierda y las filas suben de a una.
 * Hover: la fila se pone negra, el texto blanco y se corre (como la fila destacada de la referencia).
 *
 * @var yii\web\View $this
 * @var app\models\PaginaSeccion|null $socios
 */

use yii\helpers\Html;

if ($socios === null) {
    return;
}
?>
<section class="huk-nos-socios" aria-labelledby="socios-titulo">
    <div class="container huk-nos-socios-col">
        <header class="huk-nos-cabecera" data-reveal="left">
            <p class="huk-nos-etiqueta"><?= Html::encode($socios->titulo) ?></p>
            <h2 id="socios-titulo" class="huk-nos-titulo huk-nos-socios-titulo"><?= Html::encode($socios->subtitulo ?: $socios->titulo) ?></h2>
            <?php if ($socios->texto): ?>
                <p class="huk-nos-bajada"><?= nl2br(Html::encode($socios->texto)) ?></p>
            <?php endif; ?>
        </header>
        <?php if ($socios->itemsActivos): ?>
            <ul class="huk-nos-aportes">
                <?php foreach ($socios->itemsActivos as $n => $a): ?>
                    <li data-reveal data-reveal-delay="<?= min($n, 6) * 90 ?>">
                        <small><?= Html::encode($a->titulo) ?></small>
                        <?php if ($a->texto): ?><b><?= Html::encode($a->texto) ?></b><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($socios->firma): ?>
            <div data-reveal="zoom">
                <?= Html::a(Html::encode($socios->firma) . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>', ['/cuenta/acceso/registro'], ['class' => 'btn btn-primary huk-nos-socios-btn huk-shine']) ?>
            </div>
        <?php endif; ?>
    </div>
</section>
