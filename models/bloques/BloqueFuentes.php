<?php

namespace app\models\bloques;

/** Lista de referencias documentales (clase .srcs de la referencia). */
class BloqueFuentes extends Bloque
{
    const TIPO = 'fuentes';
    const NOMBRE = 'Lista de fuentes';
    const DESCRIPCION = 'Documentos y referencias citadas.';
    const ICONO = 'book';

    public static function campos(): array
    {
        return [
            'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título', 'max' => 60, 'defecto' => 'Fuentes documentales'],
            'fuentes' => ['tipo' => 'lista', 'etiqueta' => 'Fuentes', 'item' => 'Fuente', 'min' => 1, 'maxItems' => 30, 'campos' => [
                'texto' => ['tipo' => 'parrafo', 'etiqueta' => 'Referencia', 'requerido' => true, 'max' => 400],
                'enlace' => ['tipo' => 'enlace', 'etiqueta' => 'Enlace (opcional)', 'max' => 300],
            ]],
        ];
    }
}
