<?php
namespace app\components;

use app\models\Proyectos;

interface InterfaceProyectos
{
    public function verificarAccesoAdmingestoryadmin();
    public function verificarAccesoAdmingestoradminyconsultor();
    public function verificarAccesoCons();
    public function verificarAccesoConsycliente();

    public function listarProyectos($queryParams);
    public function listarmisProyectos($params);
    //public function nuevoProyecto($queryParams);
    public function nuevoProyecto($queryParams, InterfaceCatalogoService $catalogoService);
public function actualizarOrdenExistentes($idproducto, $ordenes);
    public function obtenerProyecto($id);
    public function verificarUsuarioLogueado();
    public function obtenerProyectoasignado($id);
    public function actualizarProyecto($dates, $id, InterfaceCatalogoService $catalogoService);
    public function cambiarEstadoproyecto($id, $motivo);
    public function puedeFinalizarProyecto($idproyecto);
    public function listHistorial($queryParams, $idproyecto);
    public function obtenerParametrosDeProducto($idproducto);
public function agregarFilasParametros($idproducto, $filas);
public function quitarParametroDeProducto($idproducto, $idparametro);
public function crearYVincularParametro($idproducto, $post);
public function agregarParametros($idproducto, $parametros);
public function obtenerParametro($idparametro);
public function editarParametro($idparametro, $post);

    public function finalizarProyecto($post);
    public function limpiarFlashes();
}


