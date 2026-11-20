<?php

namespace app\models\bloques;

/** Recuadro negro con borde dorado: antetítulo y una frase fuerte (clase .thesis de la referencia). */
class BloqueRecuadro extends Bloque
{
    const TIPO = 'recuadro';
    const NOMBRE = 'Recuadro negro';
    const DESCRIPCION = 'Una idea fuerte en un recuadro negro (ej. la tesis de inversión).';
    const ICONO = 'square';

    public static function campos(): array
    {
        return [
            'antetitulo' => ['tipo' => 'texto', 'etiqueta' => 'Antetítulo', 'max' => 60, 'placeholder' => 'Tesis de inversión'],
            'texto' => ['tipo' => 'parrafo', 'etiqueta' => 'Texto', 'requerido' => true, 'max' => 700],
        ];
    }
}
