<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Sector extends ActiveRecord
{
    public static function tableName()
    {
        return 'sector';
    }

    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['estado'], 'integer'],
            [['fecha_registro'], 'safe'],
            [['nombre'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idsector' => 'ID',
            'nombre' => 'Nombre del sector',
            'estado' => 'Estado',
            'fecha_registro' => 'Fecha de registro',
        ];
    }

    public function getProyectoSectores()
    {
        return $this->hasMany(ProyectoSector::class, ['idsector' => 'idsector']);
    }

    public static function getListaSector()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->all(), 'idsector', 'nombre');
    }


    public function getCampaniaSectores()
    {
        return $this->hasMany(CampaniaSector::class, ['idsector' => 'idsector']);
    }
}
