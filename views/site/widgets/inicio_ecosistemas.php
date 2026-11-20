<?php

/**
 * Inicio · Ecosistemas: todos los activos (Panel → Ecosistemas) de a 4 por fila sobre fondo negro, con las mismas
 * tarjetas de la página Ecosistemas (huk.css sección "Ecosistemas").
 *
 * @var yii\web\View $this
 * @var app\models\Ecosistema[] $ecosistemas
 */

use yii\helpers\Html;

if (!$ecosistemas) {
    return;
}
$efectos = ['left', '', '', 'right'];
?>
<section class="huk-ecos is-oscuro" aria-labelledby="inicio-ecos-titulo">
    <div class="container">
        <header class="huk-ini-cab" data-reveal>
            <div>
                <p class="huk-nos-etiqueta">Ecosistemas</p>
                <h2 id="inicio-ecos-titulo" class="huk-nos-titulo">Sectores donde creamos valor</h2>
            </div>
            <?= Html::a('Ver todos los ecosistemas <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>', ['/ecosistemas/index'], ['class' => 'huk-ini-todos']) ?>
        </header>
        <div class="huk-ecos-grid">
            <?php foreach ($ecosistemas as $i => $e): ?>
                <?= $this->render('ecosistema_tarjeta', ['ecosistema' => $e, 'efecto' => $efectos[$i % 4], 'retraso' => ($i % 4) * 110]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
