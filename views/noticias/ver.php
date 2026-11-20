<?php

/**
 * Noticia del Blog: encabezado (banner del Blog con la categoría en un cuadrito y el título de la noticia),
 * barra lateral a la izquierda y, a la derecha: la imagen (la de contenido o la portada) o, si tiene galería,
 * el carrusel de fotos (huk.js punto 15); la descripción completa y los botones para compartir.
 * Para compartir y para Google: descripción = resumen, imagen = portada (widgets/vista_previa).
 * Galería y compartir: widgets/galeria_fotos y widgets/compartir (los mismos de los ecosistemas).
 *
 * @var yii\web\View $this
 * @var app\models\Noticia $noticia
 * @var app\models\Banners|null $encabezado
 * @var array $lateral
 */

use yii\helpers\Html;
use yii\helpers\Url;

$n = $noticia;
$this->title = $n->titulo;
$enlace = Url::to(['/noticias/ver', 'slug' => $n->slug], true);
$portada = Url::to($n->url($n->imagen_portada), true);

$fotos = array_map(static fn ($img) => $n->url('galeria/' . $img->archivo), $n->imagenes);
?>

<?= $this->render('//site/widgets/vista_previa', [
    'titulo' => $n->titulo, 'descripcion' => $n->resumen, 'enlace' => $enlace, 'imagen' => $portada, 'tipo' => 'article',
    'extra' => ['article:published_time' => $n->fecha, 'article:section' => $n->categoria->nombre ?? ''],
]) ?>

<?= $this->render('//site/widgets/encabezado', [
    'banner' => $encabezado,
    'titulo' => $n->titulo,
    'tituloDelBanner' => false,
    'etiqueta' => $n->categoria->nombre ?? null,
    'migas' => array_values(array_filter([
        ['label' => 'Inicio', 'url' => ['/site/index']],
        ['label' => 'Blog', 'url' => ['/noticias/index']],
        $n->categoria ? ['label' => $n->categoria->nombre, 'url' => ['/noticias/index', 'categoria' => $n->categoria->slug]] : null,
        ['label' => 'Noticia'],
    ])),
]) ?>

<section class="huk-blog" aria-label="Noticia">
    <div class="container huk-blog-grid">
        <?= $this->render('_lateral', ['lateral' => $lateral, 'busqueda' => '', 'categoriaActual' => $n->categoria->slug ?? null, 'actual' => $n->idnoticia]) ?>

        <article class="huk-blog-principal huk-noticia">
            <?php if ($fotos): ?>
                <?= $this->render('//site/widgets/galeria_fotos', ['fotos' => $fotos, 'alt' => $n->titulo]) ?>
            <?php else: ?>
                <figure class="huk-noticia-imagen" data-reveal="foto">
                    <?= Html::img($n->imagenPrincipal(), ['alt' => $n->titulo]) ?>
                </figure>
            <?php endif; ?>

            <div class="huk-noticia-texto" data-reveal><?= $n->descripcion /* HTML limpio (HtmlEditor) */ ?></div>

            <?= $this->render('//site/widgets/compartir', ['enlace' => $enlace, 'titulo' => $n->titulo, 'etiqueta' => 'Compartir']) ?>

            <?= Html::a('<i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver al blog', ['/noticias/index'], ['class' => 'huk-noticia-volver']) ?>
        </article>
    </div>
</section>
