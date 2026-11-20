<?php

namespace app\models\bloques;

/** Texto con formato: negrita, cursiva, listas, enlaces y subtítulos (clase .ptxt de la referencia). */
class BloqueTexto extends Bloque
{
    const TIPO = 'texto';
    const NOMBRE = 'Texto';
    const DESCRIPCION = 'Párrafos con negrita, listas y enlaces.';
    const ICONO = 'align-left';

    public static function campos(): array
    {
        return [
            'texto' => ['tipo' => 'html', 'etiqueta' => 'Texto', 'requerido' => true],
        ];
    }
}
