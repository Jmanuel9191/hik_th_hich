<?php
namespace app\components;

interface InterfaceCuenta
{
    public function actualizarUsuario($model, $datosPost);
    public function cambiarPassword($usuario, $newPassword);
    public function procesarFormularioCambioPassword($model, $datosPost);
    public function recuperacionCuenta($email);
    public function buscarUsuarioPorTokenRecuperacion($token);
    public function restablecerContrasena($usuario, $nuevaContrasena);
    public function desbloquearintentosFallidos($id);
    public function cambioPasswordObligatorio($usuario, $nuevaContrasena);

    public function verificarUsuarioLogueado();
    public function limpiarFlashes();
}