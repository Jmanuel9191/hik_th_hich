<?php

/**
 * Documentos: los marcados "lo ven los inversores" (todos o los elegidos). PDF y fotos se abren en otra pestaña;
 * Word, Excel y el resto se descargan. Cada apertura o descarga queda registrada.
 * Sin documentos: en la web no se muestra; en la vista previa, un aviso.
 * @var app\models\bloques\BloqueDocumentos $b
 * @var array $biblioteca
 * @var app\components\MediosProyecto $medios
 */

use app\components\SubidaArchivo;
use yii\helpers\Html;

$documentos = $b->documentosDe($biblioteca['documentos']);
if (!$documentos && !$medios->esPanel()) {
    return;
}
$iconos = ['pdf' => 'fa-file-pdf', 'word' => 'fa-file-word', 'excel' => 'fa-file-excel', 'powerpoint' => 'fa-file-powerpoint', 'imagen' => 'fa-file-image', 'otro' => 'fa-file-lines'];
?>
<div class="huk-pro-docs">
    <?php if ($b->titulo !== ''): ?>
        <h3 class="huk-pro-h3"><?= Html::encode($b->titulo) ?></h3>
    <?php endif; ?>
    <?php if ($b->texto !== ''): ?>
        <p class="huk-pro-docs-texto"><?= nl2br(Html::encode($b->texto)) ?></p>
    <?php endif; ?>
    <?php if ($documentos): ?>
        <ul class="huk-pro-docs-lista">
            <?php foreach ($documentos as $i => $d): ?>
                <?php $ve = $d->seVe(); ?>
                <li style="--i:<?= $i ?>">
                    <a href="<?= Html::encode($medios->documento((int) $d->iddocumento)) ?>" class="huk-pro-doc is-<?= $d->clase() ?>"
                       <?= $ve ? 'target="_blank" rel="noopener"' : 'download' ?>>
                        <i class="fa-regular <?= $iconos[$d->clase()] ?>" aria-hidden="true"></i>
                        <span class="huk-pro-doc-txt">
                            <b><?= Html::encode($d->nombre) ?></b>
                            <small><?= Html::encode($d->tipoNombre()) ?> · <?= strtoupper(Html::encode($d->extension())) ?> · <?= SubidaArchivo::peso($d->tamano) ?></small>
                        </span>
                        <span class="huk-pro-doc-bajar" title="<?= $ve ? 'Ver' : 'Descargar' ?>">
                            <i class="fa-solid <?= $ve ? 'fa-eye' : 'fa-download' ?>" aria-hidden="true"></i><span class="visually-hidden"><?= $ve ? 'Ver (se abre en otra pestaña)' : 'Descargar' ?></span>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="huk-pro-nota">Vista previa: todavía no hay documentos marcados «lo ven los inversores» (pestaña Documentos). En la web este bloque no se muestra.</p>
    <?php endif; ?>
</div>
