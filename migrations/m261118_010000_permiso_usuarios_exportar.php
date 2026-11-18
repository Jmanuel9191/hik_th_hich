<?php

use yii\db\Migration;

/** Permiso para exportar el listado de usuarios (Excel, CSV, PDF). Solo Superadmin. */
class m261118_010000_permiso_usuarios_exportar extends Migration
{
    public function safeUp()
    {
        $this->insert('{{%permisos}}', [
            'alias' => 'usuarios_exportar',
            'descripcion' => 'Permite exportar el listado de usuarios (Excel, CSV, PDF)',
            'estado' => '1',
            'fecha_registro' => date('Y-m-d H:i:s'),
        ]);
        $this->insert('{{%rol_permiso}}', ['idrol' => 1, 'idpermiso' => $this->db->getLastInsertID()]);
    }

    public function safeDown()
    {
        $this->delete('{{%permisos}}', ['alias' => 'usuarios_exportar']);
    }
}
