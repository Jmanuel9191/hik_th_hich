<?php

namespace app\models\bloques;

/** Línea de tiempo (clase .timeline de la referencia): pasos hechos (negro), pendientes (gris) y decisión (dorado). */
class BloqueLineaTiempo extends Bloque
{
    const TIPO = 'linea_tiempo';
    const NOMBRE = 'Línea de tiempo';
    const DESCRIPCION = 'Etapas en fila: hecha, pendiente o decisión.';
    const GRUPO = 'pasos';
    const ICONO = 'long-arrow-right';

    const ESTADOS = ['pendiente' => 'Pendiente (gris)', 'hecho' => 'Hecho (negro)', 'decision' => 'Decisión (dorado)'];

    public static function campos(): array
    {
        return [
            'pasos' => ['tipo' => 'lista', 'etiqueta' => 'Etapas', 'item' => 'Etapa', 'min' => 2, 'maxItems' => 6, 'campos' => [
                'rotulo' => ['tipo' => 'texto', 'etiqueta' => 'Rótulo', 'max' => 50, 'placeholder' => 'Concluida · Ago 2026'],
                'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título', 'requerido' => true, 'max' => 60, 'placeholder' => 'Fase I'],
                'texto' => ['tipo' => 'parrafo', 'etiqueta' => 'Texto', 'max' => 300],
                'estado' => ['tipo' => 'opciones', 'etiqueta' => 'Estado', 'opciones' => self::ESTADOS],
            ]],
        ];
    }
}
