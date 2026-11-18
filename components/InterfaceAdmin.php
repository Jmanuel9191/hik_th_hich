<?php
namespace app\components;

interface InterfaceAdmin
{
    public function verificarAccesoAdmin();
    public function obtenerRolesActivos();
    public function listHistorial($queryParams, $idusuario = null);
    public function listUsuarios($queryParams);
    public function conteoPorPestana(): array;
    public function obtenerUsuario($id);
    public function nuevoUsuario($postData);
    public function actualizarUsuario($dates, $id);
    public function cambiarEstadoUsuario($id, $motivo = '');
    public function eliminarUsuario($id);
    public function historialUsuario($id);
    public function exportarUsuarios($params);

}