<?php

namespace app\components;

/**
 * Datos comunes de la web pública (header y footer del layout views/layouts/main.php).
 */
interface InterfaceSitio
{
    /**
     * Todo lo que necesita el layout en una sola llamada.
     * @return array [
     *   'config'        => columna => texto (tabla configuracion),
     *   'empresa'       => título de la página (pestaña, header, alt del logo),
     *   'nombreEmpresa' => nombre con el lema (solo el © del pie),
     *   'redes'         => ícono => URL (solo las cargadas),
     *   'pie'           => PaginaSeccion|null (texto y lema del pie),
     *   'ecosistemas'   => Ecosistema[] activos por número,
     *   'imagenes'      => ['logo', 'logoPie', 'favicon', 'fenix'] => URL o null si el archivo no existe,
     * ]
     */
    public function layout(): array;

    /** URL de una imagen fija del layout (logo, logoPie, favicon, fenix), o null si el archivo no existe. */
    public function imagen(string $clave): ?string;
}
