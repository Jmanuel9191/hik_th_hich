<?php

use app\models\bloques\Catalogo;
use yii\db\Migration;
use yii\db\Query;

/**
 * Proyecto de ejemplo Puerta del Sol (id 900): rehace su página web con los bloques del Paq B
 * (tablas, gráfico, minerales, línea de tiempo, proceso, tarjetas, galería y documentos…).
 * Contenido: migrations/datos/puerta_del_sol.php. Solo toca el proyecto 900 con slug «puerta-del-sol»:
 * se borran sus secciones (y bloques) y se cargan las nuevas. Las fotos son las mismas (privado/proyectos/900/bloques).
 * Si el proyecto no existe, no hace nada.
 */
class m261120_040000_puerta_del_sol_v2 extends Migration
{
    const ID = 900;

    public function safeUp()
    {
        $existe = (new Query())->from('{{%proyecto}}')->where(['idproyecto' => self::ID, 'slug' => 'puerta-del-sol'])->exists($this->db);
        if (!$existe) {
            echo "    > No está el proyecto de ejemplo Puerta del Sol (900): no hay nada que actualizar.\n";
            return true;
        }
        $d = require __DIR__ . '/datos/puerta_del_sol.php';
        $ahora = date('Y-m-d H:i:s');
        $this->delete('{{%proyecto_seccion}}', ['idproyecto' => self::ID]); // bloques en cascada
        foreach ($d['secciones'] as $i => $s) {
            $this->insert('{{%proyecto_seccion}}', [
                'idproyecto' => self::ID, 'orden' => $i + 1, 'antetitulo' => $s['antetitulo'], 'titulo' => $s['titulo'], 'menu' => $s['menu'],
                'ancla' => $s['ancla'], 'fondo' => $s['fondo'], 'diseno' => $s['diseno'],
                'en_menu' => ($s['en_menu'] ?? true) ? '1' : '0', 'numerada' => ($s['numerada'] ?? true) ? '1' : '0',
                'visible' => '1', 'fecha_registro' => $ahora,
            ]);
            $idseccion = (int) $this->db->getLastInsertID();
            foreach ($s['bloques'] as $j => [$tipo, $ancho, $datos]) {
                $b = Catalogo::crear($tipo, $datos);
                if (!$b->validate()) {
                    throw new \RuntimeException("Bloque inválido en «{$s['ancla']}» ($tipo): " . implode(' ', $b->getFirstErrors()));
                }
                $this->insert('{{%proyecto_bloque}}', [
                    'idseccion' => $idseccion, 'orden' => $j + 1, 'tipo' => $tipo, 'ancho' => $ancho,
                    'datos' => json_encode($b->datos(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'visible' => '1', 'fecha_registro' => $ahora,
                ]);
            }
        }
        $this->update('{{%proyecto}}', ['mostrar_ficha' => '1', 'fecha_actualizacion' => $ahora], ['idproyecto' => self::ID]);
        $this->insert('{{%proyecto_historial}}', [
            'idproyecto' => self::ID, 'accion' => 'Página web', 'descripcion' => 'Página de ejemplo actualizada con los bloques nuevos (tablas, gráfico, minerales, línea de tiempo…).', 'fecha' => $ahora,
        ]);
    }

    public function safeDown()
    {
        echo "    > La página anterior de Puerta del Sol no se restaura (es un ejemplo).\n";
        return true;
    }
}
