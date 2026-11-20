<?php

/**
 * Etiquetas para Google y para la vista previa al compartir (Open Graph y Twitter) + enlace canónico.
 * Reutilizable: noticias y ecosistemas. La descripción también reemplaza la meta "description" del layout.
 *
 * @var yii\web\View $this
 * @var string $titulo
 * @var string $descripcion
 * @var string $enlace   URL completa de la página
 * @var string|null $imagen  URL completa de la imagen (portada)
 * @var string $tipo     'article' o 'website'
 * @var array  $extra    otras propiedades og/article (ej. ['article:section' => 'Minería'])
 */

$this->params['descripcion'] = $descripcion;
$this->registerLinkTag(['rel' => 'canonical', 'href' => $enlace]);
$propiedades = array_filter([
    'og:type' => $tipo ?? 'website',
    'og:title' => $titulo,
    'og:description' => $descripcion,
    'og:image' => $imagen ?? null,
    'og:url' => $enlace,
    'og:locale' => 'es_ES',
] + ($extra ?? []), static fn ($v) => $v !== null && $v !== '');
foreach ($propiedades as $propiedad => $valor) {
    $this->registerMetaTag(['property' => $propiedad, 'content' => $valor], $propiedad);
}
$twitter = array_filter(['twitter:card' => !empty($imagen) ? 'summary_large_image' : 'summary', 'twitter:title' => $titulo, 'twitter:description' => $descripcion, 'twitter:image' => $imagen ?? null]);
foreach ($twitter as $nombre => $valor) {
    $this->registerMetaTag(['name' => $nombre, 'content' => $valor], $nombre);
}
