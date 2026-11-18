<?php
namespace app\components;

use app\models\Proyectos;

interface InterfaceCampanias
{
    public function verificarAccesoAdmingestoryadmin();
    public function verificarAccesoAdmingestoradminyconsultor();
    public function verificarAccesoCons();
    public function verificarAccesoConsycliente();

    public function listarCampanias($queryParams);
    public function listarmisCampanias($params);

    public function nuevoCampania($queryParams, InterfaceCatalogoService $catalogoService);

    public function obtenerCampania($id);
    public function verificarUsuarioLogueado();
    public function obtenerCampaniaasignado($id);
    public function actualizarCampania($dates, $id, InterfaceCatalogoService $catalogoService);
    public function cambiarEstadocampania($id, $motivo);
    public function puedeFinalizarCampania($idcampania);
    public function listHistorial($queryParams, $idcampania);

    public function finalizarCampania($post);
    public function limpiarFlashes();
}


