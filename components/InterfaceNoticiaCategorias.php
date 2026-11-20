<?php

namespace app\components;

use app\models\NoticiaCategoria;

/** Blog: categorías de las noticias (Panel → Blog → Categorías). */
interface InterfaceNoticiaCategorias
{
    /** @return array ['dataProvider', 'cantidades' => idcategoria => n° de noticias, 'extremos' => [min, max] del orden] */
    public function listar();

    public function buscar($id): NoticiaCategoria;

    /** @return array ['exito' => bool, 'mensaje' => string] */
    public function guardar(NoticiaCategoria $model, array $post);

    public function cambiarEstado($id);

    /** Sube (-1) o baja (+1) un lugar. */
    public function mover($id, $direccion);

    /** Solo si no tiene noticias. */
    public function eliminar($id);

    /** Historial de todas las categorías. @return \yii\data\ActiveDataProvider */
    public function historial();
}
