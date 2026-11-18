<?php

namespace app\components;

interface InterfaceCatalogoService
{
    // Funciones para gestionar relaciones Proyecto - Sucursales/Sectores
    public function guardarSucursalesProyecto($idproyecto, array $idsucursales);
    public function guardarSectoresProyecto($idproyecto, array $idsectores);

    // Funciones para gestionar relaciones Campaña - Sucursales/Sectores/Audiencias
    public function guardarSucursalesCampania($idcampania, array $idsucursales);
    public function guardarSectoresCampania($idcampania, array $idsectores);
    public function guardarAudienciasCampania($idcampania, array $audiencias);

    // Funciones para obtener nombres de catálogos (por array de IDs)
    public function obtenerNombresSucursales(array $ids): array;
    public function obtenerNombresSectores(array $ids): array;
    public function obtenerNombresAudiencias(array $ids): array;

    // ===============================
    // Nuevas funciones CRUD por catálogo
    // ===============================
    public function limpiarFlashes();
    public function listar(string $tipo, array $queryParams);
    public function crear(string $tipo, array $post);
    public function obtener(string $tipo, int $id);
    public function actualizar(string $tipo, int $id, array $post);
    public function eliminar(string $tipo, int $id);
}
