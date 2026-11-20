<?php

namespace app\models\bloques;

/** Banda de hitos (clase .milestones de la referencia): franja negra con texto, etiquetas y opciones (la primera dorada). */
class BloqueHitos extends Bloque
{
    const TIPO = 'hitos';
    const NOMBRE = 'Banda de hitos';
    const DESCRIPCION = 'Franja negra: texto, etiquetas y opciones como Continuar / Rediseñar / Detener.';
    const GRUPO = 'pasos';
    const ICONO = 'flag';

    public static function campos(): array
    {
        return [
            'antetitulo' => ['tipo' => 'texto', 'etiqueta' => 'Antetítulo', 'max' => 60, 'placeholder' => 'Disciplina de hitos'],
            'texto' => ['tipo' => 'parrafo', 'etiqueta' => 'Texto', 'max' => 400],
            'etiquetas' => ['tipo' => 'lineas', 'etiqueta' => 'Etiquetas (una por línea)', 'max' => 1000],
            'opciones' => ['tipo' => 'lineas', 'etiqueta' => 'Opciones a la derecha (una por línea; la primera va en dorado)', 'max' => 200,
                'placeholder' => "CONTINUAR\nREDISEÑAR\nDETENER"],
        ];
    }

    protected function reglas(): array
    {
        return [[['texto'], 'required', 'when' => fn($m) => $m->etiquetas === '' && $m->opciones === '', 'message' => 'Escriba un texto, etiquetas u opciones.']];
    }
}
