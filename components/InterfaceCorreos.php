<?php
namespace app\components;

interface InterfaceCorreos
{
    public function enviarCorreodeBienvenida($model);
    public function enviarCorreoDeBloqueo($model, $motivo);
    public function enviarCorreoDeInactividad($model, $motivo);
    public function enviarCorreoDedesBloqueo($model, $motivo);


    public function enviarCorreoRecuperacion($id, $enlace);

    /* Inversores (web) */
    public function enviarCorreoVerificacionInversor($usuario, $enlace);
    public function enviarCorreoActivacionInversor($usuario, $enlace);
    public function enviarCorreoRecuperacionInversor($usuario, $enlace);
    /** Formulario de contacto de la web → a la empresa, con "responder a" la persona. @param array $datos nombre, empresa, telefono, email, mensaje */
    public function enviarCorreoContacto(array $datos, string $destino);
    public function enviarCorreoNuevaSolicitudInversor(array $emails, $usuario, $enlacePanel, array $resumen = []);
    public function enviarCorreoInversorAprobado($usuario, array $areas, array $noIncluidas = []);
    public function enviarCorreoInversorRechazado($usuario, $motivo, array $areasPedidas = []);
    public function enviarCorreoAreasActualizadas($usuario, array $ahora, array $nuevas, array $quitadas, array $noAprobadas = []);
    public function enviarCorreoGerenteNuevoInversor($gerente, $inversor, array $areas, $enlacePanel);
    public function enviarCorreoCuentaInversor($usuario, $bloqueada, $motivo);
    public function enviarCorreoDesbloqueoPorIntentos($usuario);
    public function enviarCorreoDeBloqueoporintentos($id);
    /** Un inversor aprobado pide más áreas desde Mi cuenta → al correo del sistema, Superadmin y distribuidores. */
    public function enviarCorreoPedidoAreas(array $emails, $usuario, array $areas, $enlacePanel);
    /** "Quiero invertir": confirmación al inversor (quién y en cuánto tiempo lo contactan). */
    public function enviarCorreoInteresInversor($usuario, $proyecto, $interes, string $quienContacta);
    /** "Quiero invertir": aviso al equipo (gerente del proyecto, gerentes del área, Superadmin, correo del sistema). */
    public function enviarCorreoInteresEquipo(array $emails, $usuario, $proyecto, array $resumen, $enlacePanel, bool $nuevo = true);
    /** Aviso al inversor: cambió su contraseña desde Mi cuenta. */
    public function enviarCorreoContrasenaCambiadaInversor($usuario);


}
