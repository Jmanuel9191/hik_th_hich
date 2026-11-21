<?php

/**
 * Columna izquierda de Mi cuenta: foto (se cambia tocándola), nombre, estado y datos de contacto.
 *
 * @var yii\web\View $this
 * @var app\models\Inversor $inversor
 * @var bool $tieneFoto
 */

use app\models\Inversor;
use yii\helpers\Html;

$u = $inversor->usuario;
$fecha = fn($f) => $f ? Yii::$app->formatter->asDate($f, 'php:d/m/Y') : '—';
?>

<aside class="huk-perfil-lateral">
    <div class="huk-perfil-card">
        <?= Html::beginForm(['/cuenta/panel/foto-subir'], 'post', ['enctype' => 'multipart/form-data', 'class' => 'huk-perfil-foto-form', 'data-foto-form' => true]) ?>
        <label class="huk-perfil-avatar<?= $tieneFoto ? ' has-foto' : '' ?>" title="Cambiar foto">
            <?php if ($tieneFoto): ?>
                <img src="<?= Html::encode(\yii\helpers\Url::to(['/cuenta/panel/foto', 'v' => substr(md5((string) $u->foto_perfil), 0, 8)])) ?>"
                     alt="Foto de <?= Html::encode($u->nombrecompleto) ?>" width="132" height="132" data-foto-img>
            <?php else: ?>
                <span class="huk-perfil-iniciales" data-foto-iniciales><?= Html::encode($inversor->iniciales()) ?></span>
            <?php endif; ?>
            <span class="huk-perfil-avatar-btn"><i class="fa-solid fa-camera" aria-hidden="true"></i><span class="visually-hidden">Cambiar foto</span></span>
            <?= Html::fileInput('foto', null, ['accept' => 'image/jpeg,image/png,image/webp', 'class' => 'visually-hidden', 'data-foto-input' => true]) ?>
        </label>
        <?= Html::endForm() ?>
        <p class="huk-perfil-foto-ayuda" data-foto-ayuda>JPG, PNG o WEBP · hasta 8 MB</p>
        <?php if ($tieneFoto): ?>
            <?= Html::beginForm(['/cuenta/panel/foto-quitar'], 'post', ['class' => 'huk-perfil-foto-quitar']) ?>
            <?= Html::submitButton('Quitar foto', ['class' => 'btn btn-link']) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>

        <h1 class="huk-perfil-nombre notranslate" translate="no"><?= Html::encode($u->nombrecompleto) ?></h1>
        <p class="huk-perfil-tipo">
            <?= Html::encode(Inversor::TIPOS[$inversor->tipo] ?? $inversor->tipo) ?>
            <?php if ($inversor->tipo === 'empresa' && $inversor->razon_social): ?> · <?= Html::encode($inversor->razon_social) ?><?php endif; ?>
        </p>
        <span class="huk-perfil-estado is-<?= Html::encode($inversor->estado_solicitud) ?>">
            <i class="fa-solid fa-circle" aria-hidden="true"></i>
            <?= $inversor->estado_solicitud === 'aprobado' ? 'Inversor aprobado' : Html::encode($inversor->estadoNombre()) ?>
        </span>

        <ul class="huk-perfil-datos notranslate" translate="no">
            <li><i class="fa-solid fa-envelope" aria-hidden="true"></i><span><?= Html::encode($u->email) ?></span></li>
            <li><i class="fa-solid fa-mobile-screen" aria-hidden="true"></i><span><?= Html::encode($u->movil ?: $u->telefono ?: '—') ?></span></li>
            <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><?= Html::encode(trim($u->ciudad . ', ' . $u->pais, ', ') ?: '—') ?></span></li>
            <li><i class="fa-solid fa-calendar-check" aria-hidden="true"></i><span>Miembro desde <?= $fecha($inversor->fecha_registro) ?></span></li>
        </ul>

        <?= Html::beginForm(['/cuenta/acceso/salir'], 'post', ['class' => 'huk-perfil-salir']) ?>
        <?= Html::submitButton('<i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Cerrar sesión', ['class' => 'huk-btn-linea w-100']) ?>
        <?= Html::endForm() ?>
    </div>
</aside>
