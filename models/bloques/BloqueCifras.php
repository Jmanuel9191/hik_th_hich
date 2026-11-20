<?php

namespace app\models\bloques;

/** Cifras grandes que cuentan solas al aparecer (clases .pstats / .kv de la referencia). */
class BloqueCifras extends Bloque
{
    const TIPO = 'cifras';
    const NOMBRE = 'Cifras';
    const DESCRIPCION = 'Números grandes con su texto; cuentan solos al aparecer.';
    const GRUPO = 'datos';
    const ICONO = 'sort-numeric-asc';

    const ESTILOS = ['fila' => 'En fila, con línea arriba', 'grilla' => 'De a dos, con líneas'];

    public static function campos(): array
    {
        return [
            'estilo' => ['tipo' => 'opciones', 'etiqueta' => 'Diseño', 'opciones' => self::ESTILOS],
            'cifras' => ['tipo' => 'lista', 'etiqueta' => 'Cifras', 'item' => 'Cifra', 'min' => 1, 'maxItems' => 6, 'campos' => [
                'valor' => ['tipo' => 'texto', 'etiqueta' => 'Número', 'requerido' => true, 'max' => 16, 'placeholder' => '≈ 1 km'],
                'texto' => ['tipo' => 'texto', 'etiqueta' => 'Texto', 'requerido' => true, 'max' => 80, 'placeholder' => 'de llanura aluvial'],
                'contar' => ['tipo' => 'casilla', 'etiqueta' => 'Contar al aparecer (no en fechas)', 'defecto' => true],
            ]],
        ];
    }
}
