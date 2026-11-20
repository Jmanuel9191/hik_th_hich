<?php

/**
 * Mi cuenta: perfil en dos columnas. Izquierda: foto y datos clave. Derecha: pestañas
 * Resumen (estado, áreas, pedido de áreas, acuerdo) · Mis datos · Mis inversiones (aprobados) · Seguridad.
 * La pestaña abierta viene del servidor ($tab) y el JS la mantiene en la URL.
 *
 * @var yii\web\View $this
 * @var app\models\Inversor $inversor
 * @var app\models\NdaAceptacion|null $nda
 * @var app\models\TerminosVersion|null $terminos
 * @var array $proyectos idecosistema => cantidad visibles
 * @var app\models\Ecosistema[] $pendientes áreas pedidas sin asignar
 * @var array $disponibles idecosistema => nombre (puede pedirlas)
 * @var array $inversiones
 * @var bool $tieneFoto
 * @var string $tab
 * @var app\models\PerfilInversorForm $formDatos
 * @var app\models\ContrasenaInversorForm $formClave
 * @var app\models\InteresInversion[] $intereses  sus "Quiero invertir"
 */

use yii\helpers\Html;

\app\assets_b\CuentaAsset::register($this);

$aprobado = $inversor->estado_solicitud === 'aprobado';
$pestanas = array_filter([
    'resumen' => ['fa-gauge-high', 'Resumen'],
    'datos' => ['fa-id-card', 'Mis datos'],
    'inversiones' => $aprobado ? ['fa-sack-dollar', 'Mis inversiones'] : null,
    'seguridad' => ['fa-shield-halved', 'Seguridad'],
]);
if (!isset($pestanas[$tab])) {
    $tab = 'resumen';
}
?>

<section class="huk-cuenta huk-perfil">
    <div class="container">
        <div class="huk-perfil-grid">
            <?= $this->render('_lateral', ['inversor' => $inversor, 'tieneFoto' => $tieneFoto]) ?>

            <div class="huk-perfil-main">
                <?= $this->render('../_aviso') ?>

                <nav class="huk-perfil-tabs" role="tablist" aria-label="Secciones de Mi cuenta">
                    <?php foreach ($pestanas as $id => [$icono, $texto]): ?>
                        <button type="button" role="tab" id="tab-<?= $id ?>" class="huk-perfil-tab<?= $id === $tab ? ' active' : '' ?>"
                                data-bs-toggle="tab" data-bs-target="#panel-<?= $id ?>" data-tab="<?= $id ?>"
                                aria-controls="panel-<?= $id ?>" aria-selected="<?= $id === $tab ? 'true' : 'false' ?>">
                            <i class="fa-solid <?= $icono ?>" aria-hidden="true"></i><span><?= $texto ?></span>
                        </button>
                    <?php endforeach; ?>
                </nav>

                <div class="tab-content">
                    <?php foreach (array_keys($pestanas) as $id): ?>
                        <div class="tab-pane fade<?= $id === $tab ? ' show active' : '' ?>" id="panel-<?= $id ?>" role="tabpanel" aria-labelledby="tab-<?= $id ?>" tabindex="0">
                            <?= $this->render('_' . $id, compact('inversor', 'nda', 'terminos', 'proyectos', 'pendientes', 'disponibles', 'inversiones', 'formDatos', 'formClave', 'intereses')) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
