<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class CampaniaAudiencias extends ActiveRecord
{
    public static function tableName()
    {
        return 'campanias_audiencia';
    }

    public function rules()
    {
        return [
            [['idcampania', 'idaudiencia '], 'required'],
            [['idcampania', 'idaudiencia '], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idcampania' => 'Campaña',
            'idaudiencia ' => 'Audiencia',
        ];
    }

    public function getCampania()
    {
        return $this->hasOne(Campanias::class, ['idcampania' => 'idcampania']);
    }

    public function getSector()
    {
        return $this->hasOne(Audiencias::class, ['idaudiencia ' => 'idaudiencia ']);
    }
}
