<?php

namespace app\components;

interface InterfaceEcosistemas
{
    public function listar($params);
    public function buscar($id, $permiso = 'ecosistemas_ver');
    public function puedeGestionar($ecosistema);
    public function guardar($model, $post);
    public function cambiarEstado($id);
    /** Marca o quita "Destacado" (prioritario 2026–2035). */
    public function cambiarDestacado($id);
    public function subirGaleria($id);
    public function borrarImagenGaleria($idimagen);
    public function historial($id);
    /** Cuántos proyectos tiene asignados (no se puede eliminar si tiene). */
    public function proyectosAsignados($id);
    /** Elimina el ecosistema con sus imágenes y galería, si no tiene proyectos. */
    public function eliminar($id);
}
