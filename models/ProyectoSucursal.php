<?php

// models/ProyectoSucursal.php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class ProyectoSucursal extends ActiveRecord
{
    public static function tableName()
    {
        return 'proyecto_sucursal';
    }

    public function rules()
    {
        return [
            [['idproyecto', 'idsucursal'], 'required'],
            [['idproyecto', 'idsucursal'], 'integer'],
        ];
    }

    public function getSucursal()
    {
        return $this->hasOne(Sucursal::class, ['idsucursal' => 'idsucursal']);
    }



}
