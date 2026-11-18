<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Cambio en la pantalla Roles y permisos.
 *
 * @property int $idhistorial
 * @property int $idrol
 * @property int $idpermiso
 * @property string $accion   Asignado | Quitado
 * @property int|null $idusuario_actor
 * @property string|null $ip
 * @property string $fecha
 */
class RolPermisoHistorial extends ActiveRecord
{
    public static function tableName()
    {
        return 'rol_permiso_historial';
    }

    public function getRol()
    {
        return $this->hasOne(Roles::class, ['idrol' => 'idrol']);
    }

    public function getPermiso()
    {
        return $this->hasOne(Permisos::class, ['idpermiso' => 'idpermiso']);
    }

    public function getUsuarioActor()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_actor']);
    }
}
