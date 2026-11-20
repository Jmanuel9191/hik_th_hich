<?php

namespace app\models\bloques;

/** Una imagen con su pie (figure / .mapfig de la referencia). */
class BloqueImagen extends Bloque
{
    const TIPO = 'imagen';
    const NOMBRE = 'Imagen con pie';
    const DESCRIPCION = 'Foto o mapa con su texto al pie.';
    const ICONO = 'picture-o';

    const FORMATOS = ['original' => 'Como es', '16-10' => 'Horizontal 16:10', '4-3' => 'Horizontal 4:3', '1-1' => 'Cuadrada'];

    public static function campos(): array
    {
        return [
            'imagen' => ['tipo' => 'imagen', 'etiqueta' => 'Imagen', 'requerido' => true,
                'ayuda' => 'JPG, PNG o WEBP, hasta 8 MB. Se achica sola para que la página cargue rápido.'],
            'alt' => ['tipo' => 'texto', 'etiqueta' => 'Qué muestra (para lectores de pantalla)', 'max' => 150, 'placeholder' => 'Nevado Condoriri y laguna Tuni'],
            'pie' => ['tipo' => 'parrafo', 'etiqueta' => 'Pie de foto', 'max' => 300, 'placeholder' => 'Imagen de referencia: …'],
            'formato' => ['tipo' => 'opciones', 'etiqueta' => 'Recorte', 'opciones' => self::FORMATOS],
            'mapa' => ['tipo' => 'casilla', 'etiqueta' => 'Es un mapa o plano (se muestra completo, con borde fino)'],
        ];
    }
}
