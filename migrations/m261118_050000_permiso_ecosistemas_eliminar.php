<?php

use yii\db\Migration;

/**
 * Permiso para eliminar ecosistemas (solo si no tienen proyectos). Superadmin y Editor de contenido.
 * El historial deja de borrarse en cascada: así queda constancia de quién eliminó qué.
 */
class m261118_050000_permiso_ecosistemas_eliminar extends Migration
{
    public function safeUp()
    {
        $this->insert('{{%permisos}}', [
            'alias' => 'ecosistemas_eliminar',
            'descripcion' => 'Permite eliminar ecosistemas que no tengan proyectos asignados',
            'estado' => '1',
            'fecha_registro' => date('Y-m-d H:i:s'),
        ]);
        $id = $this->db->getLastInsertID();
        $this->insert('{{%rol_permiso}}', ['idrol' => 1, 'idpermiso' => $id]);
        $this->insert('{{%rol_permiso}}', ['idrol' => 4, 'idpermiso' => $id]);
        $this->dropForeignKey('fk_ecohist_eco', '{{%ecosistema_historial}}');
    }

    public function safeDown()
    {
        $this->addForeignKey('fk_ecohist_eco', '{{%ecosistema_historial}}', 'idecosistema', '{{%ecosistema}}', 'idecosistema', 'CASCADE', 'CASCADE');
        $id = (new \yii\db\Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => 'ecosistemas_eliminar'])->scalar();
        $this->delete('{{%rol_permiso}}', ['idpermiso' => $id]);
        $this->delete('{{%permisos}}', ['idpermiso' => $id]);
    }
}
