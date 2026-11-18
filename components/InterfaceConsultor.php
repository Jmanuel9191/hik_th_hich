<?php
namespace app\components;

interface InterfaceConsultor
{
    public function verificarAccesoConsultor();
    public function obtenerUsuario($id);

}