<?php
namespace app\components;

interface InterfaceTarea
{
    public function listTareas($queryParams);
    public function listHistorial($queryParams, $idTarea);
    public function nuevaTarea($queryParams);
    public function actualizarTarea($dates, $id);
    public function findModel($id);
    public function limpiarFlashes();
    public function cambiarEstadoTarea($id);
}


