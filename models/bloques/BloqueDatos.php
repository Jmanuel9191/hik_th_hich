<?php

namespace app\models\bloques;

/** Datos clave: pares rótulo / valor en recuadros (clase .facts de la referencia). */
class BloqueDatos extends Bloque
{
    const TIPO = 'datos';
    const NOMBRE = 'Datos clave';
    const DESCRIPCION = 'Pares de rótulo y valor en recuadros (Región: Vilaque…).';
    const GRUPO = 'datos';
    const ICONO = 'th-list';

    public static function campos(): array
    {
        return [
            'datos' => ['tipo' => 'lista', 'etiqueta' => 'Datos', 'item' => 'Dato', 'min' => 1, 'maxItems' => 8, 'campos' => [
                'rotulo' => ['tipo' => 'texto', 'etiqueta' => 'Rótulo', 'requerido' => true, 'max' => 40, 'placeholder' => 'Región'],
                'valor' => ['tipo' => 'texto', 'etiqueta' => 'Valor', 'requerido' => true, 'max' => 120, 'placeholder' => 'Vilaque'],
            ]],
        ];
    }
}
