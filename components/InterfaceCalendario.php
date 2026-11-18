<?php
namespace app\components;

use app\models\Usuarios;

interface InterfaceCalendario
{

    public function limpiarFlashes();
    public function obtenerEventosPorUsuario(Usuarios $usuario): array;
    public function obtenerEventosGlobales(): array;

}


