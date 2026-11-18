<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Sucursal extends ActiveRecord
{
    public static function tableName()
    {
        return 'sucursal';
    }

    public function rules()
    {
        return [
            [['pais'], 'required'],
            [['nombre', 'pais', 'ciudad'], 'string', 'max' => 500],
            [['estado'], 'string', 'max' => 1],
            [['fecha_registro'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idsucursal' => 'ID',
            'nombre' => 'Localidad',
            'pais' => 'País',
            'ciudad' => 'Ciudad',
            'estado' => 'Estado',
            'fecha_registro' => 'Fecha de Registro',
        ];
    }

    // Relaciones con tablas intermedias
    public function getProyectoSucursales()
    {
        return $this->hasMany(ProyectoSucursal::class, ['idsucursal' => 'idsucursal']);
    }
    public static function getListaSucursal()
    {
        $sucursales = self::find()->all();
        $resultado = [];

        foreach ($sucursales as $sucursal) {
            $resultado[$sucursal->idsucursal] = $sucursal->pais . ' - ' . $sucursal->ciudad . ' - ' . $sucursal->nombre;
        }

        return $resultado;
    }


    public function getCampaniaSucursales()
    {
        return $this->hasMany(CampaniaSucursal::class, ['idsucursal' => 'idsucursal']);
    }


}
