<?php
namespace app\components;

interface InterfaceCategoriasServicio
{
    public function listCategorias($queryParams);
    public function nuevaCategoria($postData);
    public function actualizarCategoria($model, $postData);
    public function eliminarCategoria($id);
    public function findModel($id);
    public function limpiarFlashes();
}