<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class RolPermiso extends ActiveRecord
{
    public static function tableName()
    {
        return 'rol_permiso';
    }

    public function rules()
    {
        return [
            [['idrol', 'idpermiso'], 'required'],
            [['idrol', 'idpermiso'], 'integer'],
            [['idrol', 'idpermiso'], 'unique', 'targetAttribute' => ['idrol', 'idpermiso']],
        ];
    }

    public function getRol()
    {
        return $this->hasOne(Roles::class, ['idrol' => 'idrol']);
    }

    public function getPermiso()
    {
        return $this->hasOne(Permisos::class, ['idpermiso' => 'idpermiso']);
    }
}
