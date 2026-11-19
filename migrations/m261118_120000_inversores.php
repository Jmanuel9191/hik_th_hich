<?php

use yii\db\Migration;

/**
 * Inversores (rol 5) + Acuerdo de confidencialidad (NDA) con versiones.
 *  - La cuenta vive en `usuarios` (idrol 5); los datos de la solicitud en `inversor` (1 a 1).
 *  - inversor_interes: áreas que pidió. inversor_area: áreas aprobadas (las que puede ver).
 *  - inversor_historial: todo lo que pasa con la solicitud.
 *  - nda_version (cada cambio de texto es una versión nueva) y nda_aceptacion (quién aceptó cuál, cuándo, IP).
 * Documento de identidad en privado/inversores/<idusuario>/.
 */
class m261118_120000_inversores extends Migration
{
    /** alias => [descripción, roles] (1 Superadmin, 2 Distribuidor, 3 Gerente de área) */
    const PERMISOS = [
        'inversores_listar' => ['Permite ver el listado de inversores (el gerente solo los de sus áreas)', [1, 2, 3]],
        'inversores_ver' => ['Permite ver la solicitud y los datos de un inversor', [1, 2, 3]],
        'inversores_crear' => ['Permite cargar una solicitud de inversor desde el panel', [1, 2, 3]],
        'inversores_editar' => ['Permite editar los datos de un inversor', [1, 2]],
        'inversores_aprobar' => ['Permite aprobar o rechazar solicitudes y asignar áreas', [1, 2]],
        'inversores_estado' => ['Permite bloquear o activar la cuenta de un inversor', [1, 2]],
        'inversores_historial' => ['Permite ver el historial de un inversor', [1, 2, 3]],
        'nda_editar' => ['Permite publicar nuevas versiones del acuerdo de confidencialidad', [1]],
    ];

    public function safeUp()
    {
        $op = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%inversor}}', [
            'idusuario' => $this->integer()->notNull(),
            'tipo' => $this->string(10)->notNull()->defaultValue('persona'),
            'documento_tipo' => $this->string(20)->notNull(),
            'documento_numero' => $this->string(40)->notNull(),
            'documento_archivo' => $this->string(255)->null(),
            'documento_nombre' => $this->string(255)->null(),
            'razon_social' => $this->string(200)->null(),
            'nit' => $this->string(40)->null(),
            'sitio_web' => $this->string(255)->null(),
            'rango_inversion' => $this->string(20)->notNull(),
            'plazo_preferido' => $this->string(20)->null(),
            'participacion_preferida' => $this->string(20)->null(),
            'como_nos_conocio' => $this->string(150)->null(),
            'mensaje' => $this->text()->null(),
            'estado_solicitud' => $this->string(20)->notNull()->defaultValue('sin_verificar'),
            'motivo_rechazo' => $this->string(500)->null(),
            'origen' => $this->string(10)->notNull()->defaultValue('web'),
            'idusuario_registro' => $this->integer()->null(),
            'terminos_fecha' => $this->dateTime()->null(),
            'terminos_ip' => $this->string(45)->null(),
            'email_verificado' => $this->dateTime()->null(),
            'verificacion_hash' => $this->string(64)->null(),
            'verificacion_expira' => $this->dateTime()->null(),
            'fecha_aprobacion' => $this->dateTime()->null(),
            'idusuario_aprobacion' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
            'fecha_actualizacion' => $this->dateTime()->null(),
        ], $op);
        $this->addPrimaryKey('pk_inversor', '{{%inversor}}', 'idusuario');
        $this->createIndex('idx_inversor_estado', '{{%inversor}}', 'estado_solicitud');
        $this->createIndex('idx_inversor_verif', '{{%inversor}}', 'verificacion_hash');
        $this->addForeignKey('fk_inversor_usuario', '{{%inversor}}', 'idusuario', '{{%usuarios}}', 'idusuario', 'CASCADE', 'CASCADE');

        foreach (['inversor_interes', 'inversor_area'] as $tabla) {
            $this->createTable('{{%' . $tabla . '}}', [
                'idusuario' => $this->integer()->notNull(),
                'idecosistema' => $this->integer()->notNull(),
            ], $op);
            $this->addPrimaryKey('pk_' . $tabla, '{{%' . $tabla . '}}', ['idusuario', 'idecosistema']);
            $this->addForeignKey('fk_' . $tabla . '_usu', '{{%' . $tabla . '}}', 'idusuario', '{{%usuarios}}', 'idusuario', 'CASCADE', 'CASCADE');
            $this->addForeignKey('fk_' . $tabla . '_eco', '{{%' . $tabla . '}}', 'idecosistema', '{{%ecosistema}}', 'idecosistema', 'CASCADE', 'CASCADE');
        }

        $this->createTable('{{%inversor_historial}}', [
            'idhistorial' => $this->primaryKey(),
            'idusuario' => $this->integer()->notNull(),
            'idusuario_actor' => $this->integer()->null(),
            'accion' => $this->string(60)->notNull(),
            'descripcion' => $this->text()->null(),
            'ip' => $this->string(45)->null(),
            'fecha' => $this->dateTime()->notNull(),
        ], $op);
        $this->createIndex('idx_ih_usuario', '{{%inversor_historial}}', ['idusuario', 'fecha']);

        $this->createTable('{{%nda_version}}', [
            'idversion' => $this->primaryKey(),
            'numero' => $this->integer()->notNull(),
            'titulo' => $this->string(200)->notNull(),
            'texto' => 'MEDIUMTEXT NOT NULL',
            'nota' => $this->string(300)->null(),
            'idusuario' => $this->integer()->null(),
            'ip' => $this->string(45)->null(),
            'fecha_publicacion' => $this->dateTime()->notNull(),
        ], $op);
        $this->createIndex('uq_nda_numero', '{{%nda_version}}', 'numero', true);

        $this->createTable('{{%nda_aceptacion}}', [
            'idaceptacion' => $this->primaryKey(),
            'idusuario' => $this->integer()->notNull(),
            'idversion' => $this->integer()->notNull(),
            'firma' => $this->string(150)->notNull(),
            'ip' => $this->string(45)->null(),
            'navegador' => $this->string(255)->null(),
            'fecha' => $this->dateTime()->notNull(),
        ], $op);
        $this->createIndex('uq_nda_acept', '{{%nda_aceptacion}}', ['idusuario', 'idversion'], true);
        $this->addForeignKey('fk_ndaa_version', '{{%nda_aceptacion}}', 'idversion', '{{%nda_version}}', 'idversion', 'RESTRICT', 'CASCADE');

        $this->insert('{{%nda_version}}', [
            'numero' => 1,
            'titulo' => 'Acuerdo de confidencialidad',
            'texto' => self::NDA_MODELO,
            'nota' => 'Texto modelo inicial: revisarlo con el área legal y publicar la versión definitiva.',
            'fecha_publicacion' => date('Y-m-d H:i:s'),
        ]);

        foreach (self::PERMISOS as $alias => [$descripcion, $roles]) {
            $this->insert('{{%permisos}}', ['alias' => $alias, 'descripcion' => $descripcion, 'estado' => '1', 'fecha_registro' => date('Y-m-d H:i:s')]);
            $id = $this->db->getLastInsertID();
            foreach ($roles as $idrol) {
                $this->insert('{{%rol_permiso}}', ['idrol' => $idrol, 'idpermiso' => $id]);
            }
        }
    }

    const NDA_MODELO = '<p><strong>Texto modelo. Debe ser revisado por el área legal antes de usarse como definitivo.</strong></p>'
        . '<h3>1. Objeto</h3><p>El presente acuerdo regula el tratamiento de la información que HUK-THE-ICHI GLOBAL GROUP (en adelante, «HUK») pone a disposición del inversor a través de su plataforma, incluyendo descripciones de proyectos, cifras, estudios, documentos, fotografías, videos y cualquier otro material (en adelante, la «Información Confidencial»).</p>'
        . '<h3>2. Obligación de confidencialidad</h3><p>El inversor se compromete a no divulgar, copiar, reproducir, distribuir ni utilizar la Información Confidencial para fines distintos a la evaluación de una posible inversión con HUK, y a no compartirla con terceros sin autorización previa y por escrito de HUK.</p>'
        . '<h3>3. Uso de la cuenta</h3><p>El acceso es personal e intransferible. El inversor es responsable de mantener la reserva de su contraseña y de toda actividad realizada con su cuenta. HUK registra los accesos y las descargas de documentos.</p>'
        . '<h3>4. Excepciones</h3><p>No se considera confidencial la información que sea de dominio público por causas ajenas al inversor, o que deba ser revelada por mandato de autoridad competente, en cuyo caso el inversor lo comunicará a HUK de inmediato.</p>'
        . '<h3>5. Vigencia</h3><p>Las obligaciones de este acuerdo rigen desde su aceptación y se mantienen durante dos (2) años después de que el inversor deje de tener acceso a la plataforma.</p>'
        . '<h3>6. Incumplimiento</h3><p>El incumplimiento de este acuerdo faculta a HUK a suspender el acceso de forma inmediata y a ejercer las acciones legales que correspondan.</p>'
        . '<h3>7. Aceptación</h3><p>Al marcar la casilla de aceptación y escribir su nombre completo, el inversor declara haber leído y aceptado este acuerdo. Se registran la fecha, la hora, la dirección IP y la versión aceptada.</p>';

    public function safeDown()
    {
        $ids = (new \yii\db\Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => array_keys(self::PERMISOS)])->column();
        $this->delete('{{%rol_permiso}}', ['idpermiso' => $ids]);
        $this->delete('{{%permisos}}', ['idpermiso' => $ids]);
        foreach (['nda_aceptacion', 'nda_version', 'inversor_historial', 'inversor_area', 'inversor_interes', 'inversor'] as $t) {
            $this->dropTable('{{%' . $t . '}}');
        }
    }
}
