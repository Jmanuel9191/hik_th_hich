<?php
namespace app\components;

interface InterfaceOrdenes
{
    public function listOrdenes($queryParams, $soloOrdenes = true);
    public function nuevaOrden($postData, $idusuario);
    public function findModel($id);
    public function actualizarPago($model, $postData);
    public function editarPago($idpago, $postData);
    public function getPagosDeOrden($idorden);
    public function actualizarOrden($model, $postData);
    public function eliminarOrden($id);
    public function limpiarFlashes();
}