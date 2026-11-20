<?php

/**
 * Página de un ecosistema · Oportunidades (solo si en Panel → Ecosistemas está marcado "Mostrar el bloque Oportunidades").
 * Recuadro punteado con el "+" que gira, el texto del ecosistema (o uno general) y "Proponer un proyecto" (lleva a Contacto).
 *
 * @var yii\web\View $this
 * @var app\models\Ecosistema $ecosistema
 */

use yii\helpers\Html;

$e = $ecosistema;
if ($e->mostrar_oportunidades !== '1') {
    return;
}
$texto = $e->oportunidades ?: 'Estamos armando la cartera de proyectos de ' . $e->nombre . '. Si su empresa aporta tecnología, mercado o capital, conversemos.';
?>
<section class="huk-eco-oport" aria-labelledby="oport-titulo">
    <div class="container">
        <header class="huk-nos-cabecera" data-reveal="left">
            <p class="huk-nos-etiqueta">Proyectos</p>
            <h2 id="oport-titulo" class="huk-nos-titulo">Oportunidades</h2>
        </header>
        <div class="huk-eco-oport-caja" data-reveal="zoom">
            <span class="huk-eco-oport-icono" aria-hidden="true"><i class="fa-solid fa-plus"></i></span>
            <div>
                <h3>Cartera de proyectos en originación</h3>
                <p><?= nl2br(Html::encode($texto)) ?></p>
            </div>
            <?= Html::a('Proponer un proyecto', ['/site/contacto', '#' => 'formulario'], ['class' => 'huk-eco-oport-btn']) ?>
        </div>
    </div>
</section>
