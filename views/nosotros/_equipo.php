<?php

/**
 * Nosotros → Liderazgo (Panel → Equipo): activos, primero los destacados y después en el orden de la lista.
 * Tarjetas blancas de 2 en 2 (1 en celular): foto con línea dorada abajo (o iniciales si no tiene), nombre, puesto,
 * resumen (biografía) y, al pie, los datos de contacto que tenga cargados (teléfono, email y redes).
 * Las tarjetas de una misma fila tienen el mismo alto y los contactos quedan siempre abajo, aunque una tenga menos texto.
 * Efectos: la cabecera entra con zoom; las tarjetas entran de la izquierda y de la derecha.
 * Hover: la tarjeta sube con sombra y la foto toma más color.
 *
 * @var yii\web\View $this
 * @var string $titulo  nombre de la página
 * @var app\models\Equipo[] $equipo
 */

use app\models\Equipo;
use yii\helpers\Html;

if (empty($equipo)) {
    return;
}
// Columna de la red => ícono de Font Awesome 7 (el panel usa los de Font Awesome 4: Equipo::REDES)
$iconos = ['linkedin' => 'fa-linkedin-in', 'facebook' => 'fa-facebook-f', 'instagram' => 'fa-instagram', 'twitter' => 'fa-x-twitter'];
$iniciales = static function (string $nombre): string {
    $partes = preg_split('/\s+/u', trim($nombre)) ?: [];
    return mb_strtoupper(mb_substr($partes[0] ?? '', 0, 1) . mb_substr(count($partes) > 1 ? end($partes) : '', 0, 1));
};
$icono = static fn (string $clase): string => Html::tag('i', '', ['class' => $clase, 'aria-hidden' => 'true']);
?>
<section class="huk-nos-equipo" aria-labelledby="equipo-titulo">
    <div class="container">
        <header class="huk-nos-cabecera es-centrada" data-reveal="zoom">
            <p class="huk-nos-etiqueta">Equipo directivo</p>
            <h2 id="equipo-titulo" class="huk-nos-titulo"><?= Html::encode($titulo) ?></h2>
        </header>
        <div class="huk-nos-equipo-grid">
            <?php foreach ($equipo as $i => $m): ?>
                <?php $redes = array_filter($m->getAttributes(array_keys($iconos))); ?>
                <article class="huk-nos-miembro" data-reveal="<?= $i % 2 ? 'right' : 'left' ?>" data-reveal-delay="<?= ($i % 2) * 150 ?>">
                    <div class="huk-nos-miembro-foto">
                        <?php if ($m->foto): ?>
                            <?= Html::img(Equipo::url($m->foto), ['alt' => $m->nombre, 'loading' => 'lazy']) ?>
                        <?php else: ?>
                            <span class="huk-nos-iniciales" aria-hidden="true"><?= Html::encode($iniciales($m->nombre)) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="huk-nos-miembro-txt">
                        <h3><?= Html::encode($m->nombre) ?></h3>
                        <p class="huk-nos-miembro-puesto"><?= Html::encode($m->puesto) ?></p>
                        <?php if ($m->biografia): ?>
                            <div class="huk-nos-miembro-bio"><?= $m->biografia /* HTML limpio (HtmlEditor) */ ?></div>
                        <?php endif; ?>
                        <?php if ($m->telefono || $m->email || $redes): ?>
                            <div class="huk-nos-miembro-contacto">
                                <?php if ($m->telefono): ?>
                                    <?= Html::a($icono('fa-solid fa-phone') . '<span>' . Html::encode($m->telefono) . '</span>', 'tel:' . preg_replace('/[^0-9+]/', '', $m->telefono), ['class' => 'huk-nos-miembro-dato']) ?>
                                <?php endif; ?>
                                <?php if ($m->email): ?>
                                    <?= Html::a($icono('fa-solid fa-envelope') . '<span>' . Html::encode($m->email) . '</span>', 'mailto:' . $m->email, ['class' => 'huk-nos-miembro-dato']) ?>
                                <?php endif; ?>
                                <?php if ($redes): ?>
                                    <div class="huk-nos-miembro-redes">
                                        <?php foreach ($redes as $red => $url): ?>
                                            <?= Html::a($icono('fa-brands ' . $iconos[$red]), $url, [
                                                'target' => '_blank', 'rel' => 'noopener noreferrer', 'aria-label' => Equipo::REDES[$red][0] . ' de ' . $m->nombre,
                                            ]) ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
