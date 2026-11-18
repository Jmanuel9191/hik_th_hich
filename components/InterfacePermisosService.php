<?php

namespace app\components;

interface InterfacePermisosService
{
    public function getUsuario();
    public function estaLogueado();
    public function tieneRol($aliasRol);
    public function tienePermiso($aliasPermiso);
    public function verificarPermiso($aliasPermiso);
    public function verificarAdministrador();
    public function verificarGestor();
    public function verificarConsultor();
    public function verificarCliente();
    public function verificarRol(array $rolesPermitidos);
    public function verificarAcceso(array $roles, string $permiso);
    public function verificarLogin();
    public function matrizPermisos();
    public function guardarMatriz(array $marcados);
    public function historialPermisos();

}
