<?php
namespace app\components;

interface InterfaceCliente
{
    public function verificarAccesoCliente();
    public function obtenerUsuario($id);

}