<?php

namespace app\components;

interface InterfaceGoogle
{
    public function getAuthUrl(): string;
    public function verificarUsuarioLogueado();
    public function fetchAccessToken(string $code): array;
// InterfaceGoogle.php
    //public function crearEventoConMeet($usuario, string $titulo, string $descripcion, string $horaInicio, string $horaFin): ?string;
    public function crearEvento(string $token, array $evento): mixed;

// Nuevo método para comprobar si ya se sincronizó
    public function obtenerEventosSincronizados($usuario): array;
    public function registrarEventosSincronizados($usuario, array $eventos): void;





    public function crearEventoConMeet($usuario, string $titulo, string $descripcion, string $horaInicio, string $horaFin): ?string;
    public function sincronizarEventos($usuario): array;

}
