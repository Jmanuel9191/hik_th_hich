<?php

namespace app\models\bloques;

/** Pasos finales (clase .vdc de la referencia): 2 a 4 pasos en fila, el último dorado (Validar · Demostrar · Escalar). */
class BloquePasosFinales extends Bloque
{
    const TIPO = 'pasos_finales';
    const NOMBRE = 'Pasos finales';
    const DESCRIPCION = 'De 2 a 4 pasos grandes; el último en dorado.';
    const GRUPO = 'pasos';
    const ICONO = 'flag-checkered';

    public static function campos(): array
    {
        return [
            'pasos' => ['tipo' => 'lista', 'etiqueta' => 'Pasos', 'item' => 'Paso', 'min' => 2, 'maxItems' => 4, 'campos' => [
                'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título', 'requerido' => true, 'max' => 40, 'placeholder' => 'Validar'],
                'texto' => ['tipo' => 'parrafo', 'etiqueta' => 'Texto', 'max' => 300],
            ]],
        ];
    }
}
