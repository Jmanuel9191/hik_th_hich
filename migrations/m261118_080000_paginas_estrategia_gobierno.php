<?php

use yii\db\Migration;

/**
 * Páginas: Inicio (cifras, lemas del pie), Nosotros → Preguntas frecuentes, Estrategia (expansión con mapa,
 * fases, modelo de valor) y Gobierno y ESG (gobierno, estructura, impacto, socios). Textos del brochure 2026.
 * pagina_item: + cifra, + codigo (país del mapa), título hasta 255.
 * Ecosistema y Equipo: + destacado.
 */
class m261118_080000_paginas_estrategia_gobierno extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%pagina_item}}', 'titulo', $this->string(255)->notNull());
        $this->addColumn('{{%pagina_item}}', 'cifra', $this->string(60)->null()->after('texto'));
        $this->addColumn('{{%pagina_item}}', 'codigo', $this->string(10)->null()->after('cifra'));

        $this->addColumn('{{%ecosistema}}', 'destacado', $this->char(1)->notNull()->defaultValue('0')->after('estado'));
        $this->addColumn('{{%equipo}}', 'destacado', $this->char(1)->notNull()->defaultValue('0')->after('estado'));
        // Los 8 ejes estratégicos del brochure = ecosistemas prioritarios
        $this->update('{{%ecosistema}}', ['destacado' => '1'], ['slug' => [
            'recursos-naturales', 'energia', 'salud', 'infraestructura', 'agroindustria', 'industrial', 'financiero', 'digital-e-innovacion',
        ]]);

        $this->update('{{%permisos}}', ['descripcion' => 'Permite ver el contenido de las páginas institucionales'], ['alias' => 'paginas_ver']);

        foreach ($this->contenido() as [$pagina, $clave, $campos, $items]) {
            $this->insert('{{%pagina_seccion}}', array_merge(['pagina' => $pagina, 'clave' => $clave, 'estado' => '1'], $campos));
            $id = $this->db->getLastInsertID();
            foreach ($items as $i => $item) {
                $this->insert('{{%pagina_item}}', array_merge(['texto' => null, 'cifra' => null, 'codigo' => null], $item, [
                    'idseccion' => $id, 'orden' => $i + 1, 'estado' => '1', 'fecha_registro' => date('Y-m-d H:i:s'),
                ]));
            }
        }
    }

    /** [página, clave, campos del bloque, ítems] */
    private function contenido()
    {
        $t = fn(...$titulos) => array_map(fn($x) => ['titulo' => $x], $titulos);
        return [
            ['nosotros', 'faq', ['titulo' => 'Preguntas frecuentes', 'subtitulo' => 'Lo que más nos consultan socios e inversionistas.'], [
                ['titulo' => '¿Qué es HUK-THE-ICHI GLOBAL GROUP?',
                    'texto' => '<p>Es una plataforma corporativa multinacional que origina, estructura, financia, ejecuta y opera proyectos estratégicos de escala, organizada en diez ecosistemas empresariales bajo un Corporate Center.</p>'],
                ['titulo' => '¿En qué sectores trabajan?',
                    'texto' => '<p>Recursos naturales, energía, salud, infraestructura, agroindustria, industria, finanzas, comercio global, digital e innovación y desarrollo territorial.</p>'],
                ['titulo' => '¿Cómo puedo ser socio o inversionista?',
                    'texto' => '<p>Registrate como inversor desde la web. Después de verificar tu correo, nuestro equipo revisa tu solicitud y, una vez aprobada, accedés a la información de los proyectos de tus áreas de interés.</p>'],
            ]],
            ['inicio', 'cifras', ['titulo' => 'HUK en cifras'], [
                ['cifra' => '10', 'titulo' => 'Ecosistemas empresariales'],
                ['cifra' => '4', 'titulo' => 'Países en expansión'],
                ['cifra' => 'USD 5-10 mil M', 'titulo' => 'Capital a movilizar 2026–2035'],
            ]],
            ['inicio', 'pie', ['frase' => 'Sembramos progreso, extraemos futuro.', 'subtitulo' => 'One vision • Infinite opportunities'], []],
            ['estrategia', 'expansion', [
                'titulo' => 'Visión de expansión 2026–2035',
                'subtitulo' => 'HUK South America',
                'firma' => 'Objetivo de capital movilizado 2026–2035',
                'frase' => 'USD 5 - 10 mil millones',
            ], [
                ['codigo' => 'BO', 'titulo' => 'Resources & Development'],
                ['codigo' => 'AR', 'titulo' => 'Energy & Strategic Industries'],
                ['codigo' => 'PE', 'titulo' => 'Resources & Strategic Industries'],
                ['codigo' => 'BR', 'titulo' => 'Industry & Capital'],
            ]],
            ['estrategia', 'fases', ['titulo' => 'Horizonte 2035 — tres fases'], [
                ['cifra' => '2026–2027', 'titulo' => 'Build the platform',
                    'texto' => 'Consolidación de Holding, plataformas nacionales, Investment Committee, Project Finance Office, ESG Office y proyectos estratégicos iniciales.'],
                ['cifra' => '2028–2030', 'titulo' => 'Scale',
                    'texto' => 'Escalamiento de minería, energía, agroindustria, industrialización, logística, salud y proyectos estratégicos.'],
                ['cifra' => '2031–2035', 'titulo' => 'Regional leadership'],
            ]],
            ['estrategia', 'modelo', [
                'titulo' => 'Modelo de creación de valor',
                'frase' => 'Territorio + Recursos + Proyectos + Tecnología + Capital + Mercados = Desarrollo',
            ], $t('Originar', 'Estructurar', 'Incorporar socios', 'Levantar capital', 'Ejecutar', 'Operar', 'Expandir / Desinvertir')],
            ['estrategia', 'propuesta_valor', ['titulo' => 'Propuesta de valor'], [
                ['titulo' => 'Confianza', 'texto' => 'Relaciones empresariales basadas en honestidad, transparencia y responsabilidad.'],
                ['titulo' => 'Seguridad', 'texto' => 'Conexiones comerciales estructuradas y relaciones con actores identificados.'],
                ['titulo' => 'Integración', 'texto' => 'Conexión entre producción, mercados, proyectos, tecnología y capital.'],
                ['titulo' => 'Resultados', 'texto' => 'Orientación hacia la transformación de oportunidades en negocios reales.'],
            ]],
            ['gobierno', 'gobierno', [
                'titulo' => 'Principios organizacionales',
                'frase' => 'Centralized governance · Disciplined capital allocation · Decentralized operations · Shared value creation',
            ], $t('Gobierno corporativo sólido y profesional', 'Corporate Center compacto y estratégico', 'Asignación disciplinada de capital',
                'Ecosistemas con responsabilidad real sobre resultados', 'Operaciones descentralizadas y servicios corporativos compartidos')],
            ['gobierno', 'estructura', ['titulo' => 'Estructura proyectada', 'subtitulo' => 'Gobierno centralizado · Operación descentralizada'],
                $t('Board', 'CEO', 'Corporate Center', 'Business Ecosystems', 'Business Units', 'Strategic Projects')],
            ['gobierno', 'impacto', [
                'titulo' => 'Desarrollo sostenible e impacto',
                'texto' => 'Cada proyecto incorpora un sistema de medición de impacto.',
                'frase' => 'Crecimiento con propósito: construir capacidades productivas que permanezcan en los territorios.',
            ], $t('Inversión', 'Empleo directo e indirecto', 'Proveedores locales', 'Impuestos', 'Exportaciones', 'Infraestructura',
                'Energía generada', 'Emisiones evitadas', 'Capacitación')],
            ['gobierno', 'socios', [
                'titulo' => 'Propuesta de valor para socios',
                'texto' => 'HUK-THE-ICHI busca convertirse en un socio estratégico para empresas, inversionistas e instituciones.',
                'firma' => 'Quiero ser socio',
            ], [
                ['titulo' => 'Socio industrial', 'texto' => 'Tecnología + mercado + experiencia operativa'],
                ['titulo' => 'HUK-THE-ICHI', 'texto' => 'Territorio + proyecto + estructuración + ejecución'],
                ['titulo' => 'Inversionista', 'texto' => 'Capital'],
                ['titulo' => 'Instituciones públicas', 'texto' => 'Entorno habilitante'],
                ['titulo' => 'Comunidades', 'texto' => 'Licencia social y desarrollo territorial'],
            ]],
            ['gobierno', 'razones', ['titulo' => '¿Por qué asociarse con HUK-THE-ICHI GLOBAL GROUP?'],
                $t('Plataforma multisectorial con visión de integración de ecosistemas', 'Capacidad de originar y estructurar proyectos bancables',
                    'Conocimiento territorial profundo en América del Sur', 'Modelo flexible de co-inversión y partnership',
                    'Compromiso con gobernanza, transparencia e impacto sostenible', 'Ambición de escala regional e internacional con disciplina de capital')],
        ];
    }

    public function safeDown()
    {
        $ids = (new \yii\db\Query())->select('idseccion')->from('{{%pagina_seccion}}')->where(['or',
            ['pagina' => ['inicio', 'estrategia', 'gobierno']], ['pagina' => 'nosotros', 'clave' => 'faq']])->column();
        $this->delete('{{%pagina_item}}', ['idseccion' => $ids]);
        $this->delete('{{%pagina_seccion}}', ['idseccion' => $ids]);
        $this->dropColumn('{{%equipo}}', 'destacado');
        $this->dropColumn('{{%ecosistema}}', 'destacado');
        $this->dropColumn('{{%pagina_item}}', 'codigo');
        $this->dropColumn('{{%pagina_item}}', 'cifra');
        $this->alterColumn('{{%pagina_item}}', 'titulo', $this->string(150)->notNull());
    }
}
