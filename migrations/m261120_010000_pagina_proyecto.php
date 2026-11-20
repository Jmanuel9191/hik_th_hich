<?php

use yii\db\Migration;

/**
 * Página web de cada proyecto (/proyectos/<slug>) y plantillas de página.
 *  - proyecto: datos del banner (rubro, bajada, sello, elementos, cifras) y la casilla "destacado".
 *  - proyecto_plantilla: plantillas de página (una por ecosistema + "En blanco"), editables desde el panel.
 *  - proyecto_seccion: secciones de un proyecto O de una plantilla (una de las dos columnas va llena).
 *  - proyecto_bloque: bloques de cada sección; "datos" es JSON (lo valida la clase del bloque, app\models\bloques\*).
 *  - Permisos: proyectos_pagina (Superadmin, Gerente, Editor), plantillas_editar (Superadmin, Editor).
 *    El Editor además puede ver el listado de proyectos (proyectos_listar) para llegar a la página web de cada uno.
 * Las plantillas iniciales están en migrations/datos/plantillas_proyecto.php.
 */
class m261120_010000_pagina_proyecto extends Migration
{
    const PERMISOS = [
        'proyectos_pagina' => ['Permite editar la página web de los proyectos (banner, secciones, bloques y fotos)', [1, 3, 4]],
        'plantillas_editar' => ['Permite crear y editar las plantillas de página de los proyectos', [1, 4]],
    ];

    public function safeUp()
    {
        $opciones = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        $texto = fn() => $this->getDb()->getSchema()->createColumnSchemaBuilder('mediumtext');

        // --- Proyecto: banner de la página y destacado ---
        $this->addColumn('{{%proyecto}}', 'rubro', $this->string(80)->null()->comment('Antetítulo de la tarjeta: "Proyecto minero"')->after('resumen'));
        $this->addColumn('{{%proyecto}}', 'bajada', $this->string(400)->null()->comment('Texto del banner (si está vacío, el resumen)')->after('rubro'));
        $this->addColumn('{{%proyecto}}', 'sello', $this->string(80)->null()->comment('Etiqueta del banner: CONFIDENCIAL…')->after('bajada'));
        $this->addColumn('{{%proyecto}}', 'elementos', $this->text()->null()->comment('JSON: [{numero, simbolo, nombre}]')->after('sello'));
        $this->addColumn('{{%proyecto}}', 'cifras', $this->text()->null()->comment('JSON: [{valor, texto, contar}]')->after('elementos'));
        $this->addColumn('{{%proyecto}}', 'destacado', $this->char(1)->notNull()->defaultValue('0')->after('cifras'));
        $this->createIndex('idx_proyecto_destacado', '{{%proyecto}}', ['idecosistema', 'destacado']);

        // --- Plantillas ---
        $this->createTable('{{%proyecto_plantilla}}', [
            'idplantilla' => $this->primaryKey(),
            'nombre' => $this->string(100)->notNull(),
            'descripcion' => $this->string(300)->null(),
            'idecosistema' => $this->integer()->null()->comment('Ecosistema al que se sugiere (null = cualquiera)'),
            'orden' => $this->integer()->notNull()->defaultValue(0),
            'estado' => $this->char(1)->notNull()->defaultValue('1'),
            'idusuario_registro' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
            'fecha_actualizacion' => $this->dateTime()->null(),
        ], $opciones);
        $this->createIndex('idx_plantilla_eco', '{{%proyecto_plantilla}}', ['idecosistema', 'orden']);
        $this->addForeignKey('fk_plantilla_ecosistema', '{{%proyecto_plantilla}}', 'idecosistema', '{{%ecosistema}}', 'idecosistema', 'SET NULL', 'CASCADE');

        // --- Secciones (de un proyecto o de una plantilla) ---
        $this->createTable('{{%proyecto_seccion}}', [
            'idseccion' => $this->primaryKey(),
            'idproyecto' => $this->integer()->null(),
            'idplantilla' => $this->integer()->null(),
            'orden' => $this->integer()->notNull()->defaultValue(0),
            'antetitulo' => $this->string(120)->null(),
            'titulo' => $this->string(200)->null(),
            'menu' => $this->string(40)->null()->comment('Nombre corto en el menú de anclas'),
            'ancla' => $this->string(60)->notNull(),
            'fondo' => $this->string(10)->notNull()->defaultValue('blanco'),
            'diseno' => $this->string(10)->notNull()->defaultValue('arriba'),
            'en_menu' => $this->char(1)->notNull()->defaultValue('1'),
            'numerada' => $this->char(1)->notNull()->defaultValue('1'),
            'visible' => $this->char(1)->notNull()->defaultValue('1'),
            'fecha_registro' => $this->dateTime()->notNull(),
            'fecha_actualizacion' => $this->dateTime()->null(),
        ], $opciones);
        $this->createIndex('idx_seccion_proyecto', '{{%proyecto_seccion}}', ['idproyecto', 'orden']);
        $this->createIndex('idx_seccion_plantilla', '{{%proyecto_seccion}}', ['idplantilla', 'orden']);
        $this->addForeignKey('fk_seccion_proyecto', '{{%proyecto_seccion}}', 'idproyecto', '{{%proyecto}}', 'idproyecto', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_seccion_plantilla', '{{%proyecto_seccion}}', 'idplantilla', '{{%proyecto_plantilla}}', 'idplantilla', 'CASCADE', 'CASCADE');

        // --- Bloques ---
        $this->createTable('{{%proyecto_bloque}}', [
            'idbloque' => $this->primaryKey(),
            'idseccion' => $this->integer()->notNull(),
            'orden' => $this->integer()->notNull()->defaultValue(0),
            'tipo' => $this->string(30)->notNull(),
            'ancho' => $this->string(10)->notNull()->defaultValue('completo')->comment('completo | izquierda | derecha'),
            'datos' => $texto()->null()->comment('JSON validado por la clase del bloque'),
            'visible' => $this->char(1)->notNull()->defaultValue('1'),
            'fecha_registro' => $this->dateTime()->notNull(),
            'fecha_actualizacion' => $this->dateTime()->null(),
        ], $opciones);
        $this->createIndex('idx_bloque_seccion', '{{%proyecto_bloque}}', ['idseccion', 'orden']);
        $this->addForeignKey('fk_bloque_seccion', '{{%proyecto_bloque}}', 'idseccion', '{{%proyecto_seccion}}', 'idseccion', 'CASCADE', 'CASCADE');

        // --- Permisos ---
        $ahora = date('Y-m-d H:i:s');
        foreach (self::PERMISOS as $alias => [$descripcion, $roles]) {
            $this->insert('{{%permisos}}', ['alias' => $alias, 'descripcion' => $descripcion, 'estado' => '1', 'fecha_registro' => $ahora]);
            $id = (int) $this->getDb()->getLastInsertID();
            foreach ($roles as $idrol) {
                $this->insert('{{%rol_permiso}}', ['idrol' => $idrol, 'idpermiso' => $id]);
            }
        }
        $listar = (new \yii\db\Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => 'proyectos_listar'])->scalar();
        $tiene = (new \yii\db\Query())->from('{{%rol_permiso}}')->where(['idrol' => 4, 'idpermiso' => $listar])->exists();
        if ($listar && !$tiene) {
            $this->insert('{{%rol_permiso}}', ['idrol' => 4, 'idpermiso' => $listar]);
        }

        // --- Plantillas iniciales ---
        $this->cargarPlantillas($ahora);
    }

    /** Crea las plantillas de migrations/datos/plantillas_proyecto.php (ecosistema por slug). */
    private function cargarPlantillas($ahora)
    {
        $plantillas = require __DIR__ . '/datos/plantillas_proyecto.php';
        foreach ($plantillas as $orden => $p) {
            $ideco = $p['ecosistema'] === null ? null
                : ((new \yii\db\Query())->select('idecosistema')->from('{{%ecosistema}}')->where(['slug' => $p['ecosistema']])->scalar() ?: null);
            $this->insert('{{%proyecto_plantilla}}', [
                'nombre' => $p['nombre'], 'descripcion' => $p['descripcion'], 'idecosistema' => $ideco,
                'orden' => $orden + 1, 'estado' => '1', 'fecha_registro' => $ahora,
            ]);
            $idplantilla = (int) $this->getDb()->getLastInsertID();
            foreach ($p['secciones'] as $i => $s) {
                $this->insert('{{%proyecto_seccion}}', [
                    'idplantilla' => $idplantilla, 'orden' => $i + 1,
                    'antetitulo' => $s['antetitulo'] ?? null, 'titulo' => $s['titulo'] ?? null, 'menu' => $s['menu'] ?? null,
                    'ancla' => $s['ancla'], 'fondo' => $s['fondo'] ?? 'blanco', 'diseno' => $s['diseno'] ?? 'arriba',
                    'en_menu' => ($s['en_menu'] ?? true) ? '1' : '0', 'numerada' => ($s['numerada'] ?? true) ? '1' : '0',
                    'visible' => '1', 'fecha_registro' => $ahora,
                ]);
                $idseccion = (int) $this->getDb()->getLastInsertID();
                foreach ($s['bloques'] ?? [] as $j => [$tipo, $ancho, $datos]) {
                    $this->insert('{{%proyecto_bloque}}', [
                        'idseccion' => $idseccion, 'orden' => $j + 1, 'tipo' => $tipo, 'ancho' => $ancho,
                        'datos' => json_encode($datos, JSON_UNESCAPED_UNICODE), 'visible' => '1', 'fecha_registro' => $ahora,
                    ]);
                }
            }
        }
    }

    public function safeDown()
    {
        $ids = (new \yii\db\Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => array_keys(self::PERMISOS)])->column();
        $this->delete('{{%rol_permiso}}', ['idpermiso' => $ids]);
        $this->delete('{{%permisos}}', ['idpermiso' => $ids]);
        $this->dropTable('{{%proyecto_bloque}}');
        $this->dropTable('{{%proyecto_seccion}}');
        $this->dropTable('{{%proyecto_plantilla}}');
        $this->dropIndex('idx_proyecto_destacado', '{{%proyecto}}');
        foreach (['destacado', 'cifras', 'elementos', 'sello', 'bajada', 'rubro'] as $columna) {
            $this->dropColumn('{{%proyecto}}', $columna);
        }
        // proyectos_listar del Editor queda (se puede quitar en Roles y permisos).
    }
}
