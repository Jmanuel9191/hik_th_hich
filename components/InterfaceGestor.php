<?php
namespace app\components;

interface InterfaceGestor
{
    public function verificarAccesoGestor();
    public function obtenerUsuario($id);

}