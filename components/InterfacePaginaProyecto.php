<?php

namespace app\components;

use app\models\Proyecto;
use app\models\ProyectoBloque;
use app\models\ProyectoPlantilla;

/**
 * Editor de la página web de un proyecto y de las plantillas (panel). Un "contenedor" es un proyecto
 * o una plantilla: tienen secciones y las secciones tienen bloques.
 *  - Proyecto: permiso proyectos_pagina (+ el área si es gerente). Con proyectos_ver solo se mira.
 *  - Plantilla: permiso plantillas_editar.
 * Las acciones devuelven ['exito' => bool, 'mensaje' => string] (+ 'errores' en los formularios).
 */
interface InterfacePaginaProyecto
{
    const TIPOS = ['proyecto', 'plantilla'];

    /** Proyecto para editar su página (o 403 / 404). Con $soloVer alcanza proyectos_ver. */
    public function proyecto($id, bool $soloVer = false): Proyecto;

    public function puedeEditar(Proyecto $p): bool;

    /** Plantilla para editar (o 403 / 404). */
    public function plantilla($id): ProyectoPlantilla;

    /**
     * Datos del editor: ['tipo', 'contenedor', 'secciones' (con bloques), 'banner' (BannerProyectoForm|null),
     *  'plantillas' (id => nombre), 'sugerida' (id|null), 'medios' (MediosProyecto)].
     */
    public function editor(string $tipo, int $id, bool $soloVer = false): array;

    public function guardarBanner($idproyecto, array $post): array;

    /* Secciones */
    public function formularioSeccion(string $tipo, int $id, $idseccion = null): array;
    public function guardarSeccion(string $tipo, int $id, $idseccion, array $post): array;
    public function moverSeccion($idseccion, $direccion): array;
    public function duplicarSeccion($idseccion): array;
    public function cambiarVisibilidadSeccion($idseccion): array;
    public function eliminarSeccion($idseccion): array;

    /* Bloques */
    /** ['bloque' => ProyectoBloque, 'contenido' => Bloque, 'medios' => MediosProyecto] para el formulario. */
    public function formularioBloque($idseccion, $tipo, $idbloque = null): array;
    /** Guarda (crea si no hay $idbloque). Las fotos llegan en $_FILES['archivos'][ruta]. */
    public function guardarBloque($idseccion, $tipo, $idbloque, array $post): array;
    public function moverBloque($idbloque, $direccion): array;
    public function duplicarBloque($idbloque): array;
    public function cambiarVisibilidadBloque($idbloque): array;
    public function eliminarBloque($idbloque): array;

    /* Plantillas */
    /** Copia las secciones de la plantilla al proyecto ($reemplazar: borra antes las que tenga). */
    public function aplicarPlantilla($idproyecto, $idplantilla, bool $reemplazar): array;
    /** Crea una plantilla nueva con las secciones del proyecto (copia también las fotos de los bloques). */
    public function guardarComoPlantilla($idproyecto, array $post): array;

    /** Foto de un bloque o del proyecto para el panel: ['ruta', 'mime']. */
    public function archivoImagen(string $tipo, int $id, string $carpeta, string $archivo, int $ancho): array;
}
