<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class CampaniaSucursal extends ActiveRecord
{
    public static function tableName()
    {
        return 'campania_sucursal';
    }

    public function rules()
    {
        return [
            [['idcampania', 'idsucursal'], 'required'],
            [['idcampania', 'idsucursal'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idcampania' => 'Campaña',
            'idsucursal' => 'Sucursal',
        ];
    }

    public function getCampania()
    {
        return $this->hasOne(Campanias::class, ['idcampania' => 'idcampania']);
    }

    public function getSucursal()
    {
        return $this->hasOne(Sucursal::class, ['idsucursal' => 'idsucursal']);
    }
}
