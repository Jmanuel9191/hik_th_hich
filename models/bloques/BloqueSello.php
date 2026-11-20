<?php

namespace app\models\bloques;

/** Sello (clase .std de la referencia): rótulo dorado grande y un texto (ej. "NI 43-101 · CIM"). */
class BloqueSello extends Bloque
{
    const TIPO = 'sello';
    const NOMBRE = 'Sello';
    const DESCRIPCION = 'Rótulo dorado grande y su texto (normas, certificaciones).';
    const GRUPO = 'pasos';
    const ICONO = 'certificate';

    public static function campos(): array
    {
        return [
            'rotulo' => ['tipo' => 'texto', 'etiqueta' => 'Rótulo', 'requerido' => true, 'max' => 40, 'placeholder' => 'NI 43-101 · CIM'],
            'texto' => ['tipo' => 'parrafo', 'etiqueta' => 'Texto', 'requerido' => true, 'max' => 600],
        ];
    }
}
