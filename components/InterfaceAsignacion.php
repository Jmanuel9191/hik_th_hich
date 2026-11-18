<?php
namespace app\components;

use app\models\Proyectos;

interface InterfaceAsignacion
{
    public function obtenerUsuariosesion();
    public function prepararModeloAsignacion($idproyecto);
    public function prepararModeloAsignacioncamp($idcampania);
    public function procesarAsignacionTarea($model, $datosPost);
    public function procesarEdicionAsignacion($id, $datosPost);
    public function listHistorialAsignacion($queryParams, $idproyecto);
    public function aceptarTarea($id);

   // public function confirmarAceptacion($idTarea, $estadoTarea, $comentarioTarea);
    public function obtenerAsignacionPorId($id);
    public function finalizarTarea($post);
    public function obtenerAsignacionesFinalizablescamp($id);
    public function obtenerAsignacionesFinalizables($id);
    public function puedeFinalizarAsignacion($idasignacion);
    public function solicitudfinalizarCamp($post);

    public function revertirFinalizacionTarea($post);
}


