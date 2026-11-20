<?php

namespace app\models\bloques;

/**
 * Galería del proyecto: las fotos de la pestaña "Fotos" (todas o las elegidas), con foto grande, flechas,
 * miniaturas, deslizar con el dedo y ← → (la galería del Blog, huk.js punto 15). Sin fotos, no se muestra.
 */
class BloqueGaleriaProyecto extends Bloque
{
    const TIPO = 'galeria_proyecto';
    const NOMBRE = 'Galería del proyecto';
    const DESCRIPCION = 'Las fotos de la pestaña Fotos, con foto grande y miniaturas.';
    const GRUPO = 'proyecto';
    const ICONO = 'picture-o';
    const AUTOMATICO = true;

    public static function campos(): array
    {
        return [
            'titulo' => ['tipo' => 'texto', 'etiqueta' => 'Título', 'max' => 80, 'defecto' => 'Galería de fotos'],
            'mostrar' => ['tipo' => 'opciones', 'etiqueta' => 'Qué fotos', 'opciones' => ['todas' => 'Todas (también las que se suban después)', 'elegidas' => 'Solo las que marco']],
            'fotos' => ['tipo' => 'elegir', 'etiqueta' => 'Fotos', 'fuente' => 'fotos', 'si' => 'mostrar:elegidas'],
        ];
    }

    /** @param \app\models\ProyectoImagen[] $todas  @return \app\models\ProyectoImagen[] */
    public function fotos(array $todas): array
    {
        return $this->elegidosDe($todas, 'fotos', 'idimagen');
    }
}
