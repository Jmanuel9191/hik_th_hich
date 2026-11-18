<?php
namespace app\models;

class OrdenDetalle extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'orden_detalle';
    }

    public function rules()
    {
        return [
            [['idorden', 'idproducto'], 'integer'],
            [['precio', 'subtotal'], 'number', 'min' => 0],
            [['precio_costo'], 'number', 'min' => 0],
        ];
    }

    public function getProducto()
    {
        return $this->hasOne(Productos::class, ['idproducto' => 'idproducto']);
    }

    public function getOrden()
    {
        return $this->hasOne(Ordenes::class, ['idorden' => 'idorden']);
    }
}