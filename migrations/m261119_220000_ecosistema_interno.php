<?php

use yii\db\Migration;

/**
 * Página interna de cada ecosistema (/ecosistemas/<slug>):
 * etiquetas del banner, países de la visión de expansión, texto del recuadro negro y el bloque "Oportunidades".
 */
class m261119_220000_ecosistema_interno extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%ecosistema}}', 'etiquetas', $this->text()->null()->comment('JSON: lista de etiquetas del banner')->after('resumen'));
        $this->addColumn('{{%ecosistema}}', 'enfoque', $this->text()->null()->comment('Recuadro negro "Enfoque del grupo"')->after('descripcion'));
        $this->addColumn('{{%ecosistema}}', 'paises', $this->string(255)->null()->comment('JSON: códigos de país de la visión de expansión')->after('enfoque'));
        $this->addColumn('{{%ecosistema}}', 'mostrar_oportunidades', $this->char(1)->notNull()->defaultValue('0')->after('paises'));
        $this->addColumn('{{%ecosistema}}', 'oportunidades', $this->text()->null()->comment('Texto del bloque "Oportunidades"')->after('mostrar_oportunidades'));
    }

    public function safeDown()
    {
        foreach (['oportunidades', 'mostrar_oportunidades', 'paises', 'enfoque', 'etiquetas'] as $columna) {
            $this->dropColumn('{{%ecosistema}}', $columna);
        }
    }
}
