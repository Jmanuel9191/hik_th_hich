<?php
namespace app\components;
use app\models\Tareas;
use app\models\TareasSearch;
use app\models\Usuarios;
use Yii;
use yii\web\NotFoundHttpException;

class GestionProyectoAService implements InterfaceGestionProyecto
{
    private $proyectosService;
    private $asignacionService;
    private $recursosService;
    private $materialService;
    private $catalogoService;


    public function __construct(InterfaceProyectos $proyectosService, InterfaceAsignacion $asignacionService, InterfaceRecursos $recursosService, InterfaceMaterial $materialService, InterfaceCatalogoService $catalogoService)
    {
        $this->proyectosService = $proyectosService;
        $this->asignacionService = $asignacionService;
        $this->recursosService = $recursosService;
        $this->materialService = $materialService;
        $this->catalogoService = $catalogoService;
    }

    public function verificarAccesoAdmingestoryadmin()
    {
        return $this->proyectosService->verificarAccesoAdmingestoryadmin();
    }

    public function verificarUsuarioLogueado()
    {
        return $this->proyectosService->verificarUsuarioLogueado();
    }

    public function verificarAccesoCons()
    {
        return $this->proyectosService->verificarAccesoCons();
    }

    public function verificarAccesoConsycliente()
    {
        return $this->proyectosService->verificarAccesoConsycliente();
    }

        public function listarProyectos($queryParams)
    {
        return $this->proyectosService->listarProyectos($queryParams);
    }

    public function listHistorial($params, $idproyecto)
    {
        return $this->proyectosService->listHistorial($params, $idproyecto);
    }


    public function listHistorialAsignacion($params, $idproyecto)
    {
        return $this->asignacionService->listHistorialAsignacion($params, $idproyecto);
    }

    public function eliminarAsignacionTarea($id)
    {
        return $this->asignacionService->eliminarAsignacionTarea($id);
    }



    public function listarmisProyectos($params)
    {
        return $this->proyectosService->listarmisProyectos($params);
    }



    public function obtenerParametrosDeProducto($idproducto)
    {
        return $this->proyectosService->obtenerParametrosDeProducto($idproducto);
    }


      public function agregarFilasParametros($idproducto, $filas)
    {
        return $this->proyectosService->agregarFilasParametros($idproducto, $filas);
    }


      public function crearYVincularParametro($idproducto, $post)
    {
        return $this->proyectosService->crearYVincularParametro($idproducto, $post);
    }

    public function quitarParametroDeProducto($idproducto, $idparametro)
    {
        return $this->proyectosService->quitarParametroDeProducto($idproducto, $idparametro);
    }


    
    public function editarParametro($idparametro, $post)
    {
        return $this->proyectosService->editarParametro($idparametro, $post);
    }



      public function obtenerParametro($idparametro)
    {
        return $this->proyectosService->obtenerParametro($idparametro);
    }




public function agregarParametros($idproducto, $parametros)
 {
        return $this->proyectosService->agregarParametros($idproducto, $parametros);
    }



      public function nuevoProyecto($queryParams)
    {
        return $this->proyectosService->nuevoProyecto($queryParams, $this->catalogoService);
    }

        public function actualizarOrdenExistentes($idproducto, $ordenes)

    {
        return $this->proyectosService->actualizarOrdenExistentes($idproducto, $ordenes);
    }

/*

      public function quitarParametroDeProducto($idproducto, $idparametro)
    {
        return $this->proyectosService->quitarParametroDeProducto($idproducto, $idparametro);
    }
*/


    public function actualizarProyecto($dates, $id)
    {
        return $this->proyectosService->actualizarProyecto($dates, $id, $this->catalogoService);
    }


    public function obtenerProyecto($id)
    {
        return $this->proyectosService->obtenerProyecto($id);
    }
    public function obtenerProyectoasignado($id)
    {
        return $this->proyectosService->obtenerProyectoasignado($id);
    }




    public function aceptarTarea($id)
    {
        return $this->asignacionService->aceptarTarea($id);
    }


    public function prepararModeloAsignacion($id, $esProyecto)
    {

        return $this->asignacionService->prepararModeloAsignacion($id,  $esProyecto);
    }
    public function procesarAsignacionTarea($model, $datosPost)
    {
        return $this->asignacionService->procesarAsignacionTarea($model, $datosPost);
    }

    public function procesarEdicionAsignacion($id, $datosPost)
    {
        return $this->asignacionService->procesarEdicionAsignacion($id, $datosPost);
    }
    public function cambiarEstadoproyecto($id, $motivo)
    {
        return $this->proyectosService->cambiarEstadoproyecto($id, $motivo);
    }

    public function verificarAccesoAdmingestoradminyconsultor()
    {
        return $this->proyectosService->verificarAccesoAdmingestoradminyconsultor();
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
        return $this->proyectosService->obtenerProyecto($id);
    }



    /*MATERIAL SERVICE*/
    public function verificarAccesoCompletoAlHistorial($idrecurso)
    {
        return $this->materialService->verificarAccesoCompletoAlHistorial($idrecurso);
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
    public function obtenerAsignacionesFinalizables($id)    {
        return $this->asignacionService->obtenerAsignacionesFinalizables($id);
    }
    public function revertirFinalizacionTarea($post)   {
        return $this->asignacionService->revertirFinalizacionTarea($post);
    }
    public function puedeFinalizarProyecto($idproyecto) {
        return $this->proyectosService->puedeFinalizarProyecto($idproyecto);
    }

    public function finalizarProyecto($post){
        return $this->proyectosService->finalizarProyecto($post);
    }

    public function limpiarFlashes(){
        return $this->proyectosService->limpiarFlashes();
    }

}