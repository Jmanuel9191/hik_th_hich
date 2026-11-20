<?php

/**
 * Botones para compartir (Facebook, WhatsApp, LinkedIn, X y copiar el enlace). Reutilizable: noticias y ecosistemas.
 * La vista previa (imagen y texto) la arman las etiquetas Open Graph de cada página. Estilos: huk.css sección 25.
 *
 * @var yii\web\View $this
 * @var string $enlace    URL completa de la página
 * @var string $titulo    texto que acompaña al enlace (WhatsApp y X)
 * @var string $etiqueta  ej. "Compartir" o "Compartir este ecosistema"
 */

use yii\helpers\Html;

$redes = [
    ['Facebook', 'fa-facebook-f', 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($enlace)],
    ['WhatsApp', 'fa-whatsapp', 'https://wa.me/?text=' . rawurlencode($titulo . ' ' . $enlace)],
    ['LinkedIn', 'fa-linkedin-in', 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($enlace)],
    ['X', 'fa-x-twitter', 'https://twitter.com/intent/tweet?url=' . rawurlencode($enlace) . '&text=' . rawurlencode($titulo)],
];
?>
<div class="huk-noticia-compartir" data-reveal>
    <span><?= Html::encode($etiqueta ?? 'Compartir') ?></span>
    <?php foreach ($redes as [$red, $icono, $url]): ?>
        <?= Html::a(Html::tag('i', '', ['class' => 'fa-brands ' . $icono, 'aria-hidden' => 'true']), $url, [
            'target' => '_blank', 'rel' => 'noopener noreferrer', 'aria-label' => 'Compartir en ' . $red, 'title' => 'Compartir en ' . $red,
        ]) ?>
    <?php endforeach; ?>
    <button type="button" data-copiar="<?= Html::encode($enlace) ?>" data-copiar-ok="¡Enlace copiado!" aria-label="Copiar el enlace" title="Copiar el enlace">
        <i class="fa-solid fa-link" aria-hidden="true"></i>
    </button>
</div>
