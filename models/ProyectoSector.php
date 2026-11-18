<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class ProyectoSector extends ActiveRecord
{
    public static function tableName()
    {
        return 'proyecto_sector';
    }

    public function rules()
    {
        return [
            [['idproyecto', 'idsector'], 'required'],
            [['idproyecto', 'idsector'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idproyecto' => 'Proyecto',
            'idsector' => 'Sector',
        ];
    }

    public function getProyecto()
    {
        return $this->hasOne(Proyectos::class, ['idproyecto' => 'idproyecto']);
    }

    public function getSector()
    {
        return $this->hasOne(Sector::class, ['idsector' => 'idsector']);
    }
}
