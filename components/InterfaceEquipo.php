<?php

namespace app\components;

use app\models\Equipo;

interface InterfaceEquipo
{
    /** @return array ['searchModel', 'dataProvider', 'extremos' => [min, max] del orden] */
    public function listar($params);

    /** Miembro o 404. */
    public function buscar($id): Equipo;

    /** Crea o edita (foto y biografía incluidas). @return array ['exito' => bool, 'mensaje' => string] */
    public function guardar(Equipo $model, array $post);

    public function cambiarEstado($id);

    /** Marca o quita "Destacado" (sale en el Inicio y primero en Liderazgo). */
    public function cambiarDestacado($id);

    /** Sube (-1) o baja (+1) un lugar en el orden. */
    public function mover($id, $direccion);

    /** Borra el miembro y su foto. */
    public function eliminar($id);

    public function historial($id);

    /** @return array ['titulo', 'columnas', 'modelos', 'filtros'] para Exportador (con los filtros del listado). */
    public function exportar($params);
}
