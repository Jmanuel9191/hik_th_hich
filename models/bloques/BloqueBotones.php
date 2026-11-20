<?php

namespace app\models\bloques;

/** Uno o dos botones (clase .btn de la referencia). */
class BloqueBotones extends Bloque
{
    const TIPO = 'botones';
    const NOMBRE = 'Botones';
    const DESCRIPCION = 'Uno o dos botones con enlace.';
    const ICONO = 'hand-pointer-o';

    const ESTILOS = ['dorado' => 'Dorado', 'negro' => 'Negro', 'contorno' => 'Solo borde'];

    public static function campos(): array
    {
        return [
            'botones' => ['tipo' => 'lista', 'etiqueta' => 'Botones', 'item' => 'Botón', 'min' => 1, 'maxItems' => 2, 'campos' => [
                'texto' => ['tipo' => 'texto', 'etiqueta' => 'Texto del botón', 'requerido' => true, 'max' => 40],
                'enlace' => ['tipo' => 'enlace', 'etiqueta' => 'Enlace', 'requerido' => true, 'max' => 300,
                    'placeholder' => 'https://… · /contacto · #documentos'],
                'estilo' => ['tipo' => 'opciones', 'etiqueta' => 'Estilo', 'opciones' => self::ESTILOS],
            ]],
        ];
    }
}
