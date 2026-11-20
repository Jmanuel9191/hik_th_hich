<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * "Quiero invertir": manifestación de interés del inversor en un proyecto (no es un compromiso ni un pago).
 *  - interes_inversion: uno por inversor y proyecto (si lo vuelve a enviar, se actualiza).
 *    monto NULL = "a conversar". Estados: nuevo → contacto → negociacion → concretado | descartado.
 *  - interes_inversion_nota: historial (cambios de estado, notas internas del equipo, cambios del inversor).
 *  - Permisos: interesados_ver (Superadmin, Distribuidor, Gerente) e interesados_gestionar (Superadmin, Gerente).
 * Se puede correr sobre una base que ya tenga parte de esto.
 */
class m261120_090000_interes_inversion extends Migration
{
    const PERMISOS = [
        'interesados_ver' => ['Permite ver a los inversores interesados en cada proyecto ("Quiero invertir")', [1, 2, 3]],
        'interesados_gestionar' => ['Permite cambiar el estado y anotar el seguimiento de los interesados', [1, 3]],
    ];

    public function safeUp()
    {
        $opciones = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;
        if ($this->db->getTableSchema('{{%interes_inversion}}', true) === null) {
            $this->createTable('{{%interes_inversion}}', [
                'idinteres' => $this->primaryKey(),
                'idproyecto' => $this->integer()->notNull(),
                'idusuario' => $this->integer()->notNull()->comment('inversor'),
                'monto' => $this->decimal(15, 2)->null()->comment('NULL = a conversar'),
                'idmoneda' => $this->integer()->null()->comment('moneda del proyecto'),
                'participacion' => $this->string(20)->null(),
                'plazo' => $this->string(20)->null(),
                'contacto_medio' => $this->string(20)->notNull(),
                'contacto_horario' => $this->string(100)->null(),
                'comentario' => $this->string(1000)->null(),
                'estado' => $this->string(20)->notNull()->defaultValue('nuevo'),
                'ip' => $this->string(45)->null(),
                'fecha_registro' => $this->dateTime()->notNull(),
                'fecha_actualizacion' => $this->dateTime()->notNull(),
            ], $opciones);
            $this->createIndex('uq_interes_proyecto_usuario', '{{%interes_inversion}}', ['idproyecto', 'idusuario'], true);
            $this->createIndex('idx_interes_estado', '{{%interes_inversion}}', ['estado']);
            $this->addForeignKey('fk_interes_proyecto', '{{%interes_inversion}}', 'idproyecto', '{{%proyecto}}', 'idproyecto', 'CASCADE', 'CASCADE');
            $this->addForeignKey('fk_interes_usuario', '{{%interes_inversion}}', 'idusuario', '{{%usuarios}}', 'idusuario', 'CASCADE', 'CASCADE');
        }
        if ($this->db->getTableSchema('{{%interes_inversion_nota}}', true) === null) {
            $this->createTable('{{%interes_inversion_nota}}', [
                'idnota' => $this->primaryKey(),
                'idinteres' => $this->integer()->notNull(),
                'idusuario' => $this->integer()->null()->comment('quién lo hizo'),
                'tipo' => $this->string(20)->notNull()->comment('estado | nota | inversor'),
                'texto' => $this->string(1000)->notNull(),
                'fecha' => $this->dateTime()->notNull(),
            ], $opciones);
            $this->createIndex('idx_nota_interes', '{{%interes_inversion_nota}}', ['idinteres', 'fecha']);
            $this->addForeignKey('fk_nota_interes', '{{%interes_inversion_nota}}', 'idinteres', '{{%interes_inversion}}', 'idinteres', 'CASCADE', 'CASCADE');
        }
        $ahora = date('Y-m-d H:i:s');
        foreach (self::PERMISOS as $alias => [$descripcion, $roles]) {
            if (!(new Query())->from('{{%permisos}}')->where(['alias' => $alias])->exists($this->db)) {
                $this->insert('{{%permisos}}', ['alias' => $alias, 'descripcion' => $descripcion, 'estado' => '1', 'fecha_registro' => $ahora]);
            }
            $id = (new Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => $alias])->scalar($this->db);
            foreach ($roles as $rol) {
                if (!(new Query())->from('{{%rol_permiso}}')->where(['idrol' => $rol, 'idpermiso' => $id])->exists($this->db)) {
                    $this->insert('{{%rol_permiso}}', ['idrol' => $rol, 'idpermiso' => $id]);
                }
            }
        }
    }

    public function safeDown()
    {
        $ids = (new Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => array_keys(self::PERMISOS)])->column($this->db);
        if ($ids) {
            $this->delete('{{%rol_permiso}}', ['idpermiso' => $ids]);
            $this->delete('{{%permisos}}', ['idpermiso' => $ids]);
        }
        $this->dropTable('{{%interes_inversion_nota}}');
        $this->dropTable('{{%interes_inversion}}');
    }
}
