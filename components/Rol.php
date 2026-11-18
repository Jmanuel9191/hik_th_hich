<?php

namespace app\components;

/**
 * Tipos de usuario de HUK (coinciden con roles.idrol en la BD).
 * Usar estas constantes en vez de números sueltos en el código.
 */
final class Rol
{
    const SUPERADMIN = 1;    // Todo
    const DISTRIBUIDOR = 2;  // Aprueba/rechaza inversores y asigna áreas
    const GERENTE = 3;       // Proyectos, documentos e inversiones de sus áreas
    const EDITOR = 4;        // Contenido de la web (logo, banners, textos, equipo)
    const INVERSOR = 5;      // NO entra al panel: usa la web (/cuenta)

    /** Roles que entran al panel admin. */
    const PANEL = [self::SUPERADMIN, self::DISTRIBUIDOR, self::GERENTE, self::EDITOR];

    public static function entraAlPanel($idrol): bool
    {
        return in_array((int) $idrol, self::PANEL, true);
    }
}
