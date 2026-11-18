<?php
namespace app\components;

interface InterfaceCorreos
{
    public function enviarCorreodeBienvenida($model);
    public function enviarCorreodeEditar($model);
    public function enviarCorreoDeBloqueo($model, $motivo);
    public function enviarCorreoDeInactividad($model, $motivo);
    public function enviarCorreoDedesBloqueo($model, $motivo);

    public function enviarCorreodeCreacionproyecto($model, $correoCliente);
    public function enviarCorreoActualizacionProyectoGestor($model, $correoGestor, $cambios);
    public function enviarCorreoAsignacionProyectoGestor($model, $correoGestor);

    public function enviarCorreodeEdicionproyecto($model, $correoCliente, $cambios);
    public function enviarCorreoDeProyectoBloqueo($model, $correoCliente, $motivo);
    public function enviarCorreoDeProyectoDesBloqueo($model, $correoCliente, $motivo);



    public function enviarCorreodeAsignacionproyecto($model, $correoConsultor);
    public function enviarCorreoAGestorSobreAsignacion($model, $correoGestor);
    public function enviarCorreoModificacionAsignacionGestor($model, $correoGestor);
    public function enviarCorreoNuevoRecursoGestor($model, $correoGestor);
    public function enviarCorreoEditarRecursoGestor($model, $correoGestor);
    public function enviarCorreoEliminarRecursoGestor($model, $correoGestor);
    public function enviarCorreoAvisoAceptaciondetarea($correoGestor, array $datos);
    public function enviarCorreoAvisoEliminacionAsignacionConsultor($correo, $idtarea, $descripcion);
    public function enviarCorreoAvisoEliminacionAsignacionGestor($correo, $idtarea, $descripcion);

    public function enviarCorreosNotificacion($usuario, $recurso, $esComentario, $esArchivo);

    public function enviarCorreoCambioConsultorAsignacion($model, $correoNuevoConsultor);
    public function enviarCorreoNuevoRecurso($model, $correoCliente);
    public function enviarCorreoEdicionRecurso($model, $correoCliente);
    public function enviarCorreoEliminacionRecurso($model, $correoCliente);



    public function enviarCorreoDesasignacionConsultor($model, $correoAnterior);
    public function enviarCorreoModificacionAsignacion($model, $correoConsultor);
    public function enviarCorreoModificacionAsignacionCliente($model, $correoCliente);


    public function enviarCorreoRecuperacion($id, $enlace);
    public function enviarCorreoDesbloqueoPorIntentos($usuario);
    public function enviarCorreoDeBloqueoporintentos($id);
    public function enviarCorreoRechazoRecurso($recurso, $comentario, $destinatarios);
    public function enviarCorreoAprobacionRecurso($recurso, $comentario, $destinatarios);

    public function enviarCorreoFinalizacionTarea($asignacion, $correo);
    public function enviarCorreoReversionTarea($asignacion, $destinatarios);
    public function enviarCorreoFinalizacionProyecto($proyecto, $correo);


    public function enviarCorreodeCreacioncampania($model, $correoCliente);
    public function enviarCorreodeAsignacionGestorcampania($model, $correoGestor);
    public function enviarCorreodeEdicioncampania($model, $correoCliente, $cambios);
    public function enviarCorreoActualizacionCampaniaGestor($model, $correoGestor, $cambios);
    public function enviarCorreoDeCampaniaBloqueo($model, $correoCliente, $motivo);
    public function enviarCorreoDeCampaniaDesBloqueo($model, $correoCliente, $motivo);
    public function enviarCorreoSolicitudFin($asignacion, $correo);

    public function enviarCorreoFinalizacionCampania($campania, $correo);


}
