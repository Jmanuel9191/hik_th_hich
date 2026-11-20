<?php

namespace app\models\bloques;

/** Ficha destacada (clase .target de la referencia): panel negro con título y hasta 3 cifras, y el texto a la derecha. */
class BloqueFichaDestacada extends Bloque
{
    const TIPO = 'ficha_destacada';
    const NOMBRE = 'Ficha destacada';
    const DESCRIPCION = 'Panel negro con título y 3 cifras, y el texto al lado.';
    const GRUPO = 'tarjetas';
    const ICONO = 'bullseye';

    public static function campos(): array
    {
        return [
            'antetitulo' => ['tipo' => 'texto', 'etiqueta' => 'Antetítulo', 'max' => 60, 'placeholder' => 'Blanco prioritario'],
            'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título del panel', 'requerido' => true, 'max' => 60, 'placeholder' => 'Marisa'],
            'cifras' => ['tipo' => 'lista', 'etiqueta' => 'Cifras del panel', 'item' => 'Cifra', 'maxItems' => 3, 'campos' => [
                'valor' => ['tipo' => 'texto', 'etiqueta' => 'Número', 'requerido' => true, 'max' => 20, 'placeholder' => '≈ 50%'],
                'texto' => ['tipo' => 'texto', 'etiqueta' => 'Texto', 'requerido' => true, 'max' => 100],
            ]],
            'subtitulo' => ['tipo' => 'texto', 'etiqueta' => 'Título del texto', 'max' => 120, 'placeholder' => 'Tres blancos de investigación iniciales'],
            'texto' => ['tipo' => 'html', 'etiqueta' => 'Texto', 'requerido' => true],
        ];
    }
}
