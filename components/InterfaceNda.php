<?php

namespace app\components;

use app\models\NdaVersion;

/**
 * Acuerdo de confidencialidad (NDA) con versiones. Cada cambio de texto es una versión nueva:
 * los inversores aprobados la aceptan al entrar (queda versión, firma, fecha, IP y navegador).
 */
interface InterfaceNda
{
    public function vigente(): ?NdaVersion;

    /** @return array ['versiones' => NdaVersion[], 'aceptaciones' => idversion => cantidad, 'pendientes' => inversores aprobados sin la vigente] */
    public function resumen(): array;

    /** Borrador de la versión siguiente (con el texto de la vigente). */
    public function borrador(): NdaVersion;

    /** Publica la versión nueva. @return array ['exito' => bool, 'mensaje' => string] */
    public function publicar(NdaVersion $v, array $post);

    /** El inversor acepta la versión vigente. @return array ['exito' => bool, 'mensaje' => string] */
    public function aceptar($idusuario, $firma, $acepto);

    /** Aceptaciones de un inversor (con su versión). */
    public function aceptacionesDe($idusuario): array;
}
