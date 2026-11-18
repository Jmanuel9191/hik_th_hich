<?php

namespace app\components;

use app\models\DatosGeneralesForm;

interface InterfaceConfiguracion
{
    /** Formulario cargado con los datos actuales. */
    public function formulario(): DatosGeneralesForm;

    /** Valida y guarda; deja historial de lo que cambió. */
    public function guardar(DatosGeneralesForm $form, array $post): bool;

    /** Historial de cambios (más nuevo primero). */
    public function historial();
}
