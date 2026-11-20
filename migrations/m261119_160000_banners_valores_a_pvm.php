<?php

use yii\db\Migration;

/**
 * Valores y Principios ya no es una subpágina: va dentro de Propósito, Visión y Misión.
 * Los banners de la pestaña "Valores" pasan al final de la pestaña "Propósito" (queda primero el que ya estaba).
 */
class m261119_160000_banners_valores_a_pvm extends Migration
{
    public function safeUp()
    {
        $this->update('{{%banners}}', ['ubicacion' => 'nosotros-pvm', 'orden' => new \yii\db\Expression('orden + 1000')], ['ubicacion' => 'nosotros-valores']);
    }

    public function safeDown()
    {
        $this->update('{{%banners}}', ['ubicacion' => 'nosotros-valores', 'orden' => new \yii\db\Expression('orden - 1000')], ['and', ['ubicacion' => 'nosotros-pvm'], ['>=', 'orden', 1000]]);
    }
}
