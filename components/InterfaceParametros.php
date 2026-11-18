<?php

namespace app\components;

interface InterfaceParametros
{
    public function listParametros($queryParams);
    public function nuevoParametro($postData);
    public function actualizarParametro($model, $postData);
    public function eliminarParametro($id);
      public function limpiarFlashes();
    public function findModel($id);
}