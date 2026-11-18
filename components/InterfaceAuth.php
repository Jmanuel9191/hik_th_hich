<?php
namespace app\components;

interface InterfaceAuth
{
    public function login($model, InterfaceCorreos $correoService, array $rolesPermitidos = Rol::PANEL);
    public function getRedirectRoute($user);
    public function logout();
}