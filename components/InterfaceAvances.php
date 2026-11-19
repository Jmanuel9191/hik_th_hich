<?php

namespace app\components;

use app\models\Proyecto;
use app\models\ProyectoAvance;

/**
 * Avances (bitácora) de los proyectos. Permisos: ver = proyectos_ver; cargar/editar = proyectos_editar
 * (y el área, igual que el proyecto).
 */
interface InterfaceAvances
{
    /** Avances del proyecto (todos, del más nuevo al más viejo), con sus archivos. @return ProyectoAvance[] */
    public function listar(Proyecto $p): array;

    /** Avance nuevo (sin guardar) del proyecto, con la fecha de hoy. */
    public function nuevo($idproyecto): ProyectoAvance;

    /** Avance o 404 / 403. */
    public function buscar($idavance, $editar = false): ProyectoAvance;

    /** Crea o edita (etapa y % actualizan la ficha del proyecto). @return array ['exito' => bool, 'mensaje' => string] */
    public function guardar(ProyectoAvance $a, array $post);

    /** Muestra u oculta el avance. */
    public function cambiarEstado($idavance);

    /** Borra el avance y sus archivos. */
    public function eliminar($idavance);

    /** Agrega una foto, video, documento (según la extensión) o un enlace de video, con su leyenda opcional. */
    public function agregarArchivo($idavance, array $post);

    public function eliminarArchivo($idarchivo);

    /** Cambia la leyenda (descripción corta) de un archivo del avance. */
    public function cambiarLeyenda($idarchivo, $leyenda);

    /** ['ruta', 'nombre', 'mime', 'inline'] — los documentos registran la descarga. */
    public function archivo($idarchivo): array;

    /** Vuelve a calcular etapa y % del proyecto según sus avances visibles. */
    public function recalcular(Proyecto $p);
}
