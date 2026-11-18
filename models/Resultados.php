<?php
namespace app\models;

class Resultados extends \yii\db\ActiveRecord
{
    public static function tableName() { return 'resultados'; }

    public function rules()
    {
        return [
            [['idseccion', 'idparametro'], 'required'],
            [['idseccion', 'idparametro', 'orden','es_alerta'], 'integer'],
            [['valor'], 'string', 'max' => 500],
        ];
    }

    public function getParametro()
    {
        return $this->hasOne(Parametros::class, ['idparametro' => 'idparametro']);
    }

    public function getSeccion()
    {
        return $this->hasOne(ResultadoSecciones::class, ['idseccion' => 'idseccion']);
    }
}