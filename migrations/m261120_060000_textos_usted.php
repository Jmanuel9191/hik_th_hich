<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Trato de usted: actualiza los textos que se sembraron con voseo y quedaron guardados en la base.
 *  - Términos y privacidad: si la versión vigente es todavía el texto inicial, se publica la versión siguiente
 *    con el mismo contenido en trato de usted (las versiones no se editan; lo que aceptó cada inversor queda igual).
 *  - Página Estrategia/Gobierno ("Registrate como inversor…") y la plantilla "En blanco".
 * Solo cambia lo que sigue exactamente igual al texto original: si alguien lo editó desde el panel, no se toca.
 * Se puede correr más de una vez.
 */
class m261120_060000_textos_usted extends Migration
{
    const TERMINOS_VIEJO = '<h3>Términos de uso</h3><p>Al enviar tu solicitud aceptás que HUK-THE-ICHI GLOBAL GROUP evalúe tu perfil como inversor y se comunique con vos por los medios que nos diste.</p><p>El acceso a los proyectos se habilita solo si tu solicitud es aprobada y requiere aceptar, además, un acuerdo de confidencialidad.</p><h3>Política de privacidad</h3><p>Usamos tus datos solo para evaluar tu perfil como inversor, comunicarnos con vos y darte acceso a la plataforma. No los vendemos ni los compartimos con terceros sin tu autorización. Tu documento de identidad solo lo ve el equipo de HUK.</p><p>Podés pedir que corrijamos o borremos tus datos escribiéndonos a los medios de contacto de la web.</p>';
    const TERMINOS_NUEVO = '<h3>Términos de uso</h3><p>Al enviar su solicitud acepta que HUK-THE-ICHI GLOBAL GROUP evalúe su perfil como inversor y se comunique con usted por los medios que nos proporcionó.</p><p>El acceso a los proyectos se habilita solo si su solicitud es aprobada y requiere aceptar, además, un acuerdo de confidencialidad.</p><h3>Política de privacidad</h3><p>Usamos sus datos solo para evaluar su perfil como inversor, comunicarnos con usted y darle acceso a la plataforma. No los vendemos ni los compartimos con terceros sin su autorización. Su documento de identidad solo lo ve el equipo de HUK.</p><p>Puede pedir que corrijamos o borremos sus datos escribiéndonos a los medios de contacto de la web.</p>';
    const PAGINA_VIEJO = '<p>Registrate como inversor desde la web. Después de verificar tu correo, nuestro equipo revisa tu solicitud y, una vez aprobada, accedés a la información de los proyectos de tus áreas de interés.</p>';
    const PAGINA_NUEVO = '<p>Regístrese como inversor desde la web. Después de verificar su correo, nuestro equipo revisa su solicitud y, una vez aprobada, accede a la información de los proyectos de sus áreas de interés.</p>';
    const PLANTILLA_VIEJO = 'Sin secciones: armás la página desde cero.';
    const PLANTILLA_NUEVO = 'Sin secciones: puede armar la página desde cero.';

    public function safeUp()
    {
        if ($this->db->getTableSchema('{{%terminos_version}}', true) !== null) {
            $vigente = (new Query())->from('{{%terminos_version}}')->orderBy(['numero' => SORT_DESC])->one($this->db);
            if ($vigente && $vigente['texto'] === self::TERMINOS_VIEJO) {
                $this->insert('{{%terminos_version}}', [
                    'numero' => (int) $vigente['numero'] + 1, 'titulo' => $vigente['titulo'], 'texto' => self::TERMINOS_NUEVO,
                    'nota' => 'Mismo contenido, en trato de usted', 'fecha_publicacion' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        $this->update('{{%pagina_item}}', ['texto' => self::PAGINA_NUEVO], ['texto' => self::PAGINA_VIEJO]);
        if ($this->db->getTableSchema('{{%proyecto_plantilla}}', true) !== null) {
            $this->update('{{%proyecto_plantilla}}', ['descripcion' => self::PLANTILLA_NUEVO], ['descripcion' => self::PLANTILLA_VIEJO]);
        }
    }

    public function safeDown()
    {
        $this->delete('{{%terminos_version}}', ['texto' => self::TERMINOS_NUEVO, 'nota' => 'Mismo contenido, en trato de usted']);
        $this->update('{{%pagina_item}}', ['texto' => self::PAGINA_VIEJO], ['texto' => self::PAGINA_NUEVO]);
        $this->update('{{%proyecto_plantilla}}', ['descripcion' => self::PLANTILLA_VIEJO], ['descripcion' => self::PLANTILLA_NUEVO]);
    }
}
