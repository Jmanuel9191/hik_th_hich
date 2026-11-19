<?php

use yii\db\Migration;

/**
 * Banco de proyectos (admin) + Monedas.
 *  - proyecto: ficha (tipo financiamiento / inversión), números, estados con revisión.
 *  - proyecto_imagen (galería), proyecto_video (archivo o enlace), proyecto_documento (privados),
 *    proyecto_descarga (quién bajó cada documento), proyecto_historial.
 *  - moneda: USD y BOB de entrada.
 * Archivos en privado/proyectos/<id>/ (nunca con URL pública).
 * La tabla vieja "proyectos" (sistema anterior) no se toca.
 */
class m261118_090000_banco_proyectos extends Migration
{
    /** alias => [descripción, roles] (1 Superadmin, 2 Distribuidor, 3 Gerente de área) */
    const PERMISOS = [
        'proyectos_listar' => ['Permite ver el listado de proyectos (el gerente solo los de sus áreas)', [1, 2, 3]],
        'proyectos_ver' => ['Permite ver la ficha, fotos, videos y documentos de un proyecto', [1, 2, 3]],
        'proyectos_crear' => ['Permite crear proyectos (el gerente solo en sus áreas)', [1, 3]],
        'proyectos_editar' => ['Permite editar proyectos y subir fotos, videos y documentos', [1, 3]],
        'proyectos_publicar' => ['Permite publicar, cerrar, archivar o devolver a borrador un proyecto', [1, 3]],
        'proyectos_eliminar' => ['Permite eliminar proyectos (el gerente solo los que están en borrador)', [1, 3]],
        'proyectos_historial' => ['Permite ver el historial de un proyecto', [1, 2, 3]],
        'monedas_editar' => ['Permite crear y editar las monedas de los proyectos', [1]],
    ];

    public function safeUp()
    {
        $op = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%moneda}}', [
            'idmoneda' => $this->primaryKey(),
            'codigo' => $this->string(3)->notNull(),
            'simbolo' => $this->string(6)->notNull(),
            'nombre' => $this->string(60)->notNull(),
            'estado' => $this->char(1)->notNull()->defaultValue('1'),
            'orden' => $this->integer()->notNull()->defaultValue(0),
        ], $op);
        $this->createIndex('uq_moneda_codigo', '{{%moneda}}', 'codigo', true);
        $this->insert('{{%moneda}}', ['codigo' => 'USD', 'simbolo' => 'US$', 'nombre' => 'Dólar estadounidense', 'orden' => 1]);
        $this->insert('{{%moneda}}', ['codigo' => 'BOB', 'simbolo' => 'Bs', 'nombre' => 'Boliviano', 'orden' => 2]);

        $this->createTable('{{%proyecto}}', [
            'idproyecto' => $this->primaryKey(),
            'codigo' => $this->string(30)->notNull(),
            'slug' => $this->string(180)->notNull(),
            'titulo' => $this->string(160)->notNull(),
            'tipo' => $this->string(20)->notNull()->defaultValue('financiamiento'),
            'idecosistema' => $this->integer()->notNull(),
            'idgerente' => $this->integer()->null(),
            'pais' => $this->string(60)->null(),
            'ciudad' => $this->string(80)->null(),
            'ubicacion' => $this->string(200)->null(),
            'etapa' => $this->string(30)->null(),
            'etapas_interes' => $this->string(255)->null(),
            'estado' => $this->string(20)->notNull()->defaultValue('borrador'),
            'resumen' => $this->string(500)->notNull()->defaultValue(''),
            'descripcion' => 'MEDIUMTEXT NULL',
            'riesgos' => $this->text()->null(),
            'garantias' => $this->text()->null(),
            'impacto' => $this->text()->null(),
            'idmoneda' => $this->integer()->null(),
            'monto_total' => $this->decimal(16, 2)->null(),
            'monto_objetivo' => $this->decimal(16, 2)->null(),
            'inversion_minima' => $this->decimal(16, 2)->null(),
            'inversion_maxima' => $this->decimal(16, 2)->null(),
            'roi' => $this->decimal(6, 2)->null(),
            'plazo_meses' => $this->smallInteger()->null(),
            'frecuencia_retorno' => $this->string(20)->null(),
            'tipo_participacion' => $this->string(20)->null(),
            'fecha_apertura' => $this->date()->null(),
            'fecha_cierre' => $this->date()->null(),
            'imagen_banner' => $this->string(255)->null(),
            'foto_principal' => $this->string(255)->null(),
            'idusuario_registro' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
            'fecha_actualizacion' => $this->dateTime()->null(),
            'fecha_publicacion' => $this->dateTime()->null(),
        ], $op);
        $this->createIndex('uq_proyecto_codigo', '{{%proyecto}}', 'codigo', true);
        $this->createIndex('uq_proyecto_slug', '{{%proyecto}}', 'slug', true);
        $this->createIndex('idx_proyecto_eco_estado', '{{%proyecto}}', ['idecosistema', 'estado']);
        $this->addForeignKey('fk_proyecto_ecosistema', '{{%proyecto}}', 'idecosistema', '{{%ecosistema}}', 'idecosistema', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_proyecto_moneda', '{{%proyecto}}', 'idmoneda', '{{%moneda}}', 'idmoneda', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%proyecto_imagen}}', [
            'idimagen' => $this->primaryKey(),
            'idproyecto' => $this->integer()->notNull(),
            'archivo' => $this->string(255)->notNull(),
            'nombre_original' => $this->string(255)->notNull()->defaultValue(''),
            'orden' => $this->integer()->notNull()->defaultValue(0),
            'idusuario' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
        ], $op);
        $this->addForeignKey('fk_pimg_proyecto', '{{%proyecto_imagen}}', 'idproyecto', '{{%proyecto}}', 'idproyecto', 'CASCADE', 'CASCADE');

        $this->createTable('{{%proyecto_video}}', [
            'idvideo' => $this->primaryKey(),
            'idproyecto' => $this->integer()->notNull(),
            'titulo' => $this->string(150)->notNull(),
            'tipo' => $this->string(10)->notNull(),
            'archivo' => $this->string(255)->null(),
            'url' => $this->string(500)->null(),
            'nombre_original' => $this->string(255)->null(),
            'mime' => $this->string(60)->null(),
            'tamano' => $this->bigInteger()->null(),
            'orden' => $this->integer()->notNull()->defaultValue(0),
            'idusuario' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
        ], $op);
        $this->addForeignKey('fk_pvid_proyecto', '{{%proyecto_video}}', 'idproyecto', '{{%proyecto}}', 'idproyecto', 'CASCADE', 'CASCADE');

        $this->createTable('{{%proyecto_documento}}', [
            'iddocumento' => $this->primaryKey(),
            'idproyecto' => $this->integer()->notNull(),
            'nombre' => $this->string(150)->notNull(),
            'tipo' => $this->string(30)->notNull(),
            'archivo' => $this->string(255)->notNull(),
            'nombre_original' => $this->string(255)->notNull(),
            'mime' => $this->string(100)->null(),
            'tamano' => $this->bigInteger()->null(),
            'visible_inversor' => $this->char(1)->notNull()->defaultValue('1'),
            'idusuario' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
        ], $op);
        $this->addForeignKey('fk_pdoc_proyecto', '{{%proyecto_documento}}', 'idproyecto', '{{%proyecto}}', 'idproyecto', 'CASCADE', 'CASCADE');

        // Sin FK: el registro de descargas queda aunque se borre el documento.
        $this->createTable('{{%proyecto_descarga}}', [
            'iddescarga' => $this->primaryKey(),
            'iddocumento' => $this->integer()->notNull(),
            'idproyecto' => $this->integer()->notNull(),
            'idusuario' => $this->integer()->null(),
            'ip' => $this->string(45)->null(),
            'fecha' => $this->dateTime()->notNull(),
        ], $op);
        $this->createIndex('idx_pdes_doc', '{{%proyecto_descarga}}', ['iddocumento', 'fecha']);

        $this->createTable('{{%proyecto_historial}}', [
            'idhistorial' => $this->primaryKey(),
            'idproyecto' => $this->integer()->notNull(),
            'idusuario_actor' => $this->integer()->null(),
            'accion' => $this->string(60)->notNull(),
            'descripcion' => $this->text()->null(),
            'ip' => $this->string(45)->null(),
            'fecha' => $this->dateTime()->notNull(),
        ], $op);
        $this->createIndex('idx_phis_proyecto', '{{%proyecto_historial}}', ['idproyecto', 'fecha']);

        foreach (self::PERMISOS as $alias => [$descripcion, $roles]) {
            $this->insert('{{%permisos}}', ['alias' => $alias, 'descripcion' => $descripcion, 'estado' => '1', 'fecha_registro' => date('Y-m-d H:i:s')]);
            $id = $this->db->getLastInsertID();
            foreach ($roles as $idrol) {
                $this->insert('{{%rol_permiso}}', ['idrol' => $idrol, 'idpermiso' => $id]);
            }
        }
    }

    public function safeDown()
    {
        $ids = (new \yii\db\Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => array_keys(self::PERMISOS)])->column();
        $this->delete('{{%rol_permiso}}', ['idpermiso' => $ids]);
        $this->delete('{{%permisos}}', ['idpermiso' => $ids]);
        foreach (['proyecto_historial', 'proyecto_descarga', 'proyecto_documento', 'proyecto_video', 'proyecto_imagen', 'proyecto', 'moneda'] as $t) {
            $this->dropTable('{{%' . $t . '}}');
        }
    }
}
