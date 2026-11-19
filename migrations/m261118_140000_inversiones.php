<?php

use yii\db\Migration;

/**
 * Inversiones: cada pago que hace un inversor en un proyecto (puede hacer varios).
 *  - Se guarda en la moneda en que pagó; monto_equivalente es lo que vale en la moneda del proyecto
 *    (igual al monto si es la misma moneda) y es lo que suma al % de la ronda.
 *  - Un pago no se borra: se anula con motivo (queda tachado y deja de sumar).
 * Comprobantes en privado/inversiones/<idproyecto>/.
 * Además, los permisos de exportar de Inversores, Inversiones, Proyectos y Equipo.
 */
class m261118_140000_inversiones extends Migration
{
    /** alias => [descripción, roles] (1 Superadmin, 2 Distribuidor, 3 Gerente de área, 4 Editor) */
    const PERMISOS = [
        'inversiones_listar' => ['Permite ver las inversiones (el gerente, las de los proyectos de sus áreas)', [1, 2, 3]],
        'inversiones_registrar' => ['Permite registrar pagos de inversores y subir comprobantes', [1, 2, 3]],
        'inversiones_anular' => ['Permite anular un pago (queda tachado, con el motivo)', [1, 2, 3]],
        'inversiones_exportar' => ['Permite exportar las inversiones a Excel, CSV o PDF', [1, 2, 3]],
        'inversores_exportar' => ['Permite exportar los inversores a Excel, CSV o PDF', [1, 2, 3]],
        'proyectos_exportar' => ['Permite exportar los proyectos a Excel, CSV o PDF', [1, 2, 3]],
        'equipo_exportar' => ['Permite exportar el equipo a Excel, CSV o PDF', [1, 4]],
    ];

    public function safeUp()
    {
        $op = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%inversion_pago}}', [
            'idpago' => $this->primaryKey(),
            'idproyecto' => $this->integer()->notNull(),
            'idusuario' => $this->integer()->notNull()->comment('inversor'),
            'fecha_pago' => $this->date()->notNull(),
            'monto' => $this->decimal(15, 2)->notNull(),
            'idmoneda' => $this->integer()->notNull(),
            'monto_equivalente' => $this->decimal(15, 2)->notNull()->comment('en la moneda del proyecto'),
            'idmoneda_proyecto' => $this->integer()->notNull()->comment('moneda del proyecto al registrar'),
            'tipo_pago' => $this->string(20)->notNull(),
            'referencia' => $this->string(150)->null()->comment('banco, nro. de operación o de cheque, detalle'),
            'nota' => $this->string(500)->null(),
            'comprobante_archivo' => $this->string(255)->null(),
            'comprobante_nombre' => $this->string(255)->null(),
            'estado' => $this->string(10)->notNull()->defaultValue('vigente'),
            'motivo_anulacion' => $this->string(500)->null(),
            'idusuario_anulacion' => $this->integer()->null(),
            'fecha_anulacion' => $this->dateTime()->null(),
            'idusuario_registro' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
        ], $op);
        $this->createIndex('idx_pago_proyecto', '{{%inversion_pago}}', ['idproyecto', 'estado']);
        $this->createIndex('idx_pago_inversor', '{{%inversion_pago}}', ['idusuario', 'estado']);
        $this->createIndex('idx_pago_fecha', '{{%inversion_pago}}', 'fecha_pago');
        $this->addForeignKey('fk_pago_proyecto', '{{%inversion_pago}}', 'idproyecto', '{{%proyecto}}', 'idproyecto', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_pago_inversor', '{{%inversion_pago}}', 'idusuario', '{{%usuarios}}', 'idusuario', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_pago_moneda', '{{%inversion_pago}}', 'idmoneda', '{{%moneda}}', 'idmoneda', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_pago_moneda_proy', '{{%inversion_pago}}', 'idmoneda_proyecto', '{{%moneda}}', 'idmoneda', 'RESTRICT', 'CASCADE');

        foreach (self::PERMISOS as $alias => [$descripcion, $roles]) {
            $this->insert('{{%permisos}}', ['alias' => $alias, 'descripcion' => $descripcion, 'estado' => '1', 'fecha_registro' => date('Y-m-d H:i:s')]);
            $id = $this->db->getLastInsertID();
            foreach ($roles as $idrol) {
                $this->insert('{{%rol_permiso}}', ['idrol' => $idrol, 'idpermiso' => $id]);
            }
        }
    }

    public function safeDown()
    {
        $ids = (new \yii\db\Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => array_keys(self::PERMISOS)])->column();
        $this->delete('{{%rol_permiso}}', ['idpermiso' => $ids]);
        $this->delete('{{%permisos}}', ['idpermiso' => $ids]);
        $this->dropTable('{{%inversion_pago}}');
    }
}
