<?php

namespace app\models;

use Yii;

class ProductoMaterial extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'producto_material';
    }

    public function rules()
    {
        return [
            [['idproducto_servicio', 'idproducto_material'], 'required'],
            [['idproducto_servicio', 'idproducto_material'], 'integer'],
            [['cantidad'], 'number', 'min' => 0.01],
            [['cantidad'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idproducto_material_rel' => 'ID',
            'idproducto_servicio' => 'Examen/Servicio',
            'idproducto_material' => 'Material',
            'cantidad' => 'Cantidad usada',
        ];
    }

    // El producto-examen que usa este material (ej: Hemograma Completo)
    public function getServicio()
    {
        return $this->hasOne(Productos::class, ['idproducto' => 'idproducto_servicio']);
    }

    // El producto-material en sí (ej: Jeringa)
    public function getMaterial()
    {
        return $this->hasOne(Productos::class, ['idproducto' => 'idproducto_material']);
    }
}