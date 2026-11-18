<?php
// components/InterfaceResultados.php
namespace app\components;

interface InterfaceResultados
{
    public function getSeccionesDeOrden($idorden);
    public function getParametrosDisponibles($idorden);
    public function guardarResultados($idorden, $postData);
}