<?php

namespace app\models\bloques;

/** Dos aportes (clase .give de la referencia): caja dorada y caja negra, cada una con su lista. */
class BloqueDosAportes extends Bloque
{
    const TIPO = 'dos_aportes';
    const NOMBRE = 'Dos aportes';
    const DESCRIPCION = 'Caja dorada y caja negra con viñetas (qué aporta cada parte).';
    const GRUPO = 'pasos';
    const ICONO = 'exchange';

    public static function campos(): array
    {
        return [
            'a_titulo' => ['tipo' => 'texto', 'etiqueta' => 'Caja dorada: título', 'requerido' => true, 'max' => 80, 'placeholder' => 'Aporta HUK–THE–ICHI'],
            'a_items' => ['tipo' => 'lineas', 'etiqueta' => 'Caja dorada: viñetas (una por línea)', 'requerido' => true, 'max' => 1200],
            'b_titulo' => ['tipo' => 'texto', 'etiqueta' => 'Caja negra: título', 'requerido' => true, 'max' => 80, 'placeholder' => 'Aporta el socio estratégico'],
            'b_items' => ['tipo' => 'lineas', 'etiqueta' => 'Caja negra: viñetas (una por línea)', 'requerido' => true, 'max' => 1200],
        ];
    }
}
