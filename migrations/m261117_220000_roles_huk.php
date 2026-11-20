<?php

use yii\db\Migration;

/**
 * Roles de HUK + limpieza de datos de prueba.
 *
 *  - Deja solo al usuario adminmaster (idusuario = 1) como Superadmin.
 *  - Roles: 1 Superadmin, 2 Distribuidor, 3 Gerente de área, 4 Editor de contenido, 5 Inversor.
 *  - Permisos: borra los del sistema anterior; quedan solo los de usuarios.
 *  - Un solo estado de bloqueo: '0' = Bloqueado (antes había '' y '2' = Inactivo).
 *  - usuarios: InnoDB + utf8mb4, usuario y email únicos, FK a roles, valores por defecto en empresa y zona_horaria.
 *  - historial_usuarios: columna ip y borra registros que guardaron contraseñas/tokens.
 *
 * ¡Borra datos! Hacé un respaldo de la BD antes de correrla. No tiene vuelta atrás.
 */
class m261117_220000_roles_huk extends Migration
{
    public function up()
    {
        $idAdmin = 1;

        // 1. Usuarios de prueba (y su historial)
        $this->delete('{{%historial_usuarios}}', ['<>', 'idusuario_afectado', $idAdmin]);
        $this->delete('{{%usuarios}}', ['<>', 'idusuario', $idAdmin]);

        // Registros viejos que guardaron la contraseña, el hash o tokens en el texto
        $this->delete('{{%historial_usuarios}}', ['or',
            ['like', 'descripcion', 'Contrasena cambiado'],
            ['like', 'descripcion', 'Auth_key cambiado'],
            ['like', 'descripcion', 'Google_token cambiado'],
        ]);

        // 2. Un solo estado de bloqueo
        $this->update('{{%usuarios}}', ['estado' => '0'], ['estado' => ['', '2']]);

        // 3. Roles
        $this->delete('{{%rol_permiso}}');
        $this->delete('{{%roles}}');
        $ahora = date('Y-m-d H:i:s');
        $this->batchInsert('{{%roles}}', ['idrol', 'nombre', 'alias', 'descripcion', 'fecha_registro', 'estado'], [
            [1, 'Superadmin', 'superadmin', 'Acceso total: usuarios, configuración y todos los módulos.', $ahora, '1'],
            [2, 'Distribuidor', 'distribuidor', 'Aprueba o rechaza inversores y les asigna áreas.', $ahora, '1'],
            [3, 'Gerente de área', 'gerente', 'Gestiona proyectos, documentos e inversiones de sus áreas.', $ahora, '1'],
            [4, 'Editor de contenido', 'editor', 'Gestiona el contenido de la web: logo, datos, banners, textos y equipo.', $ahora, '1'],
            [5, 'Inversor', 'inversor', 'No entra al panel. Usa la web para ver proyectos de sus áreas aprobadas.', $ahora, '1'],
        ]);
        $this->update('{{%usuarios}}', ['idrol' => 1], ['idusuario' => $idAdmin]);

        // 4. Permisos: solo los de usuarios
        $this->delete('{{%permisos}}', ['not like', 'alias', 'usuarios\_%', false]);
        $this->delete('{{%permisos}}', ['alias' => 'usuarios_inactividad']);
        $this->update('{{%permisos}}', ['descripcion' => 'Permite bloquear o activar un usuario indicando el motivo'], ['alias' => 'usuarios_estado']);
        $this->insert('{{%permisos}}', [
            'alias' => 'usuarios_eliminar',
            'descripcion' => 'Permite eliminar un usuario',
            'estado' => '1',
            'fecha_registro' => $ahora,
        ]);
        $this->execute("INSERT INTO {{%rol_permiso}} (idrol, idpermiso) SELECT 1, idpermiso FROM {{%permisos}} WHERE alias LIKE 'usuarios\\_%'");

        // 5. Tabla usuarios
        $this->execute('ALTER TABLE {{%usuarios}} ENGINE=InnoDB');
        $this->execute('ALTER TABLE {{%usuarios}} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $this->createIndex('uq_usuarios_usuario', '{{%usuarios}}', 'usuario', true);
        $this->createIndex('uq_usuarios_email', '{{%usuarios}}', 'email', true);
        $this->addForeignKey('fk_usuarios_idrol', '{{%usuarios}}', 'idrol', '{{%roles}}', 'idrol', 'RESTRICT', 'CASCADE');
        // Sin valor por defecto, MySQL en modo estricto rechaza crear usuarios desde el formulario.
        $this->alterColumn('{{%usuarios}}', 'empresa', $this->string(500)->notNull()->defaultValue(''));
        $this->alterColumn('{{%usuarios}}', 'zona_horaria', $this->string(50)->notNull()->defaultValue('America/La_Paz'));

        // 6. IP en el historial
        $this->addColumn('{{%historial_usuarios}}', 'ip', $this->string(45)->null()->defaultValue(null));
    }

    public function down()
    {
        echo "m261117_220000_roles_huk borra datos y no se puede revertir. Restaure el respaldo de la BD.\n";
        return false;
    }
}
