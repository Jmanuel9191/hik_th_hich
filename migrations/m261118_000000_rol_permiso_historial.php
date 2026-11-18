<?php

use yii\db\Migration;

/**
 * Historial de la pantalla "Roles y permisos": quién dio o quitó qué permiso a qué rol.
 */
class m261118_000000_rol_permiso_historial extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%rol_permiso_historial}}', [
            'idhistorial' => $this->primaryKey(),
            'idrol' => $this->integer()->notNull(),
            'idpermiso' => $this->integer()->notNull(),
            'accion' => $this->string(20)->notNull(), // Asignado | Quitado
            'idusuario_actor' => $this->integer()->null(),
            'ip' => $this->string(45)->null(),
            'fecha' => $this->dateTime()->notNull(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');
        $this->createIndex('idx_rph_fecha', '{{%rol_permiso_historial}}', 'fecha');
    }

    public function safeDown()
    {
        $this->dropTable('{{%rol_permiso_historial}}');
    }
}
