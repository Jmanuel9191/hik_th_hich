<?php
namespace app\components;
use app\models\Tareas;
use app\models\TareasSearch;
use app\models\Usuarios;
use Yii;
use yii\web\NotFoundHttpException;

class GestionCampaniaAService implements InterfaceGestionCampania
{
    private $campaniasService;
    private $asignacionService;
    private $recursosService;
    private $materialService;

    private $catalogoService;
    public function __construct(InterfaceCampanias $campaniasService, InterfaceAsignacion $asignacionService, InterfaceRecursos $recursosService, InterfaceMaterial $materialService, InterfaceCatalogoService $catalogoService)
    {
        $this->campaniasService = $campaniasService;
        $this->asignacionService = $asignacionService;
        $this->recursosService = $recursosService;
        $this->materialService = $materialService;
        $this->catalogoService = $catalogoService;
    }

    public function verificarAccesoAdmingestoryadmin()
    {
        return $this->campaniasService->verificarAccesoAdmingestoryadmin();
    }

    public function verificarUsuarioLogueado()
    {
        return $this->campaniasService->verificarUsuarioLogueado();
    }

    public function verificarAccesoCons()
    {
        return $this->campaniasService->verificarAccesoCons();
    }

    public function verificarAccesoConsycliente()
    {
        return $this->campaniasService->verificarAccesoConsycliente();
    }

    public function listarCampanias($queryParams)
    {
        return $this->campaniasService->listarCampanias($queryParams);
    }

    public function listHistorial($params, $idcampania)
    {
        return $this->campaniasService->listHistorial($params, $idcampania);
    }

    public function listarMisCampanias($params)
    {
        return $this->campaniasService->listarMisCampanias($params);
    }



    public function nuevoCampania($queryParams)
    {
        return $this->campaniasService->nuevoCampania($queryParams, $this->catalogoService);
    }


    public function eliminarAsignacionTarea($id)
    {
        return $this->asignacionService->eliminarAsignacionTarea($id);
    }

    public function actualizarCampania($dates, $id)
    {
        return $this->campaniasService->actualizarCampania($dates, $id, $this->catalogoService);
    }



    public function obtenerCampania($id)
    {
        return $this->campaniasService->obtenerCampania($id);
    }
    public function obtenerCampaniaasignado($id)
    {
        return $this->campaniasService->obtenerCampaniaasignado($id);
    }




    public function aceptarTarea($id)
    {
        return $this->asignacionService->aceptarTarea($id);
    }
    public function prepararModeloAsignacioncamp($idcampania)
    {

        return $this->asignacionService->prepararModeloAsignacioncamp($idcampania);
    }

    public function prepararModeloAsignacion($idcampania)
    {

        return $this->asignacionService->prepararModeloAsignacion($idcampania);
    }
    public function procesarAsignacionTarea($model, $datosPost)
    {
        return $this->asignacionService->procesarAsignacionTarea($model, $datosPost);
    }

    public function procesarEdicionAsignacion($id, $datosPost)
    {
        return $this->asignacionService->procesarEdicionAsignacion($id, $datosPost);
    }
    public function cambiarEstadocampania($id, $motivo)
    {
        return $this->campaniasService->cambiarEstadocampania($id, $motivo);
    }

    public function verificarAccesoAdmingestoradminyconsultor()
    {
        return $this->campaniasService->verificarAccesoAdmingestoradminyconsultor();
    }


    public function nuevoRecurso($dates, $idasignartarea)
    {
        return $this->recursosService->nuevoRecurso($dates, $idasignartarea);
    }

    public function obtenerAsignacionPorId($id)
    {
        return $this->asignacionService->obtenerAsignacionPorId($id);
    }
    public function eliminarRecurso($id)
    {
        return $this->recursosService->eliminarRecurso($id);
    }

    public function editarRecurso($id, $data)
    {
        return $this->recursosService->editarRecurso($id, $data);
    }

    public function findModel($id)
    {
        return $this->campaniasService->obtenerCampania($id);
    }


    public function verificarAccesoCompletoAlHistorial($idrecurso)
    {
        return $this->materialService->verificarAccesoCompletoAlHistorial($idrecurso);
    }
    /*MATERIAL SERVICE*/
    public function verificarAccesoCompletoAlHistorialcamp($idrecurso)
    {
        return $this->materialService->verificarAccesoCompletoAlHistorialcamp($idrecurso);
    }

    public function listarHistorial($params, $idrecurso)
    {
        return $this->materialService->listarHistorial($params, $idrecurso);
    }

    public function obtenerRecurso($idrecurso)
    {
        return $this->recursosService->obtenerRecurso($idrecurso);
    }


    public function procesarMensajeConArchivo($datosPost, $datosArchivo)
    {
        return $this->materialService->procesarMensajeConArchivo($datosPost, $datosArchivo);
    }

    public function obtenerMensajesNuevos($idrecurso, $ultimoId)
    {
        return $this->materialService->obtenerMensajesNuevos($idrecurso, $ultimoId);
    }


    public function obtenerUltimoIdMensaje($idrecurso)
    {
        return $this->materialService->obtenerUltimoIdMensaje($idrecurso);
    }

    public function esClienteDelRecurso($recurso)
    {
        return $this->materialService->obtenerUltimoIdMensaje($recurso);
    }
    public function verificarAccesoCliente()
    {
        return $this->materialService->verificarAccesoCliente();
    }

    public function rechazarRecursoConComentario($post)
    {
        return $this->recursosService->rechazarRecursoConComentario($post);
    }

    public function aprobarRecursoConComentario($post)
    {
        return $this->recursosService->aprobarRecursoConComentario($post);
    }

    public function todosLosRecursosAprobados($idasignacion)
    {
        return $this->recursosService->todosLosRecursosAprobados($idasignacion);
    }

    public function finalizarTarea($post)    {
        return $this->asignacionService->finalizarTarea($post);
    }

    public function solicitudfinalizarCamp($post)    {
        return $this->asignacionService->solicitudfinalizarCamp($post);
    }
    public function obtenerAsignacionesFinalizables($id)    {
        return $this->asignacionService->obtenerAsignacionesFinalizables($id);
    }

    public function obtenerAsignacionesFinalizablescamp($id)    {
        return $this->asignacionService->obtenerAsignacionesFinalizablescamp($id);
    }
    public function revertirFinalizacionTarea($post)   {
        return $this->asignacionService->revertirFinalizacionTarea($post);
    }
    public function puedeFinalizarCampania($idcampania) {
        return $this->campaniasService->puedeFinalizarCampania($idcampania);
    }

    public function finalizarCampania($post){
        return $this->campaniasService->finalizarCampania($post);
    }

    public function limpiarFlashes(){
        return $this->campaniasService->limpiarFlashes();
    }

}