<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class Permisos extends ActiveRecord
{


    public static function tableName()
    {
        return 'permisos';
    }

    public function rules()
    {
        return [
            [['alias', 'alias'], 'required'],
            [['alias'], 'string', 'max' => 250],
            [['descripcion'], 'string'],
            [['estado'], 'string', 'max' => 1],
            [['alias'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idpermiso' => 'ID',
            'alias' => 'Clave Técnica',
            'estado' => 'Estado',
            'fecha_registro' => 'Fecha de Registro',
        ];
    }
}
