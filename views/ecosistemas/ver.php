<?php

/**
 * Página de un ecosistema (/ecosistemas/<slug>). Orden:
 *  banner (ícono, número, etiquetas) → imagen (galería si tiene; si no, foto de contenido; si no, portada) →
 *  alcance (enfoque + presencia en la expansión) y descripción → compartir → pasos "Del origen a la operación" →
 *  proyectos destacados en carrusel (si el inversor tiene acceso; si no, el llamado a ver proyectos) → oportunidades (si está marcado) → otros ecosistemas / anterior-siguiente → prefooter.
 * Estilos: huk.css sección 27. Galería y compartir: los mismos widgets del Blog.
 *
 * @var yii\web\View $this
 * @var app\models\Ecosistema $ecosistema
 * @var app\models\Ecosistema[] $todos
 * @var int $posicion
 * @var app\models\Ecosistema|null $anterior
 * @var app\models\Ecosistema|null $siguiente
 * @var app\models\Banners|null $encabezado
 * @var app\models\PaginaSeccion|null $modelo
 * @var array $paises  código => enfoque
 */

use app\widgets\ProyectosEcosistema;
use app\widgets\Prefooter;
use yii\helpers\Html;
use yii\helpers\Url;

$e = $ecosistema;
$this->title = $e->nombre . ' · Ecosistemas';
$enlace = Url::to(['/ecosistemas/ver', 'slug' => $e->slug], true);
$fotos = array_map(static fn ($img) => $e->url('galeria/' . $img->archivo), $e->imagenes);
$imagen = $e->foto_contenido ?: $e->foto_portada;
$portada = $e->foto_portada ?: ($e->foto_banner ?: null);
?>

<?= $this->render('//site/widgets/vista_previa', [
    'titulo' => $e->nombre . ($e->subtitulo ? ' · ' . $e->subtitulo : ''),
    'descripcion' => $e->resumen,
    'enlace' => $enlace,
    'imagen' => $portada ? Url::to($e->url($portada), true) : null,
    'tipo' => 'website',
]) ?>

<?= $this->render('_banner', ['ecosistema' => $e, 'encabezado' => $encabezado, 'posicion' => $posicion, 'total' => count($todos)]) ?>

<?php if ($fotos || $imagen): ?>
    <section class="huk-eco-media" aria-label="Imágenes de <?= Html::encode($e->nombre) ?>">
        <div class="container">
            <?php if ($fotos): ?>
                <?= $this->render('//site/widgets/galeria_fotos', ['fotos' => $fotos, 'alt' => $e->nombre]) ?>
            <?php else: ?>
                <figure class="huk-noticia-imagen huk-eco-imagen" data-reveal="foto">
                    <?= Html::img($e->url($imagen), ['alt' => $e->nombre]) ?>
                </figure>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?= $this->render('_alcance', ['ecosistema' => $e, 'paises' => $paises]) ?>

<section class="huk-eco-compartir" aria-label="Compartir">
    <div class="container">
        <?= $this->render('//site/widgets/compartir', ['enlace' => $enlace, 'titulo' => $e->nombre, 'etiqueta' => 'Compartir este ecosistema']) ?>
    </div>
</section>

<?= $this->render('//site/widgets/modelo_valor', ['modelo' => $modelo, 'soloPasos' => true, 'etiqueta' => 'Cómo participamos', 'titulo' => 'Del origen a la operación']) ?>

<?= ProyectosEcosistema::widget(['ecosistema' => $e]) ?>

<?= $this->render('_oportunidades', ['ecosistema' => $e]) ?>

<?= $this->render('_navegacion', ['ecosistema' => $e, 'todos' => $todos, 'anterior' => $anterior, 'siguiente' => $siguiente]) ?>

