<?php

/**
 * Página de un ecosistema · Otros ecosistemas (botones con ícono, número y nombre; el actual en negro)
 * y "Anterior / Siguiente" (dan la vuelta). Con las flechas ← → del teclado también se pasa (huk.js punto 16).
 *
 * @var yii\web\View $this
 * @var app\models\Ecosistema $ecosistema
 * @var app\models\Ecosistema[] $todos
 * @var app\models\Ecosistema|null $anterior
 * @var app\models\Ecosistema|null $siguiente
 */

use yii\helpers\Html;
use yii\helpers\Url;

if (count($todos) < 2) {
    return;
}
?>
<section class="huk-eco-nav" aria-label="Otros ecosistemas">
    <div class="container">
        <p class="huk-eco-nav-label" data-reveal>Otros ecosistemas</p>
        <ul class="huk-eco-chips">
            <?php foreach ($todos as $i => $o): ?>
                <li data-reveal="nada" data-reveal-delay="<?= min($i, 10) * 50 ?>">
                    <a href="<?= Url::to(['/ecosistemas/ver', 'slug' => $o->slug]) ?>" class="<?= $o->idecosistema === $ecosistema->idecosistema ? 'is-actual' : '' ?>"
                        <?= $o->idecosistema === $ecosistema->idecosistema ? 'aria-current="page"' : '' ?>>
                        <?php if ($o->icono): ?><?= Html::img($o->url($o->icono), ['alt' => '', 'width' => 20, 'height' => 20, 'loading' => 'lazy']) ?><?php endif; ?>
                        <b><?= sprintf('%02d', $o->numero) ?></b> <?= Html::encode($o->nombre) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="huk-eco-pasar" data-reveal>
            <a href="<?= Url::to(['/ecosistemas/ver', 'slug' => $anterior->slug]) ?>" class="is-anterior" data-tecla-anterior>
                <small><i class="fa-solid fa-arrow-left-long" aria-hidden="true"></i> Anterior</small>
                <b><?= Html::encode($anterior->nombre) ?></b>
            </a>
            <a href="<?= Url::to(['/ecosistemas/ver', 'slug' => $siguiente->slug]) ?>" class="is-siguiente" data-tecla-siguiente>
                <small>Siguiente <i class="fa-solid fa-arrow-right-long" aria-hidden="true"></i></small>
                <b><?= Html::encode($siguiente->nombre) ?></b>
            </a>
        </div>
        <p class="huk-eco-teclas">Use <kbd>←</kbd> <kbd>→</kbd> para recorrer los ecosistemas</p>
    </div>
</section>
