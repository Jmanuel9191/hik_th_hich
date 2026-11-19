<?php

use yii\db\Migration;

/**
 * Páginas: "Inicio" sale del panel. Sus bloques (Cifras y Pie de página) pasan a Nosotros, con su historial.
 * Quiénes somos: + palabra de fondo (columna nueva), + texto arriba de la frase (firma) y + pie de la foto (firma_detalle).
 * Bloque nuevo "Destacados" (ícono + texto) con los dos de la plantilla.
 */
class m261118_170000_nosotros_inicio extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%pagina_seccion}}', 'palabra_fondo', $this->string(60)->null()->after('subtitulo'));

        $this->update('{{%pagina_seccion}}', ['pagina' => 'nosotros'], ['pagina' => 'inicio', 'clave' => ['cifras', 'pie']]);
        $this->update('{{%pagina_historial}}', ['pagina' => 'nosotros'], ['pagina' => 'inicio']);

        // Textos iniciales de Quiénes somos (solo si están vacíos)
        $this->update('{{%pagina_seccion}}', ['palabra_fondo' => 'Ecosistemas'], ['pagina' => 'nosotros', 'clave' => 'historia', 'palabra_fondo' => null]);
        $this->update('{{%pagina_seccion}}', ['firma' => 'La tesis estratégica es clara:'], ['pagina' => 'nosotros', 'clave' => 'historia', 'firma' => null]);

        $this->insert('{{%pagina_seccion}}', ['pagina' => 'nosotros', 'clave' => 'destacados', 'estado' => '1']);
        $id = $this->db->getLastInsertID();
        foreach ([['Territorios estratégicos', 'destacado-territorios.svg'], ['Capital y mercados globales', 'destacado-capital.svg']] as $i => [$titulo, $icono]) {
            $this->insert('{{%pagina_item}}', [
                'idseccion' => $id, 'titulo' => $titulo, 'icono' => $icono, 'orden' => $i + 1, 'estado' => '1',
                'fecha_registro' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function safeDown()
    {
        $id = (new \yii\db\Query())->select('idseccion')->from('{{%pagina_seccion}}')->where(['pagina' => 'nosotros', 'clave' => 'destacados'])->scalar();
        if ($id) {
            $this->delete('{{%pagina_item}}', ['idseccion' => $id]);
            $this->delete('{{%pagina_seccion}}', ['idseccion' => $id]);
        }
        $this->update('{{%pagina_seccion}}', ['pagina' => 'inicio'], ['pagina' => 'nosotros', 'clave' => ['cifras', 'pie']]);
        $this->dropColumn('{{%pagina_seccion}}', 'palabra_fondo');
    }
}
