<?php

use yii\db\Migration;

/** Ecosistemas: subtítulo opcional (ej. el nombre en inglés), sale chico debajo del título en las tarjetas. */
class m261119_210000_ecosistema_subtitulo extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%ecosistema}}', 'subtitulo', $this->string(120)->null()->after('nombre'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%ecosistema}}', 'subtitulo');
    }
}
