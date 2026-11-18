<?php

namespace app\models;

use Yii;

class Parametros extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'parametros';
    }   

 public function rules()
{
    return [
        //[['nombre'], 'required'],
        [['nombre'], 'string', 'max' => 150],
        [['unidad'], 'string', 'max' => 20],
        [['es_subtitulo'], 'integer'],
        [['es_negrita'], 'integer'],
        [['referencia_texto'], 'safe'],
        [['tipo_campo'], 'string', 'max' => 20],
        [['tipo_campo'], 'default', 'value' => 'texto'],
        [['tipo_campo'], 'in', 'range' => ['texto', 'select', 'select_texto']],
        [['opciones'], 'string'],
        [['salto_linea'], 'integer'],
        [['salto_linea'], 'default', 'value' => 1],
        [['valor_default'], 'string'],
    ];
}

public function attributeLabels()
{
    return [
        'idparametro' => 'ID',
        'es_negrita'  => 'Neg',
        'nombre' => 'Nombre del Parámetro',
        'es_subtitulo' => 'Es subtítulo',
        'unidad' => 'Unidad',
        'referencia_texto' => 'Valores de Referencia',
        'fecha_registro' => 'Fecha de Registro',
        'tipo_campo' => 'Tipo de campo',
        'opciones' => 'Opciones',
        'salto_linea' => 'Salto de línea',
        'valor_default' => 'Texto predeterminado',
    ];
}
    // Productos que usan este parámetro (vía la tabla puente)
    public function getProductos()
    {
        return $this->hasMany(Productos::class, ['idproducto' => 'idproducto'])
            ->viaTable('producto_parametro', ['idparametro' => 'idparametro']);
    }

    public function getCantidadProductos()
    {
        return $this->getProductos()->count();
    }
}