<?php

namespace app\models\bloques;

/** Lista numerada (clases .work / .acts de la referencia): número dorado, comienzo en negrita opcional y texto. */
class BloqueListaNumerada extends Bloque
{
    const TIPO = 'lista_numerada';
    const NOMBRE = 'Lista numerada';
    const DESCRIPCION = 'Ítems numerados con el comienzo en negrita (actividades, pasos).';
    const GRUPO = 'pasos';
    const ICONO = 'list-ol';

    const ESTILOS = ['simple' => 'Número chico (01, 02…)', 'grande' => 'Número grande (1, 2…)'];

    public static function campos(): array
    {
        return [
            'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título (opcional)', 'max' => 100, 'placeholder' => 'Trabajo ejecutado'],
            'estilo' => ['tipo' => 'opciones', 'etiqueta' => 'Diseño', 'opciones' => self::ESTILOS],
            'inicio' => ['tipo' => 'numero', 'etiqueta' => 'Empieza en', 'min' => 0, 'defecto' => '1',
                'ayuda' => 'Ej.: 7 si sigue de otra lista que terminó en 6.'],
            'items' => ['tipo' => 'lista', 'etiqueta' => 'Ítems', 'item' => 'Ítem', 'min' => 1, 'maxItems' => 30, 'campos' => [
                'destacado' => ['tipo' => 'texto', 'etiqueta' => 'Comienzo en negrita (opcional)', 'max' => 150, 'placeholder' => 'Perforación selectiva'],
                'texto' => ['tipo' => 'parrafo', 'etiqueta' => 'Texto', 'requerido' => true, 'max' => 600],
            ]],
        ];
    }
}
