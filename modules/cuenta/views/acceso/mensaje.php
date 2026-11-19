<?php

/**
 * Página de aviso de la cuenta (correo enviado, correo confirmado, enlace inválido…).
 *
 * @var yii\web\View $this
 * @var string $icono    ícono Font Awesome (sin "fa-")
 * @var string $titulo
 * @var string $html     texto (ya escapado donde corresponde)
 * @var string|null $nota
 * @var bool $reenviar   muestra "reenviar confirmación"
 * @var string|null $email
 * @var array|null $boton [texto, ruta]
 */

use yii\helpers\Html;

\app\assets_b\CuentaAsset::register($this);
?>

<section class="huk-cuenta">
    <div class="container">
        <div class="huk-tarjeta-form is-media huk-mensaje">
            <div class="huk-mensaje-icono"><i class="fa-solid fa-<?= Html::encode($icono) ?>" aria-hidden="true"></i></div>
            <h1><?= Html::encode($titulo) ?></h1>
            <p><?= $html ?></p>
            <?php if ($nota): ?><p class="huk-nota"><?= Html::encode($nota) ?></p><?php endif; ?>
            <?php if ($reenviar): ?>
                <?= Html::beginForm(['/cuenta/acceso/reenviar'], 'post', ['class' => 'huk-reenviar']) ?>
                <?= Html::input('email', 'email', $email, ['class' => 'form-control', 'placeholder' => 'Tu correo', 'required' => true, 'aria-label' => 'Tu correo']) ?>
                <?= Html::submitButton('Reenviar', ['class' => 'btn btn-outline-dark']) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>
            <?php if ($boton): ?>
                <p class="mt-4"><?= Html::a(Html::encode($boton[0]), $boton[1], ['class' => 'huk-btn-oro']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
