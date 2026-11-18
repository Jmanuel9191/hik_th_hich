<?php
namespace app\components;

interface InterfaceLib
{
    public function obtenerPaises();
    public function obtenerZonaHoraria($ciudad, $pais);
}