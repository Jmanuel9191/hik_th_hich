<?php

namespace app\models\bloques;

/**
 * Condiciones de inversión tomadas de la ficha del proyecto: participación, montos, ROI, plazo, frecuencia y fechas.
 * Solo muestra los datos cargados. Se agrega a mano en cada proyecto (no viene en las plantillas).
 */
class BloqueCondiciones extends Bloque
{
    const TIPO = 'condiciones';
    const NOMBRE = 'Condiciones de inversión';
    const DESCRIPCION = 'Montos, plazo y ROI de la ficha del proyecto.';
    const GRUPO = 'proyecto';
    const ICONO = 'line-chart';
    const AUTOMATICO = true;

    public static function campos(): array
    {
        return [
            'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título', 'max' => 80, 'defecto' => 'Condiciones de inversión'],
            'nota' => ['tipo' => 'parrafo', 'etiqueta' => 'Nota al pie (opcional)', 'max' => 400,
                'placeholder' => 'Valores de referencia, sujetos a los acuerdos definitivos.'],
        ];
    }
}
