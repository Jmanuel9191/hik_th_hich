<?php

namespace app\models\bloques;

/** Nota chica en gris (clase .note de la referencia): aclaraciones, fuentes de un dato. */
class BloqueNota extends Bloque
{
    const TIPO = 'nota';
    const NOMBRE = 'Nota';
    const DESCRIPCION = 'Una aclaración corta, en letra chica.';
    const ICONO = 'sticky-note-o';

    public static function campos(): array
    {
        return [
            'texto' => ['tipo' => 'parrafo', 'etiqueta' => 'Texto', 'requerido' => true, 'max' => 600],
        ];
    }
}
