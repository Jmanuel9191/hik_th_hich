<?php

use yii\db\Migration;

/**
 * Ecosistemas en el orden del brochure:
 * #2 vuelve a ser "Energía" (su texto ya era de energía) y #4 pasa a "Infraestructura y logística".
 * Solo cambia si los nombres siguen como estaban. Deja constancia en el historial.
 */
class m261119_120000_ecosistemas_brochure extends Migration
{
    const CAMBIOS = [
        // id => [nombre y slug actuales, nombre y slug nuevos]   (#2 primero: libera el slug que toma #4)
        2 => [['Infraestructura y logística', 'infraestructura-y-logistica'], ['Energía', 'energia']],
        4 => [['Infraestructura', 'infraestructura'], ['Infraestructura y logística', 'infraestructura-y-logistica']],
    ];

    public function safeUp()
    {
        foreach (self::CAMBIOS as $id => [$antes, $despues]) {
            $this->cambiar($id, $antes, $despues);
        }
    }

    public function safeDown()
    {
        foreach (array_reverse(self::CAMBIOS, true) as $id => [$antes, $despues]) {
            $this->cambiar($id, $despues, $antes);
        }
    }

    private function cambiar(int $id, array $de, array $a): void
    {
        $filas = $this->db->createCommand()->update('{{%ecosistema}}',
            ['nombre' => $a[0], 'slug' => $a[1], 'fecha_actualizacion' => new \yii\db\Expression('NOW()')],
            ['idecosistema' => $id, 'nombre' => $de[0], 'slug' => $de[1]]
        )->execute();
        if ($filas) {
            $this->insert('{{%ecosistema_historial}}', [
                'idecosistema' => $id,
                'idusuario_actor' => null,
                'accion' => 'Edición',
                'descripcion' => "Título: {$de[0]} → {$a[0]}\nOrden del brochure (cambio por migración)",
                'ip' => null,
                'fecha' => new \yii\db\Expression('NOW()'),
            ]);
        }
    }
}
