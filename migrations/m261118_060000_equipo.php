<?php

use yii\db\Migration;

/**
 * Equipo: miembros que se muestran en la web (foto, nombre, puesto, contacto, redes, biografía),
 * con orden, estado e historial. Permisos para Superadmin y Editor de contenido.
 */
class m261118_060000_equipo extends Migration
{
    const PERMISOS = [
        'equipo_listar' => 'Permite ver el listado del equipo',
        'equipo_ver' => 'Permite ver el detalle de un miembro del equipo',
        'equipo_crear' => 'Permite agregar miembros al equipo',
        'equipo_editar' => 'Permite editar miembros del equipo y cambiar su orden',
        'equipo_estado' => 'Permite activar y desactivar miembros del equipo',
        'equipo_eliminar' => 'Permite eliminar miembros del equipo',
        'equipo_historial' => 'Permite ver el historial de un miembro del equipo',
    ];

    public function safeUp()
    {
        $opciones = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        $this->createTable('{{%equipo}}', [
            'idmiembro' => $this->primaryKey(),
            'nombre' => $this->string(150)->notNull(),
            'puesto' => $this->string(150)->notNull(),
            'telefono' => $this->string(50)->null(),
            'email' => $this->string(150)->null(),
            'linkedin' => $this->string(255)->null(),
            'facebook' => $this->string(255)->null(),
            'instagram' => $this->string(255)->null(),
            'twitter' => $this->string(255)->null(),
            'biografia' => $this->text()->null(),
            'foto' => $this->string(255)->null(),
            'orden' => $this->integer()->notNull()->defaultValue(0),
            'estado' => $this->char(1)->notNull()->defaultValue('1'),
            'idusuario_registro' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
            'fecha_actualizacion' => $this->dateTime()->null(),
        ], $opciones);
        $this->createIndex('idx_equipo_orden', '{{%equipo}}', ['estado', 'orden']);

        $this->createTable('{{%equipo_historial}}', [
            'idhistorial' => $this->primaryKey(),
            'idmiembro' => $this->integer()->notNull(),
            'idusuario_actor' => $this->integer()->null(),
            'accion' => $this->string(50)->notNull(),
            'descripcion' => $this->text()->null(),
            'ip' => $this->string(45)->null(),
            'fecha' => $this->dateTime()->notNull(),
        ], $opciones);
        $this->createIndex('idx_eh_miembro', '{{%equipo_historial}}', ['idmiembro', 'fecha']);

        foreach (self::PERMISOS as $alias => $descripcion) {
            $this->insert('{{%permisos}}', ['alias' => $alias, 'descripcion' => $descripcion, 'estado' => '1', 'fecha_registro' => date('Y-m-d H:i:s')]);
            $id = $this->db->getLastInsertID();
            $this->insert('{{%rol_permiso}}', ['idrol' => 1, 'idpermiso' => $id]); // Superadmin
            $this->insert('{{%rol_permiso}}', ['idrol' => 4, 'idpermiso' => $id]); // Editor de contenido
        }
    }

    public function safeDown()
    {
        $ids = (new \yii\db\Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => array_keys(self::PERMISOS)])->column();
        $this->delete('{{%rol_permiso}}', ['idpermiso' => $ids]);
        $this->delete('{{%permisos}}', ['idpermiso' => $ids]);
        $this->dropTable('{{%equipo_historial}}');
        $this->dropTable('{{%equipo}}');
    }
}
