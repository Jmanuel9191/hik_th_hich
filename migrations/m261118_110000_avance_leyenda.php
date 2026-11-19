<?php

use yii\db\Migration;

/**
 * Leyenda (descripción corta) de cada foto, video, documento o enlace de un avance.
 */
class m261118_110000_avance_leyenda extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%proyecto_avance_archivo}}', 'leyenda', $this->string(300)->null()->after('url'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%proyecto_avance_archivo}}', 'leyenda');
    }
}
