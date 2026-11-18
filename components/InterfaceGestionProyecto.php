<?php
namespace app\components;

interface InterfaceGestionProyecto
{
    public function listarProyectos($queryParams);
    public function listarmisProyectos($params);
    public function listHistorial($queryParams, $idproyecto);
    public function verificarAccesoAdmingestoryadmin();
    public function verificarAccesoAdmingestoradminyconsultor();
    public function verificarUsuarioLogueado();
    public function verificarAccesoCons();
    public function verificarAccesoConsycliente();
    public function nuevoProyecto($queryParams);
    public function listHistorialAsignacion($queryParams, $idproyecto);
    public function eliminarAsignacionTarea($id);
    public function cambiarEstadoproyecto($id, $motivo);
    public function nuevoRecurso($dates, $idasignartarea);
    public function editarRecurso($id, $data);
    public function aceptarTarea($id);
    public function eliminarRecurso($id);
    public function limpiarFlashes();
    public function actualizarProyecto($dates, $id);
    public function prepararModeloAsignacion($id, $esProyecto);
    public function procesarEdicionAsignacion($id, $datosPost);
    public function obtenerProyecto($id);
    public function obtenerProyectoasignado($id);
    public function obtenerAsignacionPorId($id);
    public function verificarAccesoCompletoAlHistorial($idrecurso);
    public function obtenerMensajesNuevos($idrecurso, $ultimoId);

    public function obtenerUltimoIdMensaje($idrecurso);
    public function obtenerParametrosDeProducto($idproducto);
public function agregarFilasParametros($idproducto, $filas);
public function quitarParametroDeProducto($idproducto, $idparametro);
public function actualizarOrdenExistentes($idproducto, $ordenes);
    public function listarHistorial($params, $idrecurso);
    public function obtenerRecurso($idrecurso);
    public function procesarMensajeConArchivo($datosPost, $datosArchivo);
    public function esClienteDelRecurso($recurso);
    public function rechazarRecursoConComentario($post);
    public function aprobarRecursoConComentario($post);
    public function verificarAccesoCliente();
    public function todosLosRecursosAprobados($idasignacion);
    public function finalizarTarea($post);

    public function agregarParametros($idproducto, $parametros);
public function obtenerParametro($idparametro);
public function editarParametro($idparametro, $post);

    public function obtenerAsignacionesFinalizables($id);
    public function revertirFinalizacionTarea($post);
    public function puedeFinalizarProyecto($idproyecto);
    public function finalizarProyecto($post);
    public function crearYVincularParametro($idproducto, $post);


}