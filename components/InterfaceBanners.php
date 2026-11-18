<?php

namespace app\components;

use app\models\Banners;

interface InterfaceBanners
{
    /** @return array ['searchModel' => BannersSearch, 'dataProvider' => ActiveDataProvider, 'extremos' => [min, max] del orden] */
    public function listar($params);

    /** Banner o 404. */
    public function buscar($id): Banners;

    /** Crea o edita (con imágenes). @return array ['exito' => bool, 'mensaje' => string] */
    public function guardar(Banners $model, array $post);

    public function cambiarEstado($id);

    /** Sube (-1) o baja (+1) un lugar en el orden. */
    public function mover($id, $direccion);

    /** Borra el banner y sus imágenes. */
    public function eliminar($id);

    public function historial($id);
}
