<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Página de proyecto · Paq B
 *  - proyecto.mostrar_ficha: la página empieza con la Ficha automática (datos de la inversión). Por defecto, sí.
 *  - Se quita el bloque "Condiciones de inversión" (lo reemplaza la Ficha): se borran los que hubiera.
 *  - A cada plantilla con secciones se le agrega "Galería y documentos" antes del Aviso (si todavía no la tiene).
 *    La sección está en migrations/datos/seccion_galeria.php.
 * Se puede correr sobre una base que ya tenga parte de esto: lo que existe no se toca.
 */
class m261120_030000_paq_b extends Migration
{
    public function safeUp()
    {
        if ($this->db->getTableSchema('{{%proyecto}}', true)->getColumn('mostrar_ficha') === null) {
            $this->addColumn('{{%proyecto}}', 'mostrar_ficha', $this->char(1)->notNull()->defaultValue('1')
                ->comment('1 = la página web empieza con la Ficha')->after('destacado'));
        }

        $this->delete('{{%proyecto_bloque}}', ['tipo' => 'condiciones']);

        $seccion = require __DIR__ . '/datos/seccion_galeria.php';
        $ahora = date('Y-m-d H:i:s');
        $plantillas = (new Query())->select('idplantilla')->from('{{%proyecto_plantilla}}')->column($this->db);
        foreach ($plantillas as $id) {
            $secciones = (new Query())->select(['idseccion', 'orden', 'ancla'])->from('{{%proyecto_seccion}}')
                ->where(['idplantilla' => $id])->orderBy(['orden' => SORT_ASC, 'idseccion' => SORT_ASC])->all($this->db);
            if (!$secciones || in_array($seccion['ancla'], array_column($secciones, 'ancla'), true)) {
                continue; // "En blanco" o ya la tiene
            }
            // va donde está el Aviso (y el Aviso y lo que sigue bajan un lugar); si no hay Aviso, al final
            $orden = null;
            foreach ($secciones as $s) {
                if ($s['ancla'] === 'aviso') {
                    $orden = (int) $s['orden'];
                    break;
                }
            }
            if ($orden === null) {
                $orden = (int) end($secciones)['orden'] + 1;
            } else {
                $this->update('{{%proyecto_seccion}}', ['orden' => new \yii\db\Expression('orden + 1')], ['and', ['idplantilla' => $id], ['>=', 'orden', $orden]]);
            }
            $this->insert('{{%proyecto_seccion}}', [
                'idplantilla' => $id, 'orden' => $orden, 'antetitulo' => $seccion['antetitulo'], 'titulo' => $seccion['titulo'],
                'menu' => $seccion['menu'], 'ancla' => $seccion['ancla'], 'fondo' => $seccion['fondo'], 'diseno' => $seccion['diseno'],
                'en_menu' => $seccion['en_menu'] ? '1' : '0', 'numerada' => $seccion['numerada'] ? '1' : '0', 'visible' => '1', 'fecha_registro' => $ahora,
            ]);
            $idseccion = (int) $this->db->getLastInsertID();
            foreach ($seccion['bloques'] as $k => [$tipo, $ancho, $datos]) {
                $this->insert('{{%proyecto_bloque}}', [
                    'idseccion' => $idseccion, 'orden' => $k + 1, 'tipo' => $tipo, 'ancho' => $ancho,
                    'datos' => json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'visible' => '1', 'fecha_registro' => $ahora,
                ]);
            }
        }
    }

    public function safeDown()
    {
        $ids = (new Query())->select('idseccion')->from('{{%proyecto_seccion}}')
            ->where(['and', ['not', ['idplantilla' => null]], ['ancla' => 'galeria']])->column($this->db);
        if ($ids) {
            $this->delete('{{%proyecto_seccion}}', ['idseccion' => $ids]); // los bloques se borran en cascada
        }
        if ($this->db->getTableSchema('{{%proyecto}}', true)->getColumn('mostrar_ficha') !== null) {
            $this->dropColumn('{{%proyecto}}', 'mostrar_ficha');
        }
    }
}
