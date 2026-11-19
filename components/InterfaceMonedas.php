<?php

namespace app\components;

use app\models\Moneda;

interface InterfaceMonedas
{
    /** @return array ['dataProvider', 'usadas' => idmoneda => cantidad de proyectos] */
    public function listar();

    public function buscar($id): Moneda;

    /** @return array ['exito' => bool, 'mensaje' => string] */
    public function guardar(Moneda $m, array $post);

    public function cambiarEstado($id);

    /** Solo si ningún proyecto la usa. */
    public function eliminar($id);
}
