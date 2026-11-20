<?php

use yii\db\Migration;

/**
 * historial_usuarios.fecha tenía ON UPDATE CURRENT_TIMESTAMP: si se tocaba la fila, cambiaba la fecha del hecho.
 * Se deja como fecha fija (se carga al insertar y no cambia más). Mismo tipo, no se convierten los datos.
 */
class m261119_122000_historial_usuarios_fecha extends Migration
{
    public function up()
    {
        $this->execute('ALTER TABLE {{%historial_usuarios}} MODIFY `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down()
    {
        $this->execute('ALTER TABLE {{%historial_usuarios}} MODIFY `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }
}
