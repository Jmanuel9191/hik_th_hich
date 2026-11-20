<?php

namespace app\widgets;

use app\modules\cuenta\Module as Cuenta;
use yii\base\Widget;

/**
 * Llamado a ver los proyectos: una frase y el botón.
 *  · Sin sesión de inversor: "Registrarme" (registro) y "Ya tengo cuenta" (ingresar).
 *  · Con sesión: "Ver proyectos" (/proyectos; si todavía no puede verlos, de ahí lo mandan a Mi cuenta).
 * Va donde lo pongas:  <?= \app\widgets\LlamadoProyectos::widget() ?>
 * Vista: views/site/widgets/llamado_proyectos.php · estilos: huk.css sección 26.
 */
class LlamadoProyectos extends Widget
{
    public function run()
    {
        $sesion = Cuenta::sesion();
        return $this->render('@app/views/site/widgets/llamado_proyectos', [
            'conSesion' => !$sesion->isGuest,
            'nombre' => $sesion->isGuest ? null : (string) ($sesion->identity->nombrecompleto ?? ''),
        ]);
    }
}
