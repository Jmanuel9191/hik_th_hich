<?php

use yii\db\Migration;

/**
 * Banners por página: columna "ubicacion" (inicio, nosotros, ecosistemas, proyectos, expansion, contacto).
 * Los banners que ya existen quedan en el Inicio.
 */
class m261119_130000_banner_ubicacion extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%banners}}', 'ubicacion', $this->string(20)->notNull()->defaultValue('inicio')->after('idbanner'));
        $this->createIndex('idx_banners_ubicacion', '{{%banners}}', ['ubicacion', 'estado', 'orden']);
    }

    public function safeDown()
    {
        $this->dropIndex('idx_banners_ubicacion', '{{%banners}}');
        $this->dropColumn('{{%banners}}', 'ubicacion');
    }
}
