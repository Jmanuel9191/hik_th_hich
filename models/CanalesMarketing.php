<?php

namespace app\models;

use Yii;

/**
 * Esta es la clase modelo para la tabla "canales_marketing".
 *
 * @property int $idcanal
 * @property string $nombre
 * @property string|null $descripcion
 * @property int|null $estado
 * @property string|null $fecha_registro
 */
class CanalesMarketing extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'canales_marketing';
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
            'idcanal' => 'ID',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'alias' => 'Alias',
            'estado' => 'Estado',
            'fecha_registro' => 'Fecha de Registro',
        ];
    }

    public static function getListaCanales()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->all(), 'idcanal', 'nombre');
    }
    public function getCampanias()
    {
        return $this->hasMany(Campanias::class, ['idcanal' => 'idcanal']);
    }

}
