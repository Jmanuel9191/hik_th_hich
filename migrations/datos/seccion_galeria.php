<?php

/**
 * Sección "Galería y documentos" de las plantillas (antes del Aviso). Sus tres bloques toman lo que se sube en las
 * pestañas Fotos, Videos y Documentos del proyecto; si una pestaña está vacía, ese bloque no aparece en la web.
 * La usan migrations/datos/plantillas_proyecto.php y m261120_030000_paq_b (para las plantillas que ya existen).
 */
return [
    'antetitulo' => 'Material del proyecto', 'titulo' => 'Galería y documentos', 'menu' => 'Galería', 'ancla' => 'galeria',
    'fondo' => 'gris', 'diseno' => 'arriba', 'en_menu' => true, 'numerada' => false, 'bloques' => [
        ['galeria_proyecto', 'completo', ['titulo' => 'Galería de fotos', 'mostrar' => 'todas', 'fotos' => []]],
        ['videos', 'completo', ['titulo' => 'Videos', 'mostrar' => 'todos', 'videos' => [], 'enlaces' => []]],
        ['documentos', 'completo', ['titulo' => 'Documentos del proyecto', 'texto' => '', 'mostrar' => 'todos', 'documentos' => []]],
    ],
];
