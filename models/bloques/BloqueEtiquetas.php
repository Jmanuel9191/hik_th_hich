<?php

namespace app\models\bloques;

/** Etiquetas en cápsulas (clases .tags / .light-tags de la referencia). Hover dorado. */
class BloqueEtiquetas extends Bloque
{
    const TIPO = 'etiquetas';
    const NOMBRE = 'Etiquetas';
    const DESCRIPCION = 'Palabras en cápsulas (parámetros, temas, entregables).';
    const GRUPO = 'datos';
    const ICONO = 'tags';

    public static function campos(): array
    {
        return [
            'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Antetítulo (opcional)', 'max' => 100, 'placeholder' => 'Parámetros a determinar'],
            'etiquetas' => ['tipo' => 'lineas', 'etiqueta' => 'Etiquetas (una por línea)', 'requerido' => true, 'max' => 1500],
        ];
    }
}
