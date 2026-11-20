<?php

/**
 * Página de un ecosistema · Alcance: título y dos columnas siempre de igual ancho:
 * recuadro negro "Enfoque del grupo" (Panel → Ecosistemas → Enfoque) y "Presencia en la visión de expansión"
 * (los países marcados en el ecosistema, con el enfoque de cada uno que se carga en Páginas → Ejes estratégicos → Expansión).
 * Debajo, la descripción general (listas numeradas de 2 en 2 con su número; viñetas con cuadrito dorado).
 * Efectos: el título entra con zoom, el recuadro de la izquierda y los países de la derecha (de a uno).
 *
 * @var yii\web\View $this
 * @var app\models\Ecosistema $ecosistema
 * @var array $paises  código => enfoque (los de la visión de expansión)
 */

use app\widgets\MapaPaises;
use yii\helpers\Html;

$e = $ecosistema;
$presencia = array_values(array_filter($e->listaPaises(), static fn ($c) => array_key_exists($c, $paises)));
$descripcion = trim(strip_tags((string) $e->descripcion, '<img>')) !== '' ? $e->descripcion : '';
if (!$e->enfoque && !$presencia && $descripcion === '') {
    return;
}
?>
<section class="huk-eco-alcance" aria-labelledby="alcance-titulo">
    <div class="container">
        <header class="huk-nos-cabecera" data-reveal="zoom">
            <p class="huk-nos-etiqueta">Qué hacemos</p>
            <h2 id="alcance-titulo" class="huk-nos-titulo">Alcance del ecosistema</h2>
        </header>

        <?php if ($e->enfoque || $presencia): ?>
            <div class="huk-eco-alcance-grid">
                <div class="huk-eco-alcance-col">
                    <?php if ($e->enfoque): ?>
                        <div class="huk-eco-enfoque" data-reveal="left">
                            <p class="huk-eco-enfoque-label">Enfoque del grupo</p>
                            <p><?= nl2br(Html::encode($e->enfoque)) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="huk-eco-alcance-col">
                    <?php if ($presencia): ?>
                        <div class="huk-eco-presencia" data-reveal="right">
                            <p class="huk-eco-presencia-label">Presencia en la visión de expansión</p>
                            <ul>
                                <?php foreach ($presencia as $i => $codigo): ?>
                                    <li data-reveal="right" data-reveal-delay="<?= 120 + $i * 90 ?>">
                                        <b><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?= Html::encode(MapaPaises::nombre($codigo)) ?></b>
                                        <?php if ($paises[$codigo] !== ''): ?><span><?= Html::encode($paises[$codigo]) ?></span><?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($descripcion !== ''): ?>
            <div class="huk-eco-descripcion huk-noticia-texto" data-reveal><?= $descripcion /* HTML limpio (HtmlPurifier al guardar) */ ?></div>
        <?php endif; ?>
    </div>
</section>
