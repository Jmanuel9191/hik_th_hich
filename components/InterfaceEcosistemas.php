<?php

namespace app\components;

interface InterfaceEcosistemas
{
    public function listar($params);
    public function buscar($id, $permiso = 'ecosistemas_ver');
    public function puedeGestionar($ecosistema);
    public function guardar($model, $post);
    public function cambiarEstado($id);
    public function subirGaleria($id);
    public function borrarImagenGaleria($idimagen);
    public function historial($id);
}
