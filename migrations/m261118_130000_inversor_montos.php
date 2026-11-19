<?php

use yii\db\Migration;

/**
 * Paquete 22.1: el inversor indica su monto mínimo y máximo (USD) en lugar de un rango fijo.
 * Los datos que ya existan se pasan del rango elegido a mínimo/máximo.
 */
class m261118_130000_inversor_montos extends Migration
{
    const RANGOS = [
        'hasta_50k' => [1, 50000],
        '50k_250k' => [50000, 250000],
        '250k_1m' => [250000, 1000000],
        '1m_5m' => [1000000, 5000000],
        'mas_5m' => [5000000, 5000000],
    ];

    public function safeUp()
    {
        $this->addColumn('{{%inversor}}', 'monto_minimo', $this->decimal(15, 2)->null()->after('sitio_web'));
        $this->addColumn('{{%inversor}}', 'monto_maximo', $this->decimal(15, 2)->null()->after('monto_minimo'));
        foreach (self::RANGOS as $clave => [$min, $max]) {
            $this->update('{{%inversor}}', ['monto_minimo' => $min, 'monto_maximo' => $max], ['rango_inversion' => $clave]);
        }
        $this->update('{{%inversor}}', ['monto_minimo' => 1, 'monto_maximo' => 1], ['monto_minimo' => null]);
        $this->alterColumn('{{%inversor}}', 'monto_minimo', $this->decimal(15, 2)->notNull());
        $this->alterColumn('{{%inversor}}', 'monto_maximo', $this->decimal(15, 2)->notNull());
        $this->createIndex('idx_inversor_montos', '{{%inversor}}', ['monto_minimo', 'monto_maximo']);
        $this->dropColumn('{{%inversor}}', 'rango_inversion');
    }

    public function safeDown()
    {
        $this->addColumn('{{%inversor}}', 'rango_inversion', $this->string(20)->notNull()->defaultValue('hasta_50k')->after('sitio_web'));
        $this->dropIndex('idx_inversor_montos', '{{%inversor}}');
        $this->dropColumn('{{%inversor}}', 'monto_maximo');
        $this->dropColumn('{{%inversor}}', 'monto_minimo');
    }
}
