<?php
namespace app\components;

interface InterfaceGestionCampanias
{
    public function listarCampanias($queryParams);
    public function listarmisCampanias($params);
    public function listHistorial($queryParams, $idcampania);
    public function verificarAccesoAdmingestoryadmin();
    public function verificarAccesoAdmingestoradminyconsultor();
    public function verificarUsuarioLogueado();
    public function verificarAccesoCons();
    public function verificarAccesoConsycliente();
    public function nuevoCampania($queryParams);
    public function cambiarEstadocampania($id, $motivo);
    public function nuevoRecurso($dates, $idasignartarea);
    public function editarRecurso($id, $data);
    public function aceptarTarea($id);
    public function eliminarRecurso($id);
    public function limpiarFlashes();
    public function actualizarCampania($dates, $id);
    public function prepararModeloAsignacion($idcampania);
    public function procesarEdicionAsignacion($id, $datosPost);
    public function obtenerCampania($id);
    public function obtenerCampaniaasignado($id);
    public function obtenerAsignacionPorId($id);
    public function verificarAccesoCompletoAlHistorial($idrecurso);
    public function obtenerMensajesNuevos($idrecurso, $ultimoId);

    public function obtenerUltimoIdMensaje($idrecurso);


    public function listarHistorial($params, $idrecurso);
    public function obtenerRecurso($idrecurso);
    public function procesarMensajeConArchivo($datosPost, $datosArchivo);
    public function esClienteDelRecurso($recurso);
    public function rechazarRecursoConComentario($post);
    public function aprobarRecursoConComentario($post);
    public function verificarAccesoCliente();
    public function todosLosRecursosAprobados($idasignacion);
    public function finalizarTarea($post);

    public function obtenerAsignacionesFinalizables($id);
    public function revertirFinalizacionTarea($post);
    public function puedeFinalizarCampania($idproyecto);
    public function finalizarCampania($post);


}