<?php

namespace app\components;

use app\models\InteresInversion;
use app\models\Inversor;
use app\models\Proyecto;
use app\models\QuieroInvertirForm;

/**
 * "Quiero invertir": el inversor manifiesta interés en un proyecto (web) y el equipo le hace el seguimiento (panel).
 */
interface InterfaceInteresInversion
{
    /* ---------- Web (inversor) ---------- */

    /** Su interés en ese proyecto, o null. */
    public function dePara(int $idusuario, int $idproyecto): ?InteresInversion;

    /** ¿Se muestra el botón? (no si la inversión está completa y no tiene uno en curso) */
    public function puedeEnviar(Proyecto $p, ?InteresInversion $previo): bool;

    /** Guarda (o actualiza) su interés y avisa por correo. @return array ['exito', 'mensaje', 'interes'] */
    public function enviar(Inversor $inv, Proyecto $p, QuieroInvertirForm $f, array $post): array;

    /** Todos sus intereses (Mi cuenta → Mis inversiones). @return InteresInversion[] */
    public function deInversor(int $idusuario): array;

    /* ---------- Panel ---------- */

    /** Interesados de un proyecto, con su historial. @return InteresInversion[] */
    public function deProyecto(Proyecto $p): array;

    public function puedeGestionar(): bool;

    /** Cambia el estado y/o agrega una nota (permiso interesados_gestionar, gerente solo de sus áreas). @return array ['exito', 'mensaje', 'idproyecto'] */
    public function seguimiento(int $idinteres, array $post): array;
}
