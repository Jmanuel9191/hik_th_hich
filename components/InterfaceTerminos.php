<?php

namespace app\components;

use app\models\TerminosVersion;

/**
 * Términos de uso y política de privacidad del registro de inversores, con versiones (como el NDA).
 * La vigente se muestra en el formulario de registro (modal) y cada inversor guarda la versión que aceptó.
 */
interface InterfaceTerminos
{
    public function vigente(): ?TerminosVersion;

    /** @return array ['versiones' => TerminosVersion[], 'aceptaciones' => numero => cantidad de inversores] */
    public function resumen(): array;

    /** Borrador de la versión siguiente (con el texto de la vigente). */
    public function borrador(): TerminosVersion;

    /** Publica la versión nueva (permiso terminos_editar). @return array ['exito' => bool, 'mensaje' => string] */
    public function publicar(TerminosVersion $v, array $post);
}
