<?php

namespace app\commands;

use app\components\LimpiezaSolicitudes;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Tareas de consola de inversores.
 *   php yii inversores/limpiar        borra las solicitudes web que no confirmaron el correo en 7 días
 *   php yii inversores/limpiar 14     … en 14 días
 * (También se hace sola al registrarse alguien; esto es por si se quiere un cron.)
 */
class InversoresController extends Controller
{
    public function actionLimpiar(int $dias = LimpiezaSolicitudes::DIAS)
    {
        $n = LimpiezaSolicitudes::ejecutar(max(1, $dias));
        $this->stdout("Solicitudes sin confirmar borradas: $n\n");
        return ExitCode::OK;
    }
}
