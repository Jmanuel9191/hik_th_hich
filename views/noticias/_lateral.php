<?php

/**
 * Barra lateral del Blog (a la izquierda): buscar, categorías con la cantidad de noticias y entradas recientes.
 * En computadora queda fija al bajar; en celular va debajo del contenido.
 *
 * @var yii\web\View $this
 * @var array $lateral  NoticiaService::barraLateral(): ['categorias' => [[NoticiaCategoria, n]], 'recientes' => Noticia[]]
 * @var string $busqueda
 * @var string|null $categoriaActual  slug
 * @var int|null $actual  idnoticia que se está leyendo (no se repite en recientes)
 */

use yii\helpers\Html;
use yii\helpers\Url;

$meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
$recientes = array_filter($lateral['recientes'], static fn ($n) => $n->idnoticia !== ($actual ?? null));
?>
<aside class="huk-blog-lateral" aria-label="Blog: buscar y categorías">
    <div class="huk-blog-caja" data-reveal="left">
        <h2>Buscar</h2>
        <?= Html::beginForm(['/noticias/index'], 'get', ['class' => 'huk-blog-buscar', 'role' => 'search']) ?>
            <label class="visually-hidden" for="blog-q">Buscar en el blog</label>
            <input id="blog-q" type="search" name="q" value="<?= Html::encode($busqueda ?? '') ?>" placeholder="Buscar noticias…" maxlength="100">
            <button type="submit" aria-label="Buscar"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
        <?= Html::endForm() ?>
    </div>

    <?php if ($lateral['categorias']): ?>
        <div class="huk-blog-caja" data-reveal="left" data-reveal-delay="120">
            <h2>Categorías</h2>
            <ul class="huk-blog-categorias">
                <?php foreach ($lateral['categorias'] as [$cat, $n]): ?>
                    <li class="<?= ($categoriaActual ?? null) === $cat->slug ? 'is-activa' : '' ?>">
                        <?= Html::a('<span>' . Html::encode($cat->nombre) . '</span><b>' . $n . '</b>', ['/noticias/index', 'categoria' => $cat->slug]) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($recientes): ?>
        <div class="huk-blog-caja" data-reveal="left" data-reveal-delay="240">
            <h2>Entradas recientes</h2>
            <ul class="huk-blog-recientes">
                <?php foreach (array_slice($recientes, 0, 4) as $r): ?>
                    <?php $f = strtotime($r->fecha); ?>
                    <li>
                        <a href="<?= Url::to(['/noticias/ver', 'slug' => $r->slug]) ?>">
                            <?= Html::img($r->url($r->imagen_portada), ['alt' => '', 'loading' => 'lazy', 'width' => 72, 'height' => 72]) ?>
                            <span>
                                <small><?= date('d', $f) . ' ' . $meses[(int) date('n', $f) - 1] . ' ' . date('Y', $f) ?></small>
                                <b><?= Html::encode($r->titulo) ?></b>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</aside>
