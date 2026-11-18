<?php

use yii\db\Migration;

/**
 * Datos generales de la página (tabla configuracion):
 * - la tabla pasa a InnoDB + utf8mb4 (acentos, guiones largos, emojis);
 * - redes sociales hasta 255 caracteres; otros teléfonos; mapa de Google (solo el enlace "embed");
 * - historial de cambios y permiso configuracion_editar (Superadmin y Editor de contenido).
 */
class m261118_030000_datos_generales extends Migration
{
    const REDES = ['facebook', 'twitter', 'youtube', 'tiktok', 'instagram', 'LinkedIn'];

    public function safeUp()
    {
        $this->execute('ALTER TABLE {{%configuracion}} ENGINE=InnoDB');
        $this->execute('ALTER TABLE {{%configuracion}} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        foreach (self::REDES as $red) {
            $this->alterColumn('{{%configuracion}}', $red, $this->string(255)->notNull()->defaultValue(''));
        }
        $this->addColumn('{{%configuracion}}', 'telefonos_otros', $this->text()->null()->after('telefono_empresa'));
        $this->addColumn('{{%configuracion}}', 'mapa_embed', $this->string(1000)->null()->after('coordgoogle'));

        $this->createTable('{{%configuracion_historial}}', [
            'idhistorial' => $this->primaryKey(),
            'idusuario_actor' => $this->integer()->null(),
            'ip' => $this->string(45)->null(),
            'fecha' => $this->dateTime()->notNull(),
            'detalle' => $this->text()->notNull(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');
        $this->createIndex('idx_ch_fecha', '{{%configuracion_historial}}', 'fecha');

        $this->insert('{{%permisos}}', [
            'alias' => 'configuracion_editar',
            'descripcion' => 'Permite ver y editar los datos generales de la página (título, contacto, redes, mapa)',
            'estado' => '1',
            'fecha_registro' => date('Y-m-d H:i:s'),
        ]);
        $id = $this->db->getLastInsertID();
        $this->insert('{{%rol_permiso}}', ['idrol' => 1, 'idpermiso' => $id]); // Superadmin
        $this->insert('{{%rol_permiso}}', ['idrol' => 4, 'idpermiso' => $id]); // Editor de contenido
    }

    public function safeDown()
    {
        $id = (new \yii\db\Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => 'configuracion_editar'])->scalar();
        if ($id) {
            $this->delete('{{%rol_permiso}}', ['idpermiso' => $id]);
            $this->delete('{{%permisos}}', ['idpermiso' => $id]);
        }
        $this->dropTable('{{%configuracion_historial}}');
        $this->dropColumn('{{%configuracion}}', 'mapa_embed');
        $this->dropColumn('{{%configuracion}}', 'telefonos_otros');
    }
}
