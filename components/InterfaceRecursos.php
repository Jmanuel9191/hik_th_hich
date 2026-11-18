<?php
namespace app\components;

use app\models\Recursos;

interface InterfaceRecursos
{
    public function nuevoRecurso($dates, $idasignartarea);
    public function eliminarRecurso($id);
    public function editarRecurso($id, $data);
    public function esClienteDelRecurso($recurso);
    public function rechazarRecursoConComentario($post);
    public function aprobarRecursoConComentario($post);
    public function todosLosRecursosAprobados($idasignacion);


}


