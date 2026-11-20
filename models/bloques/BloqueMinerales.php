<?php

namespace app\models\bloques;

/** Minerales o elementos (clase .minerals de la referencia): foto cuadrada, número atómico, símbolo dorado y nombre. */
class BloqueMinerales extends Bloque
{
    const TIPO = 'minerales';
    const NOMBRE = 'Minerales';
    const DESCRIPCION = 'Foto, símbolo grande (Au), número atómico y nombre.';
    const GRUPO = 'tarjetas';
    const ICONO = 'diamond';

    public static function campos(): array
    {
        return [
            'minerales' => ['tipo' => 'lista', 'etiqueta' => 'Minerales', 'item' => 'Mineral', 'min' => 1, 'maxItems' => 8, 'campos' => [
                'imagen' => ['tipo' => 'imagen', 'etiqueta' => 'Foto', 'requerido' => true],
                'numero' => ['tipo' => 'texto', 'etiqueta' => 'N.º atómico', 'max' => 3, 'placeholder' => '79'],
                'simbolo' => ['tipo' => 'texto', 'etiqueta' => 'Símbolo', 'requerido' => true, 'max' => 4, 'placeholder' => 'Au'],
                'nombre' => ['tipo' => 'texto', 'etiqueta' => 'Nombre', 'requerido' => true, 'max' => 30, 'placeholder' => 'Oro'],
                'pie' => ['tipo' => 'texto', 'etiqueta' => 'Pie (opcional)', 'max' => 200, 'placeholder' => 'Pepita de oro nativo (Australia).'],
            ]],
        ];
    }
}
