<?php

use yii\db\Migration;

/**
 * Footer de la web: texto debajo del logo (Páginas → Inicio → Pie de página → "Texto").
 * Solo carga el texto inicial si el bloque no tiene uno.
 */
class m261118_150000_pie_texto extends Migration
{
    const TEXTO = 'Plataforma corporativa internacional de desarrollo e inversión. Diez ecosistemas empresariales bajo una gobernanza común.';

    public function safeUp()
    {
        $this->update('{{%pagina_seccion}}', ['texto' => self::TEXTO], ['and', ['pagina' => 'inicio', 'clave' => 'pie'], ['texto' => null]]);
    }

    public function safeDown()
    {
        $this->update('{{%pagina_seccion}}', ['texto' => null], ['pagina' => 'inicio', 'clave' => 'pie', 'texto' => self::TEXTO]);
    }
}
