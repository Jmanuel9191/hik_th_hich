<?php
namespace app\components;

interface InterfaceMaterial
{
    public function verificarAccesoCompletoAlHistorial($idrecurso);
    public function verificarAccesoCompletoAlHistorialcamp($idrecurso);
    public function listarHistorial($params, $idrecurso);
    public function procesarMensajeConArchivo($datosPost, $datosArchivo);
    public function obtenerMensajesNuevos($idrecurso, $ultimoId);
    public function obtenerUltimoIdMensaje($idrecurso);
    public function verificarAccesoCliente();

}


