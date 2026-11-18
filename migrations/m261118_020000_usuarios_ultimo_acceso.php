<?php

use yii\db\Migration;

/** Último acceso al panel (fecha e IP). Lo guarda AuthService al ingresar bien. */
class m261118_020000_usuarios_ultimo_acceso extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%usuarios}}', 'ultimo_acceso', $this->dateTime()->null()->after('fecha_registro'));
        $this->addColumn('{{%usuarios}}', 'ultimo_acceso_ip', $this->string(45)->null()->after('ultimo_acceso'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%usuarios}}', 'ultimo_acceso_ip');
        $this->dropColumn('{{%usuarios}}', 'ultimo_acceso');
    }
}
