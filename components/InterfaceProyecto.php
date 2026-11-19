<?php

namespace app\components;

use app\models\Proyecto;

/**
 * Banco de proyectos (admin). Todas las búsquedas respetan el área: el gerente solo ve y toca
 * los proyectos de sus ecosistemas; Superadmin y Distribuidor ven todos.
 */
interface InterfaceProyecto
{
    /** @return array ['searchModel', 'dataProvider', 'ecosistemas' => id => nombre (los que puede ver)] */
    public function listar($params);

    /** Proyecto o 404 / 403 (permiso + área). */
    public function buscar($id, $permiso = 'proyectos_ver'): Proyecto;

    /** Proyecto nuevo con valores por defecto (tipo financiamiento, primera moneda). */
    public function nuevo(): Proyecto;

    /** id => "01 · Nombre": ecosistemas donde el usuario puede crear o mover proyectos. */
    public function ecosistemasPermitidos(): array;

    /** id => "Nombre (Área 1, Área 2)": gerentes activos que pueden ser responsables. */
    public function gerentes(): array;

    /** ¿Puede editar este proyecto? (permiso + área) */
    public function puedeEditar(Proyecto $p): bool;

    /** ¿Puede eliminarlo? (el gerente solo en borrador; el Superadmin siempre) */
    public function puedeEliminar(Proyecto $p): bool;

    /** Estados a los que puede pasar desde el actual: estado => [texto, ícono, clase, aviso]. */
    public function transiciones(Proyecto $p): array;

    /** Crea o edita la ficha (imágenes incluidas). @return array ['exito' => bool, 'mensaje' => string] */
    public function guardar(Proyecto $p, array $post);

    public function cambiarEstado($id, $estado);

    public function eliminar($id);

    /* Galería (Kartik FileInput: [] = ok, ['error' => '…']) */
    public function subirGaleria($id);
    public function borrarImagenGaleria($idimagen);

    /* Videos */
    public function agregarVideo($id, array $post);
    public function moverVideo($idvideo, $direccion);
    public function eliminarVideo($idvideo);

    /* Documentos */
    public function agregarDocumento($id, array $post);
    public function cambiarVisibilidadDocumento($iddocumento);
    public function eliminarDocumento($iddocumento);
    /** iddocumento => cantidad de descargas */
    public function descargasPorDocumento($id): array;

    /* Archivos protegidos: ['ruta', 'nombre', 'mime'] */
    public function archivoImagen($id, $campo): array;
    public function archivoGaleria($idimagen): array;
    public function archivoVideo($idvideo): array;
    /** Registra la descarga. */
    public function archivoDocumento($iddocumento): array;

    public function historial($id);

    /** @return array ['titulo', 'columnas', 'modelos', 'filtros'] para Exportador (con los filtros del listado). */
    public function exportar($params);
}
