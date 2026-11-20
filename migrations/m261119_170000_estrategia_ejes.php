<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Nosotros → Ejes estratégicos: bloque "ejes" en Panel → Páginas → Estrategia (texto chico + título)
 * y el texto chico del Modelo de creación de valor.
 */
class m261119_170000_estrategia_ejes extends Migration
{
    public function safeUp()
    {
        $existe = (new Query())->from('{{%pagina_seccion}}')->where(['pagina' => 'estrategia', 'clave' => 'ejes'])->exists($this->db);
        if (!$existe) {
            $this->insert('{{%pagina_seccion}}', [
                'pagina' => 'estrategia',
                'clave' => 'ejes',
                'titulo' => '{n} ejes prioritarios de expansión e inversión 2026–2035',
                'subtitulo' => 'Ejes Estratégicos de Crecimiento',
                'estado' => '1',
            ]);
        }
        $this->update('{{%pagina_seccion}}', ['subtitulo' => 'Cómo creamos valor'], ['pagina' => 'estrategia', 'clave' => 'modelo', 'subtitulo' => null]);
    }

    public function safeDown()
    {
        $this->delete('{{%pagina_seccion}}', ['pagina' => 'estrategia', 'clave' => 'ejes']);
    }
}
