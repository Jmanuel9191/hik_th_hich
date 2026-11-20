<?php

namespace app\components;

use app\models\Banners;

interface InterfaceBanners
{
    /**
     * Listado de una página (pestaña).
     * @return array ['searchModel', 'dataProvider', 'extremos' => [min, max] del orden, 'conteos' => ubicacion => cantidad,
     *                'enUso' => id del banner que se ve hoy como encabezado (páginas internas) o null]
     */
    public function listar($params, $ubicacion = 'inicio');

    /** Para la web: los banners de una página que se muestran hoy (activos y dentro de sus fechas), en orden. @return Banners[] */
    public function paraWeb($ubicacion = 'inicio');

    /** Encabezado de una página interna: el primero vigente de la lista, o null. */
    public function encabezado($ubicacion): ?Banners;

    /** Banner o 404. */
    public function buscar($id): Banners;

    /** Crea o edita (con imágenes). @return array ['exito' => bool, 'mensaje' => string] */
    public function guardar(Banners $model, array $post);

    public function cambiarEstado($id);

    /** Sube (-1) o baja (+1) un lugar en el orden (dentro de su misma página). */
    public function mover($id, $direccion);

    /** Borra el banner y sus imágenes. */
    public function eliminar($id);

    public function historial($id);
}
