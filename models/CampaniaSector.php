<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class CampaniaSector extends ActiveRecord
{
    public static function tableName()
    {
        return 'campania_sector';
    }

    public function rules()
    {
        return [
            [['idcampania', 'idsector'], 'required'],
            [['idcampania', 'idsector'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idcampania' => 'Campaña',
            'idsector' => 'Sector',
        ];
    }

    public function getCampania()
    {
        return $this->hasOne(Campanias::class, ['idcampania' => 'idcampania']);
    }

    public function getSector()
    {
        return $this->hasOne(Sector::class, ['idsector' => 'idsector']);
    }
}
