<?php

use yii\db\Migration;

/**
 * Blog (menú "Blog" de la web → /noticias): noticias con portada, imagen de contenido y galería; categorías;
 * historial de ambos. Permisos noticias_* para Superadmin y Editor de contenido.
 * Banners: la pestaña "Expansión" (sin página) pasa a ser "Blog".
 */
class m261119_200000_blog extends Migration
{
    const PERMISOS = [
        'noticias_listar' => 'Permite ver el listado de noticias del blog',
        'noticias_ver' => 'Permite ver el detalle de una noticia',
        'noticias_crear' => 'Permite crear noticias',
        'noticias_editar' => 'Permite editar noticias y marcarlas como destacadas',
        'noticias_estado' => 'Permite publicar u ocultar noticias',
        'noticias_galeria' => 'Permite subir y borrar fotos de la galería de una noticia',
        'noticias_eliminar' => 'Permite eliminar noticias',
        'noticias_historial' => 'Permite ver el historial del blog (noticias y categorías)',
        'noticias_categorias' => 'Permite crear, editar, ordenar y eliminar categorías del blog',
    ];

    public function safeUp()
    {
        $opciones = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';

        $this->createTable('{{%noticia_categoria}}', [
            'idcategoria' => $this->primaryKey(),
            'nombre' => $this->string(100)->notNull(),
            'slug' => $this->string(120)->notNull()->unique(),
            'orden' => $this->integer()->notNull()->defaultValue(0),
            'estado' => $this->char(1)->notNull()->defaultValue('1'),
            'idusuario_registro' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
            'fecha_actualizacion' => $this->dateTime()->null(),
        ], $opciones);

        $this->createTable('{{%noticia}}', [
            'idnoticia' => $this->primaryKey(),
            'titulo' => $this->string(200)->notNull(),
            'slug' => $this->string(220)->notNull()->unique(),
            'resumen' => $this->string(400)->notNull(),
            'descripcion' => $this->getDb()->getSchema()->createColumnSchemaBuilder('mediumtext')->notNull(),
            'fecha' => $this->date()->notNull(),
            'idcategoria' => $this->integer()->notNull(),
            'imagen_portada' => $this->string(255)->notNull(),
            'imagen_contenido' => $this->string(255)->null(),
            'estado' => $this->char(1)->notNull()->defaultValue('1'),
            'destacado' => $this->char(1)->notNull()->defaultValue('0'),
            'idusuario_registro' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
            'fecha_actualizacion' => $this->dateTime()->null(),
        ], $opciones);
        $this->createIndex('idx_noticia_web', '{{%noticia}}', ['estado', 'fecha']);
        $this->createIndex('idx_noticia_categoria', '{{%noticia}}', ['idcategoria']);
        $this->addForeignKey('fk_noticia_categoria', '{{%noticia}}', 'idcategoria', '{{%noticia_categoria}}', 'idcategoria', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%noticia_imagen}}', [
            'idimagen' => $this->primaryKey(),
            'idnoticia' => $this->integer()->notNull(),
            'archivo' => $this->string(255)->notNull(),
            'nombre_original' => $this->string(255)->null(),
            'orden' => $this->integer()->notNull()->defaultValue(0),
            'idusuario' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
        ], $opciones);
        $this->addForeignKey('fk_noticia_imagen', '{{%noticia_imagen}}', 'idnoticia', '{{%noticia}}', 'idnoticia', 'CASCADE', 'CASCADE');

        // Historial del blog: tipo "noticia" o "categoria" + id del registro (queda aunque se elimine)
        $this->createTable('{{%blog_historial}}', [
            'idhistorial' => $this->primaryKey(),
            'tipo' => $this->string(20)->notNull(),
            'idregistro' => $this->integer()->notNull(),
            'idusuario_actor' => $this->integer()->null(),
            'accion' => $this->string(50)->notNull(),
            'descripcion' => $this->text()->null(),
            'ip' => $this->string(45)->null(),
            'fecha' => $this->dateTime()->notNull(),
        ], $opciones);
        $this->createIndex('idx_bh_registro', '{{%blog_historial}}', ['tipo', 'idregistro', 'fecha']);

        foreach (self::PERMISOS as $alias => $descripcion) {
            $this->insert('{{%permisos}}', ['alias' => $alias, 'descripcion' => $descripcion, 'estado' => '1', 'fecha_registro' => date('Y-m-d H:i:s')]);
            $id = $this->db->getLastInsertID();
            $this->insert('{{%rol_permiso}}', ['idrol' => 1, 'idpermiso' => $id]); // Superadmin
            $this->insert('{{%rol_permiso}}', ['idrol' => 4, 'idpermiso' => $id]); // Editor de contenido
        }

        $this->update('{{%banners}}', ['ubicacion' => 'noticias'], ['ubicacion' => 'expansion']);
    }

    public function safeDown()
    {
        $this->update('{{%banners}}', ['ubicacion' => 'expansion'], ['ubicacion' => 'noticias']);
        $ids = (new \yii\db\Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => array_keys(self::PERMISOS)])->column();
        $this->delete('{{%rol_permiso}}', ['idpermiso' => $ids]);
        $this->delete('{{%permisos}}', ['idpermiso' => $ids]);
        $this->dropTable('{{%blog_historial}}');
        $this->dropTable('{{%noticia_imagen}}');
        $this->dropTable('{{%noticia}}');
        $this->dropTable('{{%noticia_categoria}}');
    }
}
