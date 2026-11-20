<?php

use app\models\bloques\Catalogo;
use yii\db\Migration;

/**
 * Proyecto de ejemplo "Puerta del Sol" en BORRADOR (la página de la referencia de diseño), con id fijo 900
 * para que sus fotos vengan listas en privado/proyectos/900/. Contenido: migrations/datos/puerta_del_sol.php.
 * Si ya existe un proyecto 900 o con el slug "puerta-del-sol", no hace nada.
 * Para sacarlo: Panel → Proyectos → Puerta del Sol → Eliminar (borra también su carpeta).
 */
class m261120_020000_ejemplo_puerta_del_sol extends Migration
{
    const ID = 900;

    public function safeUp()
    {
        $existe = (new \yii\db\Query())->from('{{%proyecto}}')->where(['or', ['idproyecto' => self::ID], ['slug' => 'puerta-del-sol']])->exists();
        if ($existe) {
            echo "    > Ya hay un proyecto 900 o «puerta-del-sol»: no se carga el ejemplo.\n";
            return true;
        }
        $d = require __DIR__ . '/datos/puerta_del_sol.php';
        $p = $d['proyecto'];
        $ahora = date('Y-m-d H:i:s');
        $ideco = (new \yii\db\Query())->select('idecosistema')->from('{{%ecosistema}}')->where(['slug' => $p['ecosistema']])->scalar();
        if (!$ideco) {
            echo "    > No existe el ecosistema «{$p['ecosistema']}»: no se carga el ejemplo.\n";
            return true;
        }
        $anio = date('Y');
        $max = 0;
        foreach ((new \yii\db\Query())->select('codigo')->from('{{%proyecto}}')->where(['like', 'codigo', '-' . $anio . '-'])->column() as $c) {
            $max = max($max, (int) substr($c, strrpos($c, '-') + 1));
        }
        $this->insert('{{%proyecto}}', [
            'idproyecto' => self::ID,
            'codigo' => sprintf('HUK-MIN-%s-%03d', $anio, $max + 1),
            'slug' => $p['slug'], 'titulo' => $p['titulo'], 'tipo' => $p['tipo'], 'idecosistema' => $ideco,
            'pais' => $p['pais'], 'ciudad' => $p['ciudad'], 'ubicacion' => $p['ubicacion'], 'etapa' => $p['etapa'],
            'porcentaje_avance' => $p['porcentaje_avance'], 'estado' => 'borrador', 'resumen' => $p['resumen'],
            'rubro' => $p['rubro'], 'bajada' => $p['bajada'], 'sello' => $p['sello'],
            'elementos' => json_encode($p['elementos'], JSON_UNESCAPED_UNICODE), 'cifras' => json_encode($p['cifras'], JSON_UNESCAPED_UNICODE),
            'destacado' => $p['destacado'],
            'idmoneda' => (new \yii\db\Query())->select('idmoneda')->from('{{%moneda}}')->where(['estado' => '1'])->orderBy('orden')->scalar() ?: null,
            'imagen_banner' => $p['imagen_banner'], 'foto_principal' => $p['foto_principal'],
            'fecha_registro' => $ahora, 'fecha_actualizacion' => $ahora,
        ]);
        foreach ($d['secciones'] as $i => $s) {
            $this->insert('{{%proyecto_seccion}}', [
                'idproyecto' => self::ID, 'orden' => $i + 1, 'antetitulo' => $s['antetitulo'], 'titulo' => $s['titulo'], 'menu' => $s['menu'],
                'ancla' => $s['ancla'], 'fondo' => $s['fondo'], 'diseno' => $s['diseno'],
                'en_menu' => ($s['en_menu'] ?? true) ? '1' : '0', 'numerada' => ($s['numerada'] ?? true) ? '1' : '0',
                'visible' => '1', 'fecha_registro' => $ahora,
            ]);
            $idseccion = (int) $this->getDb()->getLastInsertID();
            foreach ($s['bloques'] as $j => [$tipo, $ancho, $datos]) {
                $b = Catalogo::crear($tipo, $datos);
                if (!$b->validate()) { // el ejemplo tiene que ser válido como si lo hubiera cargado una persona
                    throw new \RuntimeException("Bloque inválido en «{$s['ancla']}» ($tipo): " . implode(' ', $b->getFirstErrors()));
                }
                $this->insert('{{%proyecto_bloque}}', [
                    'idseccion' => $idseccion, 'orden' => $j + 1, 'tipo' => $tipo, 'ancho' => $ancho,
                    'datos' => json_encode($b->datos(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'visible' => '1', 'fecha_registro' => $ahora,
                ]);
            }
        }
        $this->insert('{{%proyecto_historial}}', [
            'idproyecto' => self::ID, 'accion' => 'Creación', 'descripcion' => 'Proyecto de ejemplo cargado (Puerta del Sol, en borrador).', 'fecha' => $ahora,
        ]);
        if (!is_dir(Yii::getAlias('@app/privado/proyectos/' . self::ID . '/bloques'))) {
            echo "    > Ojo: falta la carpeta privado/proyectos/900 (las fotos del ejemplo). Súbala desde el ZIP.\n";
        }
    }

    public function safeDown()
    {
        $this->delete('{{%proyecto}}', ['idproyecto' => self::ID, 'slug' => 'puerta-del-sol']); // secciones y bloques en cascada
    }
}
