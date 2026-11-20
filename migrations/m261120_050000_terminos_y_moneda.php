<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Registro de inversores:
 *  - Términos de uso y política de privacidad administrables (Configuración → Términos y privacidad), con versiones
 *    como el NDA: no se editan, se publica una nueva. Se guarda qué versión aceptó cada inversor al registrarse.
 *    Arranca con la versión 1 = el texto que estaba fijo en el formulario.
 *  - Moneda del monto que piensa invertir (las de Configuración → Monedas). Los que ya estaban: USD.
 *  - Permiso terminos_editar (Superadmin).
 * Se puede correr sobre una base que ya tenga parte de esto.
 */
class m261120_050000_terminos_y_moneda extends Migration
{
    const TEXTO_V1 = '<h3>Términos de uso</h3>'
        . '<p>Al enviar su solicitud acepta que HUK-THE-ICHI GLOBAL GROUP evalúe su perfil como inversor y se comunique con usted por los medios que nos proporcionó.</p>'
        . '<p>El acceso a los proyectos se habilita solo si su solicitud es aprobada y requiere aceptar, además, un acuerdo de confidencialidad.</p>'
        . '<h3>Política de privacidad</h3>'
        . '<p>Usamos sus datos solo para evaluar su perfil como inversor, comunicarnos con usted y darle acceso a la plataforma. '
        . 'No los vendemos ni los compartimos con terceros sin su autorización. Su documento de identidad solo lo ve el equipo de HUK.</p>'
        . '<p>Puede pedir que corrijamos o borremos sus datos escribiéndonos a los medios de contacto de la web.</p>';

    public function safeUp()
    {
        $ahora = date('Y-m-d H:i:s');
        $opciones = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        if ($this->db->getTableSchema('{{%terminos_version}}', true) === null) {
            $this->createTable('{{%terminos_version}}', [
                'idversion' => $this->primaryKey(),
                'numero' => $this->integer()->notNull()->unique(),
                'titulo' => $this->string(200)->notNull(),
                'texto' => 'MEDIUMTEXT NOT NULL',
                'nota' => $this->string(300)->null(),
                'idusuario' => $this->integer()->null(),
                'ip' => $this->string(45)->null(),
                'fecha_publicacion' => $this->dateTime()->notNull(),
            ], $opciones);
        }
        if (!(new Query())->from('{{%terminos_version}}')->exists($this->db)) {
            $this->insert('{{%terminos_version}}', [
                'numero' => 1, 'titulo' => 'Términos de uso y política de privacidad', 'texto' => self::TEXTO_V1,
                'nota' => 'Texto inicial', 'fecha_publicacion' => $ahora,
            ]);
        }

        $inversor = $this->db->getTableSchema('{{%inversor}}', true);
        if ($inversor->getColumn('terminos_version') === null) {
            $this->addColumn('{{%inversor}}', 'terminos_version', $this->integer()->null()->comment('versión de los términos que aceptó')->after('terminos_ip'));
            // los que ya aceptaron (registro web) aceptaron el texto que hoy es la versión 1
            $this->update('{{%inversor}}', ['terminos_version' => 1], ['not', ['terminos_fecha' => null]]);
        }
        if ($inversor->getColumn('idmoneda') === null) {
            $this->addColumn('{{%inversor}}', 'idmoneda', $this->integer()->null()->comment('moneda del monto a invertir')->after('monto_maximo'));
            $usd = (new Query())->select('idmoneda')->from('{{%moneda}}')->where(['codigo' => 'USD'])->scalar($this->db);
            if ($usd) {
                $this->update('{{%inversor}}', ['idmoneda' => (int) $usd]);
            }
        }

        if (!(new Query())->from('{{%permisos}}')->where(['alias' => 'terminos_editar'])->exists($this->db)) {
            $this->insert('{{%permisos}}', ['alias' => 'terminos_editar', 'descripcion' => 'Permite publicar nuevas versiones de los términos de uso y la política de privacidad',
                'estado' => '1', 'fecha_registro' => $ahora]);
        }
        $id = (new Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => 'terminos_editar'])->scalar($this->db);
        if (!(new Query())->from('{{%rol_permiso}}')->where(['idrol' => 1, 'idpermiso' => $id])->exists($this->db)) {
            $this->insert('{{%rol_permiso}}', ['idrol' => 1, 'idpermiso' => $id]);
        }
    }

    public function safeDown()
    {
        $id = (new Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => 'terminos_editar'])->scalar($this->db);
        if ($id) {
            $this->delete('{{%rol_permiso}}', ['idpermiso' => $id]);
            $this->delete('{{%permisos}}', ['idpermiso' => $id]);
        }
        $inversor = $this->db->getTableSchema('{{%inversor}}', true);
        foreach (['idmoneda', 'terminos_version'] as $c) {
            if ($inversor->getColumn($c) !== null) {
                $this->dropColumn('{{%inversor}}', $c);
            }
        }
        if ($this->db->getTableSchema('{{%terminos_version}}', true) !== null) {
            $this->dropTable('{{%terminos_version}}');
        }
    }
}
