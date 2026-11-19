<?php

use yii\db\Migration;

/**
 * Páginas institucionales (primera: Nosotros).
 *  - pagina_seccion: bloques fijos de cada página (la web espera cada "clave"). Solo se editan.
 *  - pagina_item: listas dentro de una sección (valores, principios, metas, bloques libres…).
 *  - pagina_historial: quién cambió qué.
 * Carga los textos del Corporate Brochure 2026. Permisos paginas_* para Superadmin y Editor de contenido.
 */
class m261118_070000_paginas_nosotros extends Migration
{
    const PERMISOS = [
        'paginas_ver' => 'Permite ver el contenido de las páginas institucionales (Nosotros)',
        'paginas_editar' => 'Permite editar textos, imágenes y listas de las páginas institucionales',
        'paginas_historial' => 'Permite ver el historial de cambios de las páginas institucionales',
    ];

    public function safeUp()
    {
        $opciones = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        $this->createTable('{{%pagina_seccion}}', [
            'idseccion' => $this->primaryKey(),
            'pagina' => $this->string(40)->notNull(),
            'clave' => $this->string(40)->notNull(),
            'titulo' => $this->string(200)->null(),
            'subtitulo' => $this->string(300)->null(),
            'frase' => $this->string(300)->null(),
            'texto' => 'MEDIUMTEXT NULL',
            'imagen' => $this->string(255)->null(),
            'firma' => $this->string(150)->null(),
            'firma_detalle' => $this->string(150)->null(),
            'estado' => $this->char(1)->notNull()->defaultValue('1'),
            'idusuario_actualizacion' => $this->integer()->null(),
            'fecha_actualizacion' => $this->dateTime()->null(),
        ], $opciones);
        $this->createIndex('uq_pagina_seccion', '{{%pagina_seccion}}', ['pagina', 'clave'], true);

        $this->createTable('{{%pagina_item}}', [
            'iditem' => $this->primaryKey(),
            'idseccion' => $this->integer()->notNull(),
            'titulo' => $this->string(150)->notNull(),
            'texto' => $this->text()->null(),
            'icono' => $this->string(255)->null(),
            'imagen' => $this->string(255)->null(),
            'orden' => $this->integer()->notNull()->defaultValue(0),
            'estado' => $this->char(1)->notNull()->defaultValue('1'),
            'idusuario_registro' => $this->integer()->null(),
            'fecha_registro' => $this->dateTime()->notNull(),
            'fecha_actualizacion' => $this->dateTime()->null(),
        ], $opciones);
        $this->createIndex('idx_pagina_item_orden', '{{%pagina_item}}', ['idseccion', 'estado', 'orden']);
        $this->addForeignKey('fk_pagina_item_seccion', '{{%pagina_item}}', 'idseccion', '{{%pagina_seccion}}', 'idseccion', 'CASCADE', 'CASCADE');

        $this->createTable('{{%pagina_historial}}', [
            'idhistorial' => $this->primaryKey(),
            'pagina' => $this->string(40)->notNull(),
            'idusuario_actor' => $this->integer()->null(),
            'accion' => $this->string(60)->notNull(),
            'descripcion' => $this->text()->null(),
            'ip' => $this->string(45)->null(),
            'fecha' => $this->dateTime()->notNull(),
        ], $opciones);
        $this->createIndex('idx_ph_pagina', '{{%pagina_historial}}', ['pagina', 'fecha']);

        $this->cargarNosotros();

        foreach (self::PERMISOS as $alias => $descripcion) {
            $this->insert('{{%permisos}}', ['alias' => $alias, 'descripcion' => $descripcion, 'estado' => '1', 'fecha_registro' => date('Y-m-d H:i:s')]);
            $id = $this->db->getLastInsertID();
            $this->insert('{{%rol_permiso}}', ['idrol' => 1, 'idpermiso' => $id]); // Superadmin
            $this->insert('{{%rol_permiso}}', ['idrol' => 4, 'idpermiso' => $id]); // Editor de contenido
        }
    }

    /** Textos del Corporate Brochure 2026 (páginas 01 a 04). */
    private function cargarNosotros()
    {
        $secciones = [
            'historia' => [
                'titulo' => 'Quiénes Somos',
                'subtitulo' => 'De conectar negocios a desarrollar oportunidades',
                'frase' => 'Conectamos territorios estratégicos con capital, tecnología y mercados globales.',
                'texto' => '<p>HUK THE ICHI nació como una empresa orientada a facilitar conexiones comerciales y productivas entre actores económicos a nivel nacional e internacional. Operando en nombre propio, por cuenta de terceros o en asociación, construyó una plataforma multisectorial que integra minería, energía, agroindustria, infraestructura, salud, finanzas y comercio global.</p>'
                    . '<p>Hoy, esa base se transforma en <strong>HUK-THE-ICHI GLOBAL GROUP</strong>: una plataforma corporativa multinacional capaz de originar, estructurar, financiar, ejecutar y operar proyectos estratégicos de escala.</p>'
                    . '<p>La tesis estratégica es clara:</p>',
                'items' => [
                    ['Conectar', 'Personas, empresas, territorios, proyectos, tecnología y capital.'],
                    ['Transformar', 'Recursos y oportunidades en producción, infraestructura y negocios.'],
                    ['Multiplicar', 'El valor económico y social generado por cada proyecto.'],
                ],
            ],
            'bloques' => ['titulo' => null, 'items' => []],
            'proposito' => [
                'titulo' => 'Propósito',
                'texto' => 'Creamos soluciones empresariales que impulsen el desarrollo económico, mejoren la calidad de vida y fortalezcan la competitividad regional conformando ecosistemas integrales.',
            ],
            'vision' => [
                'titulo' => 'Visión 2045',
                'texto' => 'Ser uno de los grupos empresariales más influyentes de clase mundial en recursos naturales, energía, salud, infraestructura, tecnología y desarrollo territorial.',
                'items' => [
                    ['Presencia operativa en América Latina y Asia', null],
                    ['Referente regional en ecosistemas integrados', null],
                    ['Estándares internacionales de gobernanza', null],
                ],
            ],
            'mision' => [
                'titulo' => 'Misión',
                'texto' => "Desarrollamos ecosistemas empresariales mediante alianzas internacionales y excelencia corporativa.\nInvertimos estratégicamente, operamos con excelencia, desarrollamos ecosistemas e innovamos permanentemente.",
            ],
            'valores' => [
                'titulo' => 'Valores corporativos',
                'subtitulo' => 'Los valores orientan el comportamiento del Grupo.',
                'items' => array_map(fn($t) => [$t, null], [
                    'Excelencia', 'Integridad', 'Innovación', 'Colaboración',
                    'Orientación al Cliente', 'Sostenibilidad', 'Espíritu Emprendedor', 'Compromiso con las Personas',
                ]),
            ],
            'principios' => [
                'titulo' => 'Principios estratégicos',
                'subtitulo' => 'Los principios rigen la toma de decisiones estratégicas.',
                'items' => array_map(fn($t) => [$t, null], [
                    'Visión de largo plazo', 'Integración de ecosistemas', 'Asignación disciplinada del capital',
                    'Innovación como ventaja competitiva', 'Gobernanza y transparencia', 'Desarrollo del talento',
                    'Sostenibilidad y responsabilidad', 'Alianzas estratégicas', 'Orientación al cliente y al mercado', 'Impacto positivo',
                ]),
            ],
            'ceo' => [
                'titulo' => 'Mensaje del CEO',
                'frase' => 'No administramos empresas. Administramos ecosistemas empresariales.',
                'subtitulo' => 'Filosofía fundacional de HUK-THE-ICHI GLOBAL GROUP',
                'texto' => '<p>En un mundo donde la complejidad de los mercados exige más que intermediación, HUK-THE-ICHI GLOBAL GROUP nace con una convicción clara: transformar oportunidades territoriales y sectoriales en activos productivos, infraestructura, cadenas de valor y desarrollo sostenible.</p>'
                    . '<p>Nuestra evolución de empresa multisectorial a plataforma corporativa internacional de desarrollo e inversión responde a una visión de largo plazo. No buscamos simplemente conectar negocios; buscamos originar, estructurar, financiar y operar proyectos que generen valor duradero para socios, inversionistas, comunidades y territorios.</p>'
                    . '<p>Con una arquitectura de diez ecosistemas empresariales y un Corporate Center orientado a la disciplina de capital y la excelencia operativa, estamos construyendo una organización capaz de competir a escala regional e internacional.</p>'
                    . '<p>Aspiramos a convertirnos en una de las plataformas de desarrollo e inversión más relevantes de América del Sur.</p>',
                'firma' => 'Moisés Villafuerte Torres',
                'firma_detalle' => 'Chief Executive Officer',
            ],
        ];

        $ahora = date('Y-m-d H:i:s');
        foreach ($secciones as $clave => $s) {
            $items = $s['items'] ?? [];
            unset($s['items']);
            $this->insert('{{%pagina_seccion}}', array_merge(['pagina' => 'nosotros', 'clave' => $clave, 'estado' => '1'], $s));
            $id = $this->db->getLastInsertID();
            foreach ($items as $i => [$titulo, $texto]) {
                $this->insert('{{%pagina_item}}', [
                    'idseccion' => $id, 'titulo' => $titulo, 'texto' => $texto,
                    'orden' => $i + 1, 'estado' => '1', 'fecha_registro' => $ahora,
                ]);
            }
        }
    }

    public function safeDown()
    {
        $ids = (new \yii\db\Query())->select('idpermiso')->from('{{%permisos}}')->where(['alias' => array_keys(self::PERMISOS)])->column();
        $this->delete('{{%rol_permiso}}', ['idpermiso' => $ids]);
        $this->delete('{{%permisos}}', ['idpermiso' => $ids]);
        $this->dropTable('{{%pagina_historial}}');
        $this->dropTable('{{%pagina_item}}');
        $this->dropTable('{{%pagina_seccion}}');
    }
}
