<?php

namespace app\models\bloques;

/** Tarjetas con foto (clases .geo3 / .las de la referencia): foto opcional, número (8.1), título, texto y pie. */
class BloqueTarjetasFoto extends Bloque
{
    const TIPO = 'tarjetas_foto';
    const NOMBRE = 'Tarjetas con foto';
    const DESCRIPCION = 'Foto, número, título, texto y pie; de a 2, 3 o 4 por fila.';
    const GRUPO = 'tarjetas';
    const ICONO = 'id-card-o';

    public static function campos(): array
    {
        return [
            'por_fila' => ['tipo' => 'opciones', 'etiqueta' => 'Tarjetas por fila', 'opciones' => BloqueTarjetas::POR_FILA],
            'tarjetas' => ['tipo' => 'lista', 'etiqueta' => 'Tarjetas', 'item' => 'Tarjeta', 'min' => 1, 'maxItems' => 12, 'campos' => [
                'imagen' => ['tipo' => 'imagen', 'etiqueta' => 'Foto (opcional)'],
                'alt' => ['tipo' => 'texto', 'etiqueta' => 'Qué muestra la foto', 'max' => 150],
                'numero' => ['tipo' => 'texto', 'etiqueta' => 'Número (opcional)', 'max' => 10, 'placeholder' => '8.1'],
                'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título', 'requerido' => true, 'max' => 100],
                'texto' => ['tipo' => 'parrafo', 'etiqueta' => 'Texto', 'max' => 800],
                'pie' => ['tipo' => 'texto', 'etiqueta' => 'Pie de foto (opcional)', 'max' => 200, 'placeholder' => 'Imagen de referencia: …'],
            ]],
        ];
    }
}
