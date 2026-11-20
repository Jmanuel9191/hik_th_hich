<?php

/**
 * Nosotros · Liderazgo (Panel → Equipo): activos, primero los destacados y después en el orden de la lista.
 * Foto (o iniciales si no tiene), nombre, puesto, redes y biografía desplegable.
 *
 * @var yii\web\View $this
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
?>
<section class="huk-nos-equipo" aria-labelledby="equipo-titulo">
    <div class="container">
        <header class="huk-nos-cabecera es-centrada" data-reveal>
            <p class="huk-nos-etiqueta">Nuestro equipo</p>
            <h2 id="equipo-titulo" class="huk-nos-titulo">Liderazgo</h2>
        </header>
        <div class="huk-nos-equipo-grid">
            <?php foreach ($equipo as $i => $m): ?>
                <article class="huk-nos-miembro" data-reveal data-reveal-delay="<?= ($i % 3) * 150 ?>">
                    <div class="huk-nos-miembro-foto">
                        <?php if ($m->foto): ?>
                            <?= Html::img(Equipo::url($m->foto), ['alt' => $m->nombre, 'loading' => 'lazy']) ?>
                        <?php else: ?>
                            <span class="huk-nos-iniciales" aria-hidden="true"><?= Html::encode($iniciales($m->nombre)) ?></span>
                        <?php endif; ?>
                        <?php $redes = array_filter($m->getAttributes(array_keys($iconos))); ?>
                        <?php if ($redes): ?>
                            <div class="huk-nos-miembro-redes">
                                <?php foreach ($redes as $red => $url): ?>
                                    <?= Html::a(Html::tag('i', '', ['class' => 'fa-brands ' . $iconos[$red], 'aria-hidden' => 'true']), $url, [
                                        'target' => '_blank', 'rel' => 'noopener noreferrer', 'aria-label' => Equipo::REDES[$red][0] . ' de ' . $m->nombre,
                                    ]) ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3><?= Html::encode($m->nombre) ?></h3>
                    <p class="huk-nos-miembro-puesto"><?= Html::encode($m->puesto) ?></p>
                    <?php if (trim(strip_tags((string) $m->biografia)) !== ''): ?>
                        <details class="huk-nos-miembro-bio">
                            <summary>Ver biografía</summary>
                            <div><?= $m->biografia /* HTML limpio (HtmlEditor) */ ?></div>
                        </details>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
