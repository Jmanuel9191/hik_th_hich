<?php

use yii\db\Migration;

/**
 * Datos generales:
 * - Título de la página (pestaña, Google, header, correos): nombre corto.
 * - Nombre de la empresa (pie de la web, al lado del ©): nombre con el lema.
 */
class m261119_121000_titulo_nombre_empresa extends Migration
{
    const TITULO = 'HUK THE ICHI – GLOBAL GROUP';
    const NOMBRE = 'HUK THE ICHI – GLOBAL GROUP - ONE VISION • INFINITE OPPORTUNITIES';

    const TITULO_ANTES = 'HUK THE ICHI – GLOBLAL GROUP - ONE VISION • INFINITE OPPORTUNITIES';
    const NOMBRE_ANTES = 'HUK THE ICHI – GLOBLAL GROUP';

    public function safeUp()
    {
        $this->cambiar(self::TITULO_ANTES, self::NOMBRE_ANTES, self::TITULO, self::NOMBRE);
    }

    public function safeDown()
    {
        $this->cambiar(self::TITULO, self::NOMBRE, self::TITULO_ANTES, self::NOMBRE_ANTES);
    }

    private function cambiar(string $tituloDe, string $nombreDe, string $titulo, string $nombre): void
    {
        $filas = $this->db->createCommand()->update('{{%configuracion}}',
            ['titulo_pagina' => $titulo, 'nombre_empresa' => $nombre],
            ['titulo_pagina' => $tituloDe, 'nombre_empresa' => $nombreDe]
        )->execute();
        if ($filas) {
            $this->insert('{{%configuracion_historial}}', [
                'idusuario_actor' => null,
                'ip' => null,
                'fecha' => new \yii\db\Expression('NOW()'),
                'detalle' => "Título de la página: $tituloDe → $titulo\nNombre de la empresa: $nombreDe → $nombre",
            ]);
        }
    }
}
