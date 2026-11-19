<?php

use yii\db\Migration;

/**
 * Banners: antetítulo (texto corto en dorado, arriba del título, con la línea a la izquierda).
 */
class m261118_160000_banner_antetitulo extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%banners}}', 'antetitulo', $this->string(120)->null()->after('titulo'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%banners}}', 'antetitulo');
    }
}
