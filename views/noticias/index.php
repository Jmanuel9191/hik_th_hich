<?php

/**
 * Blog: encabezado (Panel → Banners → Blog), barra lateral a la izquierda y las noticias de a 3 por fila
 * (2 en tablet, 1 en celular), 12 por página con el paginador centrado debajo. Estilos: huk.css sección 25.
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\NoticiaCategoria|null $categoria
 * @var string $busqueda
 * @var app\models\Banners|null $encabezado
 * @var array $lateral
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = $categoria ? $categoria->nombre . ' · Blog' : 'Blog';
$noticias = $dataProvider->getModels();
$migas = [['label' => 'Inicio', 'url' => ['/site/index']], ['label' => 'Blog', 'url' => ['/noticias/index']]];
if ($categoria) {
    $migas[] = ['label' => $categoria->nombre];
}
$efectos = ['left', '', 'right'];
?>

<?= $this->render('//site/widgets/encabezado', ['banner' => $encabezado, 'titulo' => 'Blog', 'migas' => $migas]) ?>

<section class="huk-blog" aria-label="Noticias">
    <div class="container huk-blog-grid">
        <?= $this->render('_lateral', ['lateral' => $lateral, 'busqueda' => $busqueda, 'categoriaActual' => $categoria->slug ?? null]) ?>

        <div class="huk-blog-principal">
            <?php if ($categoria || $busqueda !== ''): ?>
                <div class="huk-blog-filtro" data-reveal>
                    <span>
                        <?= $categoria ? 'Categoría: <b>' . Html::encode($categoria->nombre) . '</b>' : '' ?>
                        <?= $busqueda !== '' ? ($categoria ? ' · ' : '') . 'Resultados para <b>“' . Html::encode($busqueda) . '”</b>' : '' ?>
                        (<?= $dataProvider->getTotalCount() ?>)
                    </span>
                    <?= Html::a('<i class="fa-solid fa-xmark" aria-hidden="true"></i> Ver todas', ['/noticias/index']) ?>
                </div>
            <?php endif; ?>

            <?php if ($noticias): ?>
                <div class="huk-blog-noticias">
                    <?php foreach ($noticias as $i => $n): ?>
                        <?= $this->render('//site/widgets/noticia_tarjeta', ['noticia' => $n, 'efecto' => $efectos[$i % 3], 'retraso' => ($i % 3) * 120]) ?>
                    <?php endforeach; ?>
                </div>
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->getPagination(),
                    'options' => ['class' => 'huk-paginador', 'aria-label' => 'Páginas del blog'],
                    'linkContainerOptions' => ['class' => 'huk-paginador-item'],
                    'linkOptions' => ['class' => 'huk-paginador-link'],
                    'disabledListItemSubTagOptions' => ['tag' => 'span', 'class' => 'huk-paginador-link'],
                    'activePageCssClass' => 'is-activa',
                    'disabledPageCssClass' => 'is-desactivada',
                    'prevPageLabel' => '<i class="fa-solid fa-chevron-left" aria-hidden="true"></i><span class="visually-hidden">Anterior</span>',
                    'nextPageLabel' => '<i class="fa-solid fa-chevron-right" aria-hidden="true"></i><span class="visually-hidden">Siguiente</span>',
                    'maxButtonCount' => 5,
                ]) ?>
            <?php else: ?>
                <div class="huk-blog-vacio" data-reveal="zoom">
                    <i class="fa-regular fa-newspaper" aria-hidden="true"></i>
                    <p><?= $busqueda !== '' || $categoria ? 'No encontramos noticias con ese filtro.' : 'Pronto encontrará aquí nuestras noticias.' ?></p>
                    <?php if ($busqueda !== '' || $categoria): ?>
                        <?= Html::a('Ver todas las noticias', ['/noticias/index'], ['class' => 'btn btn-primary']) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
