<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Permiso inversores_desbloquear: quitar el bloqueo por intentos fallidos de ingreso de un inversor
 * (Panel → Inversores → ficha → Cuenta). Superadmin y Gerente de área (el gerente, solo de sus áreas).
 * Se puede correr más de una vez.
 */
class m261120_080000_desbloqueo_inversores extends Migration
{
    const ALIAS = 'inversores_desbloquear';
    const ROLES = [1, 3];

    public function safeUp()
    {
        if (!(new Query())->from('{{%permisos}}')->where(['alias' => self::ALIAS])->exists($this->db)) {
            $this->insert('{{%permisos}}', ['alias' => self::ALIAS, 'descripcion' => 'Permite desbloquear a un inversor bloqueado por intentos fallidos de ingreso',
                'estado' => '1', 'fecha_registro' => date('Y-m-d H:i:s')]);
        }
        $id = (new Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => self::ALIAS])->scalar($this->db);
        foreach (self::ROLES as $rol) {
            if (!(new Query())->from('{{%rol_permiso}}')->where(['idrol' => $rol, 'idpermiso' => $id])->exists($this->db)) {
                $this->insert('{{%rol_permiso}}', ['idrol' => $rol, 'idpermiso' => $id]);
            }
        }
    }

    public function safeDown()
    {
        $id = (new Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => self::ALIAS])->scalar($this->db);
        if ($id) {
            $this->delete('{{%rol_permiso}}', ['idpermiso' => $id]);
            $this->delete('{{%permisos}}', ['idpermiso' => $id]);
        }
    }
}
