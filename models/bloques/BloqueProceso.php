<?php

namespace app\models\bloques;

/**
 * Proceso (clase .proc de la referencia): pasos numerados en fila y, arriba, los grupos a los que pertenecen.
 * Los pasos seguidos con el mismo grupo comparten una franja (la primera negra, la segunda dorada…).
 */
class BloqueProceso extends Bloque
{
    const TIPO = 'proceso';
    const NOMBRE = 'Proceso';
    const DESCRIPCION = 'Pasos numerados en fila, agrupados con una franja arriba.';
    const GRUPO = 'pasos';
    const ICONO = 'random';

    public static function campos(): array
    {
        return [
            'pasos' => ['tipo' => 'lista', 'etiqueta' => 'Pasos', 'item' => 'Paso', 'min' => 2, 'maxItems' => 8, 'campos' => [
                'grupo' => ['tipo' => 'texto', 'etiqueta' => 'Grupo (opcional)', 'max' => 50, 'placeholder' => 'Clasificación granulométrica'],
                'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título', 'requerido' => true, 'max' => 60],
                'texto' => ['tipo' => 'texto', 'etiqueta' => 'Texto (opcional)', 'max' => 200],
            ]],
        ];
    }

    /** Franjas de grupo: [['nombre' => 'Clasificación…', 'pasos' => 3], …] (vacío si ningún paso tiene grupo). */
    public function grupos(): array
    {
        if (!array_filter(array_column($this->pasos, 'grupo'))) {
            return [];
        }
        $grupos = [];
        foreach ($this->pasos as $p) {
            $ultimo = count($grupos) - 1;
            if ($ultimo >= 0 && $grupos[$ultimo]['nombre'] === $p['grupo']) {
                $grupos[$ultimo]['pasos']++;
            } else {
                $grupos[] = ['nombre' => $p['grupo'], 'pasos' => 1];
            }
        }
        return $grupos;
    }
}
