<?php

namespace app\models\bloques;

/** Párrafo de introducción, más grande y suave (clase .lead de la referencia). */
class BloqueIntroduccion extends Bloque
{
    const TIPO = 'introduccion';
    const NOMBRE = 'Texto de introducción';
    const DESCRIPCION = 'Un párrafo destacado, en letra más grande.';
    const ICONO = 'paragraph';

    public static function campos(): array
    {
        return [
            'texto' => ['tipo' => 'parrafo', 'etiqueta' => 'Texto', 'requerido' => true, 'max' => 1200,
                'ayuda' => 'Para separar en dos párrafos deje una línea en blanco.'],
        ];
    }
}
