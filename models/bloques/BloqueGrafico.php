<?php

namespace app\models\bloques;

/**
 * Gráfico de barras (clase .chart de la referencia): la barra más alta sale dorada sola, las barras crecen
 * al aparecer y abajo va el total (opcional) y una nota.
 */
class BloqueGrafico extends Bloque
{
    const TIPO = 'grafico';
    const NOMBRE = 'Gráfico de barras';
    const DESCRIPCION = 'Barras que crecen al aparecer; la más alta en dorado.';
    const GRUPO = 'datos';
    const ICONO = 'bar-chart';

    const ORIENTACIONES = ['vertical' => 'Barras verticales', 'horizontal' => 'Barras horizontales (para nombres largos)'];

    public static function campos(): array
    {
        return [
            'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título', 'max' => 100, 'placeholder' => 'Comercialización histórica de oro · 2002–2007'],
            'sufijo' => ['tipo' => 'texto', 'etiqueta' => 'Unidad (va después del número)', 'max' => 12, 'placeholder' => 'kg'],
            'orientacion' => ['tipo' => 'opciones', 'etiqueta' => 'Orientación', 'opciones' => self::ORIENTACIONES],
            'barras' => ['tipo' => 'lista', 'etiqueta' => 'Barras', 'item' => 'Barra', 'min' => 2, 'maxItems' => 16, 'campos' => [
                'etiqueta' => ['tipo' => 'texto', 'etiqueta' => 'Etiqueta', 'requerido' => true, 'max' => 30, 'placeholder' => '2003'],
                'valor' => ['tipo' => 'numero', 'etiqueta' => 'Valor', 'requerido' => true, 'min' => 0, 'placeholder' => '128'],
            ]],
            'total' => ['tipo' => 'casilla', 'etiqueta' => 'Mostrar el total (la suma de las barras)', 'defecto' => true],
            'total_texto' => ['tipo' => 'texto', 'etiqueta' => 'Texto del total', 'max' => 60, 'defecto' => 'total acumulado', 'si' => 'total:1'],
            'nota' => ['tipo' => 'parrafo', 'etiqueta' => 'Nota (opcional)', 'max' => 500],
        ];
    }

    /** Valor más alto (para la escala y para la barra dorada). */
    public function maximo(): float
    {
        return $this->barras ? max(array_map(fn($b) => (float) $b['valor'], $this->barras)) : 0.0;
    }

    public function suma(): float
    {
        return array_sum(array_map(fn($b) => (float) $b['valor'], $this->barras));
    }

    /** "128 kg", "12,5 %" (coma decimal, punto de miles). */
    public function formato(float $n): string
    {
        $texto = number_format($n, fmod($n, 1.0) ? 1 : 0, ',', '.');
        return trim($texto . ' ' . $this->sufijo);
    }
}
