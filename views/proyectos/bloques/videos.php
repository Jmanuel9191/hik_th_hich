<?php

/**
 * Videos: reproductor grande y, con varios, una lista para cambiar (huk-proyecto.js punto 4).
 * Archivos: <video> sin botón de descarga ni menú del botón derecho; se piden de a partes (Range).
 * YouTube / Vimeo: se carga el reproductor recién al tocar "play" (la página no baja nada de afuera antes).
 * Sin videos: en la web no se muestra.
 * @var app\models\bloques\BloqueVideos $b
 * @var app\components\MediosProyecto $medios
 * @var array $biblioteca
 */

use yii\helpers\Html;

$lista = $b->lista($biblioteca['videos']);
if (!$lista) {
    if ($medios->esPanel()) {
        echo '<p class="huk-pro-nota">Vista previa: no hay videos para mostrar (pestaña Videos o enlaces del bloque). En la web este bloque no se muestra.</p>';
    }
    return;
}
// datos de cada video para el reproductor
$items = [];
foreach ($lista as $v) {
    if ($v['archivo']) {
        $jpg = $v['archivo']->miniatura();
        $items[] = ['tipo' => 'archivo', 'titulo' => $v['titulo'], 'src' => $medios->video((int) $v['archivo']->idvideo),
            'poster' => $jpg ? $medios->imagen('videos', $jpg, 1440) : null, 'mini' => $jpg ? $medios->imagen('videos', $jpg, 480) : null];
    } else {
        $items[] = ['tipo' => 'enlace', 'titulo' => $v['titulo'], 'src' => $v['embed'], 'poster' => $v['miniatura'], 'mini' => $v['miniatura']];
    }
}
$primero = $items[0];
?>
<div class="huk-pro-videos<?= count($items) > 1 ? ' is-varios' : '' ?>" data-pro-videos>
    <?php if ($b->titulo !== ''): ?>
        <p class="huk-galeria-web-titulo"><i class="fa-solid fa-film" aria-hidden="true"></i> <?= Html::encode($b->titulo) ?></p>
    <?php endif; ?>
    <div class="huk-pro-videos-cuerpo">
        <div class="huk-pro-video" data-pro-video-escenario>
            <?php if ($primero['tipo'] === 'archivo'): ?>
                <video controls playsinline preload="metadata" controlslist="nodownload noremoteplayback" disablepictureinpicture
                       <?= $primero['poster'] ? ' poster="' . Html::encode($primero['poster']) . '"' : '' ?>
                       src="<?= Html::encode($primero['src']) ?>#t=0.1" title="<?= Html::encode($primero['titulo']) ?>"></video>
            <?php else: ?>
                <button type="button" class="huk-pro-video-fachada" data-pro-video-embed="<?= Html::encode($primero['src']) ?>" data-titulo="<?= Html::encode($primero['titulo']) ?>">
                    <?php if ($primero['poster']): ?><img src="<?= Html::encode($primero['poster']) ?>" alt="" loading="lazy"><?php endif; ?>
                    <span class="huk-pro-video-play"><i class="fa-solid fa-play" aria-hidden="true"></i></span>
                    <span class="visually-hidden">Reproducir «<?= Html::encode($primero['titulo']) ?>»</span>
                </button>
            <?php endif; ?>
        </div>
        <?php if (count($items) > 1): ?>
            <ol class="huk-pro-videos-lista" aria-label="Más videos">
                <?php foreach ($items as $i => $it): ?>
                    <li>
                        <button type="button" class="<?= $i ? '' : 'is-activo' ?>" data-pro-video='<?= Html::encode(json_encode($it, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>'
                                <?= $i ? '' : 'aria-current="true"' ?>>
                            <span class="huk-pro-videos-mini">
                                <?php if ($it['mini']): ?><img src="<?= Html::encode($it['mini']) ?>" alt="" loading="lazy"><?php else: ?><i class="fa-solid fa-film" aria-hidden="true"></i><?php endif; ?>
                                <i class="fa-solid fa-play huk-pro-videos-mini-play" aria-hidden="true"></i>
                            </span>
                            <span class="huk-pro-videos-titulo"><?= Html::encode($it['titulo']) ?>
                                <small><?= $it['tipo'] === 'archivo' ? 'Video' : (str_contains($it['src'], 'vimeo') ? 'Vimeo' : 'YouTube') ?></small></span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <p class="huk-pro-videos-unico"><?= Html::encode($primero['titulo']) ?></p>
        <?php endif; ?>
    </div>
</div>
