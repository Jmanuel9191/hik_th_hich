<?php

namespace app\models\bloques;

use yii\helpers\Html;

/**
 * Tabla con encabezado negro (table.t de la referencia). En las celdas:
 *   **texto**  negrita · [texto]  chip gris · [*texto]  chip dorado  (se pueden combinar: **Marisa** [*Blanco prioritario])
 * En el panel se carga con un editor de filas y columnas o pegando desde Excel.
 */
class BloqueTabla extends Bloque
{
    const TIPO = 'tabla';
    const NOMBRE = 'Tabla';
    const DESCRIPCION = 'Filas y columnas; se puede pegar desde Excel. Negrita y chips en las celdas.';
    const GRUPO = 'datos';
    const ICONO = 'table';

    public static function campos(): array
    {
        return [
            'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título (opcional)', 'max' => 100, 'placeholder' => 'Criterio de priorización sugerido'],
            'tabla' => ['tipo' => 'tabla', 'etiqueta' => 'Tabla', 'requerido' => true],
            'nota' => ['tipo' => 'parrafo', 'etiqueta' => 'Nota al pie (opcional)', 'max' => 500],
        ];
    }

    /** HTML seguro de una celda: escapa todo y después aplica **negrita**, [chip] y [*chip dorado]. */
    public static function celda(string $texto): string
    {
        $html = Html::encode($texto);
        $html = preg_replace('/\*\*(.+?)\*\*/u', '<b>$1</b>', $html);
        $html = preg_replace('/\[\*(.+?)\]/u', '<span class="huk-pro-chip is-dorado">$1</span>', $html);
        return preg_replace('/\[(?!\*)(.+?)\]/u', '<span class="huk-pro-chip">$1</span>', $html);
    }
}
