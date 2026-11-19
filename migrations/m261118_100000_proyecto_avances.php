<?php

use yii\db\Migration;

/**
 * Avances de proyecto (bitácora): fecha, título, texto, etapa y % de avance opcionales,
 * con fotos, videos, documentos o enlaces (privado/proyectos/<id>/avances/<idavance>/).
 * proyecto: + porcentaje_avance (el último informado, o el de la etapa).
 * proyecto_descarga: también registra descargas de archivos de avances.
 * Usa los permisos proyectos_* que ya existen.
 */
class m261118_100000_proyecto_avances extends Migration
{
    public function safeUp()
    {
        $op = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%proyecto_avance}}', [
            'idavance' => $this->primaryKey(),
            'idproyecto' => $this->integer()->notNull(),
            'fecha' => $this->date()->notNull(),
            'titulo' => $this->string(160)->notNull(),
            'texto' => 'MEDIUMTEXT NULL',
            'etapa' => $this->string(30)->null(),
            'porcentaje' => $this->tinyInteger()->unsigned()->null(),
            'estado' => $this->char(1)->notNull()->defaultValue('1'),
            'idusuario_registro' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
            'fecha_actualizacion' => $this->dateTime()->null(),
        ], $op);
        $this->createIndex('idx_pav_proyecto', '{{%proyecto_avance}}', ['idproyecto', 'estado', 'fecha']);
        $this->addForeignKey('fk_pav_proyecto', '{{%proyecto_avance}}', 'idproyecto', '{{%proyecto}}', 'idproyecto', 'CASCADE', 'CASCADE');

        $this->createTable('{{%proyecto_avance_archivo}}', [
            'idarchivo' => $this->primaryKey(),
            'idavance' => $this->integer()->notNull(),
            'tipo' => $this->string(10)->notNull(),
            'archivo' => $this->string(255)->null(),
            'url' => $this->string(500)->null(),
            'nombre_original' => $this->string(255)->null(),
            'mime' => $this->string(100)->null(),
            'tamano' => $this->bigInteger()->null(),
            'orden' => $this->integer()->notNull()->defaultValue(0),
            'idusuario' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
        ], $op);
        $this->addForeignKey('fk_paa_avance', '{{%proyecto_avance_archivo}}', 'idavance', '{{%proyecto_avance}}', 'idavance', 'CASCADE', 'CASCADE');

        $this->addColumn('{{%proyecto}}', 'porcentaje_avance', $this->tinyInteger()->unsigned()->null()->after('etapas_interes'));
        // Proyectos que ya existían: % según su etapa
        foreach (\app\models\Proyecto::PORCENTAJE_ETAPA as $etapa => $pct) {
            $this->update('{{%proyecto}}', ['porcentaje_avance' => $pct], ['etapa' => $etapa, 'tipo' => 'financiamiento']);
        }

        $this->alterColumn('{{%proyecto_descarga}}', 'iddocumento', $this->integer()->null());
        $this->addColumn('{{%proyecto_descarga}}', 'idarchivo_avance', $this->integer()->null()->after('iddocumento'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%proyecto_descarga}}', 'idarchivo_avance');
        $this->delete('{{%proyecto_descarga}}', ['iddocumento' => null]);
        $this->alterColumn('{{%proyecto_descarga}}', 'iddocumento', $this->integer()->notNull());
        $this->dropColumn('{{%proyecto}}', 'porcentaje_avance');
        $this->dropTable('{{%proyecto_avance_archivo}}');
        $this->dropTable('{{%proyecto_avance}}');
    }
}
