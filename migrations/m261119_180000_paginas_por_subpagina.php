<?php

use yii\db\Migration;

/**
 * Panel → Páginas ordenado como el submenú Nosotros de la web: una página del panel por subpágina.
 * Los bloques no cambian; solo pasan a la página que les corresponde (columna "pagina").
 */
class m261119_180000_paginas_por_subpagina extends Migration
{
    /** página nueva => [página anterior, bloques] */
    const MAPA = [
        'quienes' => ['nosotros', ['historia', 'destacados', 'bloques', 'ceo', 'cifras']],
        'pvm' => ['nosotros', ['proposito', 'vision', 'mision', 'valores', 'principios']],
        'faq' => ['nosotros', ['faq']],
        'general' => ['nosotros', ['pie']],
        'ejes' => ['estrategia', ['ejes', 'modelo', 'propuesta_valor', 'expansion', 'fases']],
    ];

    public function safeUp()
    {
        foreach (self::MAPA as $nueva => [$anterior, $claves]) {
            $this->update('{{%pagina_seccion}}', ['pagina' => $nueva], ['pagina' => $anterior, 'clave' => $claves]);
        }
        // El historial anterior queda en la primera página de cada grupo
        $this->update('{{%pagina_historial}}', ['pagina' => 'quienes'], ['pagina' => 'nosotros']);
        $this->update('{{%pagina_historial}}', ['pagina' => 'ejes'], ['pagina' => 'estrategia']);
    }

    public function safeDown()
    {
        foreach (self::MAPA as $nueva => [$anterior, $claves]) {
            $this->update('{{%pagina_seccion}}', ['pagina' => $anterior], ['pagina' => $nueva, 'clave' => $claves]);
        }
        $this->update('{{%pagina_historial}}', ['pagina' => 'nosotros'], ['pagina' => ['quienes', 'pvm', 'faq', 'general']]);
        $this->update('{{%pagina_historial}}', ['pagina' => 'estrategia'], ['pagina' => 'ejes']);
    }
}
