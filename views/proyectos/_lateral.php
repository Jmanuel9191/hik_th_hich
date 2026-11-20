<?php

/**
 * Barra lateral de Proyectos (como la del Blog): buscar y los ecosistemas del inversor con la cantidad de proyectos.
 * En computadora queda fija al bajar; en celular va debajo.
 *
 * @var yii\web\View $this
 * @var array $lateral [[Ecosistema, cantidad]]
 * @var string $busqueda
 * @var string|null $actual slug del ecosistema elegido
 */

use yii\helpers\Html;
?>
<aside class="huk-blog-lateral" aria-label="Proyectos: buscar y ecosistemas">
    <div class="huk-blog-caja" data-reveal="left">
        <h2>Buscar</h2>
        <?= Html::beginForm($actual ? ['/proyectos/index', 'ecosistema' => $actual] : ['/proyectos/index'], 'get', ['class' => 'huk-blog-buscar', 'role' => 'search']) ?>
            <label class="visually-hidden" for="proyectos-q">Buscar proyectos</label>
            <input id="proyectos-q" type="search" name="q" value="<?= Html::encode($busqueda) ?>" placeholder="Nombre, ciudad, país…" maxlength="100">
            <button type="submit" aria-label="Buscar"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
        <?= Html::endForm() ?>
    </div>

    <?php if ($lateral): ?>
        <div class="huk-blog-caja" data-reveal="left" data-reveal-delay="120">
            <h2>Ecosistemas</h2>
            <ul class="huk-blog-categorias">
                <li class="<?= $actual === null ? 'is-activa' : '' ?>">
                    <?= Html::a('<span>Todos</span><b>' . array_sum(array_column($lateral, 1)) . '</b>', ['/proyectos/index']) ?>
                </li>
                <?php foreach ($lateral as [$eco, $n]): ?>
                    <li class="<?= $actual === $eco->slug ? 'is-activa' : '' ?>">
                        <?= Html::a('<span>' . Html::encode($eco->nombre) . '</span><b>' . $n . '</b>', ['/proyectos/index', 'ecosistema' => $eco->slug]) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="huk-blog-caja huk-proys-confidencial" data-reveal="left" data-reveal-delay="240">
        <i class="fa-solid fa-lock" aria-hidden="true"></i>
        <p>Material confidencial. Solo lo ven los inversores aprobados de cada área. Las descargas quedan registradas.</p>
    </div>
</aside>
