<?php

namespace app\components;

use app\models\PaginaItem;
use app\models\PaginaSeccion;

interface InterfacePaginas
{
    /** Configuración de la página (PaginaSeccion::PAGINAS) o 404. */
    public function pagina($pagina): array;

    /** Para el Inicio: tarjetas activas de Nosotros → Quiénes somos (Conectar · Transformar · Multiplicar). @return PaginaItem[] */
    public function tarjetasInicio(): array;

    /** Para el Inicio: resumen de Quiénes somos. Solo bloques visibles; si un bloque está oculto, su parte viene vacía.
     *  @return array ['historia' => PaginaSeccion|null, 'destacados' => PaginaItem[], 'cifras' => PaginaItem[]] */
    public function quienesInicio(): array;

    /**
     * Página Nosotros de la web: solo bloques visibles, con sus ítems activos (si un bloque está oculto, viene null o vacío).
     * @return array [
     *   'quienes' => ['historia', 'destacados', 'cifras'] (mismo formato que quienesInicio()),
     *   'tarjetas' => PaginaItem[], 'bloques' => PaginaItem[] (bloques adicionales),
     *   'proposito', 'vision', 'mision', 'valores', 'principios', 'ceo' => PaginaSeccion|null,
     * ]
     */
    public function nosotrosWeb(): array;

    /**
     * Nosotros → Ejes estratégicos: bloques visibles de Panel → Páginas → Ejes estratégicos (null si el bloque está oculto).
     * @return array ['ejes', 'modelo', 'propuesta', 'expansion', 'fases' => PaginaSeccion|null] (con sus ítems activos)
     */
    public function estrategiaWeb(): array;

    /**
     * Nosotros → Gobierno y socios: bloques visibles de Panel → Páginas → Gobierno y socios (null si está oculto).
     * @return array ['gobierno', 'impacto', 'socios', 'razones' => PaginaSeccion|null] (con sus ítems activos)
     */
    public function gobiernoWeb(): array;

    /**
     * Países de la visión de expansión (Panel → Páginas → Ejes estratégicos → Expansión), activos y en orden.
     * Para elegir en qué países está cada ecosistema. @return array código => enfoque (ej. 'BO' => 'Resources & Development')
     */
    public function paisesExpansion(): array;

    /** Pie de la web: Panel → Páginas → Pie de página (texto y lema). null si el bloque está oculto. */
    public function pieWeb(): ?PaginaSeccion;

    /** Bloques de una pestaña, en el orden de la configuración. @return PaginaSeccion[] clave => bloque */
    public function bloques($pagina, $pestana): array;

    /** Ítems de un bloque (todos, activos e inactivos) + extremos del orden. @return array ['items' => PaginaItem[], 'extremos' => [min, max]] */
    public function items(PaginaSeccion $seccion): array;

    public function buscarSeccion($id): PaginaSeccion;

    /** Guarda textos e imagen del bloque. @return array ['exito' => bool, 'mensaje' => string] */
    public function guardarSeccion(PaginaSeccion $seccion, array $post);

    /** Muestra u oculta el bloque entero en la web. */
    public function cambiarEstadoSeccion($id);

    public function buscarItem($id): PaginaItem;

    /** Ítem nuevo (sin guardar) para un bloque que tenga lista, o 404. */
    public function nuevoItem($idseccion): PaginaItem;

    /** Crea o edita un ítem (imágenes incluidas). */
    public function guardarItem(PaginaItem $item, array $post);

    public function cambiarEstadoItem($id);

    /** Sube (-1) o baja (+1) un lugar dentro de su lista. */
    public function moverItem($id, $direccion);

    /** Borra el ítem y sus imágenes. */
    public function eliminarItem($id);

    public function historial($pagina);
}
