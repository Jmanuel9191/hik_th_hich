<?php

use yii\db\Migration;

/**
 * Páginas: segunda imagen por bloque, "imagen_firma" (la firma manuscrita del Mensaje del CEO, PNG transparente).
 */
class m261119_140000_ceo_firma_imagen extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%pagina_seccion}}', 'imagen_firma', $this->string(255)->null()->after('imagen'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%pagina_seccion}}', 'imagen_firma');
    }
}
