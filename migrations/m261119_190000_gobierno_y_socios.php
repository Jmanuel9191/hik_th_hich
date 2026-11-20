<?php

use yii\db\Migration;

/**
 * Nosotros → Gobierno y socios (reemplaza a Preguntas frecuentes en el submenú).
 * Textos de cabecera nuevos (solo si están vacíos) y los banners de "Preguntas" pasan a "Gobierno".
 * Las preguntas frecuentes quedan guardadas en la base, pero ya no salen en el panel ni en la web.
 */
class m261119_190000_gobierno_y_socios extends Migration
{
    public function safeUp()
    {
        $this->update('{{%pagina_seccion}}', ['firma' => 'Gobierno Corporativo y ESG'], ['pagina' => 'gobierno', 'clave' => 'gobierno', 'firma' => null]);
        $this->update('{{%pagina_seccion}}', ['subtitulo' => 'Crecimiento con propósito'], ['pagina' => 'gobierno', 'clave' => 'gobierno', 'subtitulo' => null]);
        $this->update('{{%pagina_seccion}}', ['subtitulo' => 'Un socio estratégico para crecer'], ['pagina' => 'gobierno', 'clave' => 'socios', 'subtitulo' => null]);
        $this->update('{{%banners}}', ['ubicacion' => 'nosotros-gobierno'], ['ubicacion' => 'nosotros-faq']);
    }

    public function safeDown()
    {
        $this->update('{{%banners}}', ['ubicacion' => 'nosotros-faq'], ['ubicacion' => 'nosotros-gobierno']);
    }
}
