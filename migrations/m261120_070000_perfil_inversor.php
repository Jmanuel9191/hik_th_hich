<?php

use yii\db\Migration;

/**
 * Perfil del inversor (Mi cuenta):
 *  - usuarios.foto_perfil: foto de perfil.
 *  - inversor_interes.pedido_fecha: cuándo pidió esa área desde Mi cuenta (null = la marcó al registrarse).
 *    Mientras no se le asigne ni se le niegue, es un pedido pendiente.
 * La foto es privada: se guarda en privado/inversores/{id}/ y se sirve solo por controlador
 * (al propio inversor y al equipo con permiso para ver inversores).
 * Se puede correr sobre una base que ya tenga la columna.
 */
class m261120_070000_perfil_inversor extends Migration
{
    public function safeUp()
    {
        if ($this->db->getTableSchema('{{%usuarios}}', true)->getColumn('foto_perfil') === null) {
            $this->addColumn('{{%usuarios}}', 'foto_perfil', $this->string(255)->null()->comment('foto de perfil (privada, archivo en privado/inversores/{id})'));
        }
        if ($this->db->getTableSchema('{{%inversor_interes}}', true)->getColumn('pedido_fecha') === null) {
            $this->addColumn('{{%inversor_interes}}', 'pedido_fecha', $this->dateTime()->null()->comment('pedido desde Mi cuenta, pendiente de decisión'));
        }
    }

    public function safeDown()
    {
        if ($this->db->getTableSchema('{{%inversor_interes}}', true)->getColumn('pedido_fecha') !== null) {
            $this->dropColumn('{{%inversor_interes}}', 'pedido_fecha');
        }
        if ($this->db->getTableSchema('{{%usuarios}}', true)->getColumn('foto_perfil') !== null) {
            $this->dropColumn('{{%usuarios}}', 'foto_perfil');
        }
    }
}
