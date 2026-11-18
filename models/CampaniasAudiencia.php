<?php

namespace app\models;

use Yii;

/**
 * Esta es la clase modelo para la tabla "campanias_audiencia".
 *
 * @property int $idcampania
 * @property int $idaudiencia
 *
 * @property Campanias $campania
 * @property Audiencias $audiencia
 */
class CampaniasAudiencia extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'campanias_audiencia';
    }

    public function rules()
    {
        return [
            [['idcampania', 'idaudiencia'], 'required'],
            [['idcampania', 'idaudiencia'], 'integer'],
            [['idcampania', 'idaudiencia'], 'unique', 'targetAttribute' => ['idcampania', 'idaudiencia']],
            [['idcampania'], 'exist', 'skipOnError' => true, 'targetClass' => Campanias::class, 'targetAttribute' => ['idcampania' => 'idcampania']],
            [['idaudiencia'], 'exist', 'skipOnError' => true, 'targetClass' => Audiencias::class, 'targetAttribute' => ['idaudiencia' => 'idaudiencia']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idcampania' => 'Campaña',
            'idaudiencia' => 'Audiencia',
        ];
    }

    public function getCampania()
    {
        return $this->hasOne(Campanias::class, ['idcampania' => 'idcampania']);
    }

    public function getAudiencia()
    {
        return $this->hasOne(Audiencias::class, ['idaudiencia' => 'idaudiencia']);
    }
}
