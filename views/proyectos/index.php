<?php

/**
 * Proyectos del inversor (/proyectos): encabezado (Panel → Banners → Proyectos), barra lateral a la izquierda
 * (buscar y ecosistemas de sus áreas) y las tarjetas de a 3 (2 en tablet, 1 en celular), 9 por página.
 * Mismo esquema que el Blog. Estilos: huk.css sección 28.
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\Ecosistema|null $ecosistema
 * @var string $busqueda
 * @var app\models\Banners|null $encabezado
 * @var array $lateral [[Ecosistema, cantidad]]
 * @var app\models\Inversor $inversor
 */

use yii\helpers\Html;
use yii\widgets\LinkPager;

$this->title = $ecosistema ? $ecosistema->nombre . ' · Proyectos' : 'Proyectos';
$proyectos = $dataProvider->getModels();
$migas = [['label' => 'Inicio', 'url' => ['/site/index']], ['label' => 'Proyectos', 'url' => ['/proyectos/index']]];
if ($ecosistema) {
    $migas[] = ['label' => $ecosistema->nombre];
}
$efectos = ['left', '', 'right'];
$filtrado = $ecosistema || $busqueda !== '';
?>

<?= $this->render('//site/widgets/encabezado', ['banner' => $encabezado, 'titulo' => 'Proyectos', 'migas' => $migas]) ?>

<section class="huk-blog huk-proys" aria-label="Proyectos">
    <div class="container huk-blog-grid">
        <?= $this->render('_lateral', ['lateral' => $lateral, 'busqueda' => $busqueda, 'actual' => $ecosistema->slug ?? null]) ?>

        <div class="huk-blog-principal">
            <div class="huk-proys-intro" data-reveal>
                <p class="huk-nos-etiqueta">Hola, <?= Html::encode(strtok(trim((string) ($inversor->usuario->nombrecompleto ?? '')), ' ') ?: 'inversor') ?></p>
                <p>Estos son los proyectos de sus áreas. La información es <b>confidencial</b> y está alcanzada por el acuerdo que aceptó.</p>
            </div>

            <?php if ($filtrado): ?>
                <div class="huk-blog-filtro" data-reveal>
                    <span>
                        <?= $ecosistema ? 'Ecosistema: <b>' . Html::encode($ecosistema->nombre) . '</b>' : '' ?>
                        <?= $busqueda !== '' ? ($ecosistema ? ' · ' : '') . 'Resultados para <b>“' . Html::encode($busqueda) . '”</b>' : '' ?>
                        (<?= $dataProvider->getTotalCount() ?>)
                    </span>
                    <?= Html::a('<i class="fa-solid fa-xmark" aria-hidden="true"></i> Ver todos', ['/proyectos/index']) ?>
                </div>
            <?php endif; ?>

            <?php if ($proyectos): ?>
                <div class="huk-proys-grilla">
                    <?php foreach ($proyectos as $i => $p): ?>
                        <?= $this->render('//site/widgets/proyecto_tarjeta', ['proyecto' => $p, 'efecto' => $efectos[$i % 3], 'retraso' => ($i % 3) * 120]) ?>
                    <?php endforeach; ?>
                </div>
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->getPagination(),
                    'options' => ['class' => 'huk-paginador', 'aria-label' => 'Páginas de proyectos'],
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
                    <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
                    <p><?= $filtrado ? 'No encontramos proyectos con ese filtro.' : 'Todavía no hay proyectos publicados en sus áreas. Le avisaremos cuando haya.' ?></p>
                    <?php if ($filtrado): ?>
                        <?= Html::a('Ver todos los proyectos', ['/proyectos/index'], ['class' => 'btn btn-primary']) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
