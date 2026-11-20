<?php

namespace app\models\bloques;

/** Fotos chicas de a 2, 3 o 4 por fila, cada una con su pie (clase .photos3 de la referencia). */
class BloqueGaleria extends Bloque
{
    const TIPO = 'galeria';
    const NOMBRE = 'Galería chica';
    const DESCRIPCION = 'De 2 a 12 fotos, de a 2, 3 o 4 por fila, con su pie.';
    const ICONO = 'th';

    public static function campos(): array
    {
        return [
            'por_fila' => ['tipo' => 'opciones', 'etiqueta' => 'Fotos por fila', 'opciones' => ['3' => '3 por fila', '2' => '2 por fila', '4' => '4 por fila']],
            'fotos' => ['tipo' => 'lista', 'etiqueta' => 'Fotos', 'item' => 'Foto', 'min' => 2, 'maxItems' => 12, 'campos' => [
                'imagen' => ['tipo' => 'imagen', 'etiqueta' => 'Foto', 'requerido' => true],
                'alt' => ['tipo' => 'texto', 'etiqueta' => 'Qué muestra', 'max' => 150],
                'pie' => ['tipo' => 'texto', 'etiqueta' => 'Pie', 'max' => 200],
            ]],
        ];
    }
}
