<?php

namespace app\models\bloques;

/**
 * Tarjetas de a 2, 3 o 4 por fila (clases .cols3 / .sectors / .princ / .hitos de la referencia):
 * antetítulo, título y texto o lista. Se puede marcar una en negro. En el texto, las líneas que empiezan
 * con "- " se muestran como lista.
 */
class BloqueTarjetas extends Bloque
{
    const TIPO = 'tarjetas';
    const NOMBRE = 'Tarjetas';
    const DESCRIPCION = 'De 2 a 4 por fila, con título y texto o lista; una puede ir en negro.';
    const GRUPO = 'tarjetas';
    const ICONO = 'clone';

    const POR_FILA = ['3' => '3 por fila', '2' => '2 por fila', '4' => '4 por fila'];
    const ESTILOS = ['borde' => 'Con borde fino', 'linea' => 'Con línea dorada arriba'];

    public static function campos(): array
    {
        return [
            'por_fila' => ['tipo' => 'opciones', 'etiqueta' => 'Tarjetas por fila', 'opciones' => self::POR_FILA],
            'estilo' => ['tipo' => 'opciones', 'etiqueta' => 'Diseño', 'opciones' => self::ESTILOS],
            'tarjetas' => ['tipo' => 'lista', 'etiqueta' => 'Tarjetas', 'item' => 'Tarjeta', 'min' => 1, 'maxItems' => 12, 'campos' => [
                'antetitulo' => ['tipo' => 'texto', 'etiqueta' => 'Antetítulo', 'max' => 60, 'placeholder' => 'Indicios de mineralización'],
                'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título', 'requerido' => true, 'max' => 100, 'placeholder' => 'Evidencia reportada'],
                'texto' => ['tipo' => 'parrafo', 'etiqueta' => 'Texto (las líneas que empiezan con «- » son una lista)', 'max' => 900],
                'negra' => ['tipo' => 'casilla', 'etiqueta' => 'Esta tarjeta en negro (destacada)'],
            ]],
        ];
    }

    /**
     * Texto de una tarjeta en partes: [['p', 'párrafo'], ['ul', ['ítem', 'ítem']], …]
     * (las líneas con "- " seguidas forman una lista).
     */
    public static function partes(string $texto): array
    {
        $partes = [];
        foreach (preg_split('/\R/u', $texto) as $linea) {
            $linea = trim($linea);
            if ($linea === '') {
                continue;
            }
            if (preg_match('/^[-•*]\s+(.*)$/u', $linea, $m)) {
                $ultimo = count($partes) - 1;
                if ($ultimo >= 0 && $partes[$ultimo][0] === 'ul') {
                    $partes[$ultimo][1][] = $m[1];
                } else {
                    $partes[] = ['ul', [$m[1]]];
                }
            } else {
                $partes[] = ['p', $linea];
            }
        }
        return $partes;
    }
}
