<?php

namespace app\components;

use app\models\Proyecto;

/**
 * Proyectos en la web pública (/proyectos y la página de cada uno), solo para el inversor que puede verlos:
 * cuenta de inversor activa, correo confirmado, solicitud aprobada, NDA vigente aceptado y el ecosistema
 * del proyecto entre sus áreas. Se ven los proyectos "publicados" y los de "inversión completa".
 */
interface InterfaceProyectosWeb
{
    /** Estados de acceso del visitante. */
    const VISITANTE = 'visitante';   // sin sesión de inversor
    const SIN_ACCESO = 'sin_acceso'; // sesión, pero cuenta bloqueada o sin confirmar
    const PENDIENTE = 'pendiente';   // solicitud en revisión o rechazada
    const NDA = 'nda';               // aprobado, falta aceptar el NDA vigente
    const OK = 'ok';

    /** @return array ['estado' => VISITANTE|SIN_ACCESO|PENDIENTE|NDA|OK, 'inversor' => Inversor|null, 'areas' => int[]] */
    public function acceso(): array;

    /**
     * Listado con buscador y filtro por ecosistema (slug de una de sus áreas; otro slug → 404).
     * @return array ['dataProvider', 'ecosistema' => Ecosistema|null, 'busqueda' => string]
     */
    public function listado(array $acceso, ?string $busqueda, ?string $ecosistema): array;

    /** Barra lateral: [[Ecosistema, cantidad], …] de sus áreas. */
    public function barraLateral(array $acceso): array;

    /** Proyecto visible para este inversor, o 404 (nunca dice si existe). */
    public function porSlug(array $acceso, string $slug): Proyecto;

    /**
     * Página armada (secciones visibles con su número, filas de bloques, menú de anclas y la biblioteca: fotos, videos y documentos que usan los bloques).
     * La usan la web y la vista previa del panel.
     * @return array ['ficha' => ?array (Proyecto::datosFicha), 'secciones' => [['seccion' => ProyectoSeccion, 'filas' => array]], 'menu' => [ancla => nombre], 'biblioteca' => ['fotos' => ProyectoImagen[], 'videos' => ProyectoVideo[], 'documentos' => ProyectoDocumento[]]]
     */
    public function armarPagina(Proyecto $p): array;

    /** Carrusel del ecosistema: ['proyectos' => Proyecto[], 'total' => int] o null si no tiene acceso o no hay. */
    public function carrusel(int $idecosistema): ?array;

    /** Inicio: destacados de todas sus áreas (hasta 9; si no marcó ninguno, los últimos). null = no puede ver proyectos. */
    public function destacadosInicio(): ?array;

    /** idecosistema => cantidad de proyectos visibles (panel /cuenta). */
    public function cantidadesPorArea(array $areas): array;

    /** Foto protegida (achicada): ['ruta', 'mime']. */
    public function archivoImagen(array $acceso, int $idproyecto, string $carpeta, string $archivo, int $ancho): array;

    /** Documento visible para el inversor (registra la descarga): ['ruta', 'nombre', 'mime', 'inline' (PDF y fotos se ven)]. */
    public function archivoDocumento(array $acceso, int $iddocumento): array;

    /** Video subido de un proyecto que puede ver: ['ruta', 'mime']. */
    public function archivoVideo(array $acceso, int $idvideo): array;
}
