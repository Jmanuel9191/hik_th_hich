<?php

use yii\db\Migration;

/**
 * Limpieza: tablas de los sistemas anteriores (laboratorio, campañas/tareas, inventario, permisos viejos)
 * que el proyecto HUK no usa. También quita dos claves foráneas repetidas de rol_permiso.
 *
 * IMPORTANTE: borra datos. Exportá la base antes de correrla. No se puede deshacer.
 */
class m261119_123000_limpieza_sistema_anterior extends Migration
{
    const TABLAS = [
        'administrador',
        'asignartareas',
        'campania_sector',
        'campania_sucursal',
        'campanias',
        'campanias_audiencia',
        'canales_marketing',
        'categorias_servicio',
        'config_usuarios',
        'doctores',
        'estado_general',
        'eventos_sincronizados',
        'historial_asignaciones',
        'historial_campanias',
        'historial_proyectos',
        'historial_recurso',
        'historial_tareas',
        'inventario',
        'inventario_lote_movimiento',
        'inventario_lotes',
        'material',
        'modulos',
        'orden_detalle',
        'orden_pagos',
        'ordenes',
        'parametros',
        'permiso',
        'producto_material',
        'producto_parametro',
        'productos',
        'proyecto_sector',
        'proyecto_sucursal',
        'proyectos',
        'recursos',
        'resultado_secciones',
        'resultados',
        'sector',
        'session',
        'sucursal',
        'tareas',
        'tipo_tarea',
    ];

    // rol_permiso tenía cada clave foránea dos veces; quedan fk_rol y fk_permiso (con ON DELETE CASCADE)
    const FK_REPETIDAS = ['rol_permiso_ibfk_1', 'rol_permiso_ibfk_2'];

    public function up()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0');
        foreach (self::TABLAS as $tabla) {
            if ($this->db->schema->getTableSchema($tabla, true) !== null) {
                $this->dropTable($tabla);
            }
        }
        $this->execute('SET FOREIGN_KEY_CHECKS = 1');

        foreach (self::FK_REPETIDAS as $fk) {
            $existe = (new \yii\db\Query())->from('information_schema.TABLE_CONSTRAINTS')
                ->where(['CONSTRAINT_SCHEMA' => new \yii\db\Expression('DATABASE()'), 'TABLE_NAME' => 'rol_permiso', 'CONSTRAINT_NAME' => $fk])
                ->exists($this->db);
            if ($existe) {
                $this->dropForeignKey($fk, '{{%rol_permiso}}');
            }
        }
    }

    public function down()
    {
        echo "m261119_123000_limpieza_sistema_anterior no se puede deshacer: restaure el respaldo de la base.\n";
        return false;
    }
}
