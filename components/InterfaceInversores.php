<?php

namespace app\components;

use app\models\Inversor;
use app\models\SolicitudInversorForm;
use app\models\Usuarios;

/**
 * Inversores: solicitud (web o panel), verificación de correo, aprobación con áreas, bloqueo e historial.
 * Panel: Superadmin y Distribuidor todo; el gerente ve los de sus áreas y puede cargar solicitudes.
 */
interface InterfaceInversores
{
    /* ---------- Panel ---------- */

    /** @return array ['searchModel', 'dataProvider', 'conteos' => pestaña => n, 'ecosistemas' => id => nombre] */
    public function listar($params, $pestana);

    public function buscar($id, $permiso = 'inversores_ver'): Inversor;

    /** Crea usuario (rol 5) + solicitud; manda el correo para que cree su contraseña. @return array ['exito', 'mensaje', 'id'] */
    public function crearDesdePanel(SolicitudInversorForm $f, array $post);

    public function editar(Inversor $inv, SolicitudInversorForm $f, array $post);

    /** Aprueba con las áreas indicadas (ids de ecosistema). */
    public function aprobar($id, array $areas);

    /** Cambia las áreas de un inversor ya aprobado. */
    public function guardarAreas($id, array $areas);

    public function rechazar($id, $motivo);

    /** Bloquea o reactiva la cuenta (con motivo). */
    public function cambiarEstadoCuenta($id, $motivo);

    /** Reenvía el correo que corresponda (confirmar correo o activar cuenta). */
    public function reenviarCorreo($id);

    public function subirDocumento($id);

    /** ['ruta', 'nombre', 'mime'] del documento de identidad. */
    public function archivoDocumento($id): array;

    public function historial($id);

    /* ---------- Web ---------- */

    /** Registro desde la web. @return array ['exito', 'mensaje'] */
    public function registrar(SolicitudInversorForm $f);

    /** Confirma el correo con el token del enlace. @return Usuarios|null */
    public function verificarCorreo($token);

    /** Vuelve a mandar el correo de confirmación (respuesta siempre igual). */
    public function reenviarVerificacion($email);

    /** "Olvidé mi contraseña" de inversores (respuesta siempre igual). */
    public function pedirRecuperacion($email);

    /** Usuario inversor dueño de un token de recuperación/activación vigente, o null. */
    public function usuarioPorToken($token);

    /** Guarda la contraseña nueva y anula el token (si era activación, también confirma el correo). */
    public function guardarContrasena(Usuarios $u, $contrasena);

    /** ¿Puede entrar? null = sí; si no, el motivo (correo sin confirmar). */
    public function motivoSinAcceso(Usuarios $u);

    /** Datos del inversor logueado. */
    public function perfil($idusuario): ?Inversor;

    /** @return array ['titulo', 'columnas', 'modelos', 'filtros'] para Exportador (pestaña + filtros). */
    public function exportar($params, $pestana);
}
