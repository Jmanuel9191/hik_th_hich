<?php

namespace app\components;

use app\models\ProyectoPlantilla;

/**
 * Plantillas de página de proyecto (Panel → Proyectos → Plantillas). Permiso: plantillas_editar.
 * El contenido (secciones y bloques) se edita con InterfacePaginaProyecto.
 */
interface InterfacePlantillas
{
    /** @return array ['plantillas' => ProyectoPlantilla[], 'secciones' => idplantilla => cantidad] */
    public function listar(): array;

    public function buscar($id): ProyectoPlantilla;

    public function nueva(): ProyectoPlantilla;

    /** Nombre, descripción, ecosistema sugerido y si está activa. */
    public function guardar(ProyectoPlantilla $t, array $post): array;

    /** Copia la plantilla con sus secciones, bloques y fotos ("… (copia)"). */
    public function duplicar($id): array;

    public function mover($id, $direccion): array;

    /** Borra la plantilla y sus fotos (los proyectos que la usaron no cambian: ya tienen su copia). */
    public function eliminar($id): array;
}
