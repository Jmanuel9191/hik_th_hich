<?php

namespace app\models\bloques;

/** Aviso con título en mayúsculas sobre fondo gris y borde dorado (clase .notice de la referencia). */
class BloqueAviso extends Bloque
{
    const TIPO = 'aviso';
    const NOMBRE = 'Aviso';
    const DESCRIPCION = 'Aviso legal o advertencia importante.';
    const ICONO = 'exclamation-triangle';

    public static function campos(): array
    {
        return [
            'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título', 'max' => 60, 'defecto' => 'Aviso importante'],
            'texto' => ['tipo' => 'parrafo', 'etiqueta' => 'Texto', 'requerido' => true, 'max' => 1500],
        ];
    }
}
