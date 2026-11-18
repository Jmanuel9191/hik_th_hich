<?php
namespace app\models;

class ProductoParametro extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'producto_parametro';
    }

    // Le decís a Yii2 que la PK es compuesta
    public static function primaryKey()
    {
        return ['idproducto', 'idparametro'];
    }

    public function rules()
    {
        return [
            [['idproducto', 'idparametro'], 'required'],
            [['idproducto', 'idparametro', 'orden'], 'integer'],
            [['orden'], 'default', 'value' => 1],
        ];
    }

    public function getParametro()
    {
        return $this->hasOne(Parametros::class, ['idparametro' => 'idparametro']);
    }

    public function getProducto()
    {
        return $this->hasOne(Productos::class, ['idproducto' => 'idproducto']);
    }
}