<?php

namespace app\models;

/**
 * This is the model class for table "producto_categoria".
 *
 * @property integer $idpc
 * @property integer $idproducto
 * @property integer $idcategoria
 * @property string $fecha_registro
 */
class ProductoCategoria extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'producto_categoria';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idproducto', 'idcategoria', 'fecha_registro'], 'required'],
            [['idproducto', 'idcategoria'], 'integer'],
            [['fecha_registro'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'idpc' => 'Idpc',
            'idproducto' => 'Idproducto',
            'idcategoria' => 'Idcategoria',
            'fecha_registro' => 'Fecha Registro',
        ];
    }

    public function getCategoria()
    {
        return $this->hasOne(Categoria::className(), ['idcategoria' => 'idcategoria']);
    }
}
