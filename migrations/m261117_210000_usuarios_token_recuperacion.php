<?php

use yii\db\Migration;

/**
 * Recuperar contraseña con enlace de 30 minutos.
 * Guarda el HASH del token (nunca el token) y su fecha de vencimiento.
 */
class m261117_210000_usuarios_token_recuperacion extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%usuarios}}', 'reset_token_hash', $this->string(64)->null()->defaultValue(null));
        $this->addColumn('{{%usuarios}}', 'reset_token_expira', $this->dateTime()->null()->defaultValue(null));
        $this->createIndex('idx_usuarios_reset_token_hash', '{{%usuarios}}', 'reset_token_hash');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_usuarios_reset_token_hash', '{{%usuarios}}');
        $this->dropColumn('{{%usuarios}}', 'reset_token_expira');
        $this->dropColumn('{{%usuarios}}', 'reset_token_hash');
    }
}
