<?php

use yii\db\Migration;

/**
 * Banners de la portada: imagen (escritorio y celular), textos opcionales, botón, orden,
 * estado y fechas de vigencia opcionales. Historial y permisos (Superadmin y Editor de contenido).
 */
class m261118_040000_banners extends Migration
{
    const PERMISOS = [
        'banners_listar' => 'Permite ver el listado de banners',
        'banners_ver' => 'Permite ver el detalle de un banner',
        'banners_crear' => 'Permite crear banners',
        'banners_editar' => 'Permite editar banners y cambiar su orden',
        'banners_estado' => 'Permite activar y desactivar banners',
        'banners_eliminar' => 'Permite eliminar banners',
        'banners_historial' => 'Permite ver el historial de un banner',
    ];

    public function safeUp()
    {
        $opciones = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        $this->createTable('{{%banners}}', [
            'idbanner' => $this->primaryKey(),
            'titulo' => $this->string(150)->null(),
            'texto' => $this->string(400)->null(),
            'boton_texto' => $this->string(60)->null(),
            'boton_url' => $this->string(500)->null(),
            'imagen' => $this->string(255)->notNull(),
            'imagen_movil' => $this->string(255)->null(),
            'orden' => $this->integer()->notNull()->defaultValue(0),
            'estado' => $this->char(1)->notNull()->defaultValue('1'),
            'fecha_inicio' => $this->date()->null(),
            'fecha_fin' => $this->date()->null(),
            'idusuario_registro' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
            'fecha_actualizacion' => $this->dateTime()->null(),
        ], $opciones);
        $this->createIndex('idx_banners_orden', '{{%banners}}', ['estado', 'orden']);

        $this->createTable('{{%banner_historial}}', [
            'idhistorial' => $this->primaryKey(),
            'idbanner' => $this->integer()->notNull(),
            'idusuario_actor' => $this->integer()->null(),
            'accion' => $this->string(50)->notNull(),
            'descripcion' => $this->text()->null(),
            'ip' => $this->string(45)->null(),
            'fecha' => $this->dateTime()->notNull(),
        ], $opciones);
        $this->createIndex('idx_bh_banner', '{{%banner_historial}}', ['idbanner', 'fecha']);

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
        $this->dropTable('{{%banner_historial}}');
        $this->dropTable('{{%banners}}');
    }
}
