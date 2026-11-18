<?php

namespace app\models;

use Yii;

/**
 * Esta es la clase modelo para la tabla "audiencias".
 *
 * @property int $idaudiencia
 * @property string $nombre
 * @property string|null $descripcion
 * @property int|null $estado
 * @property string|null $fecha_registro
 *
 * @property CampaniasAudiencia[] $campaniasAudiencias
 */
class Audiencias extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'audiencias';
    }

    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['descripcion'], 'string'],
            [['estado'], 'integer'],
            [['fecha_registro'], 'safe'],
            [['nombre'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idaudiencia' => 'ID',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'estado' => 'Estado',
            'fecha_registro' => 'Fecha de Registro',
        ];
    }
    public static function getListaAudiencia()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->all(), 'idaudiencia', 'nombre');
    }


    public function getCampaniasAudiencias()
    {
        return $this->hasMany(CampaniasAudiencia::class, ['idaudiencia' => 'idaudiencia']);
    }
}
