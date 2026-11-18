<?php

namespace app\models;

/**
 * This is the model class for table "categoria".
 *
 * @property integer $idcategoria
 * @property string $nombre
 * @property string $descripcion
 * @property string $imagen
 * @property integer $idpadre
 * @property integer $modulo
 * @property string $estado
 */
class HistorialProyectos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'historial_proyectos';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idproyecto', 'idusuario'], 'required'],
            [['descripcion', 'accion'], 'string'],
            [['fecha'], 'safe'],
            [['idproyecto', 'idusuario'], 'integer'],
        ];


    }
    public function attributeLabels()
    {
        return [
            'idhistorial' => 'ID',
            'idproyecto' => 'Proyecto',
            'idusuario' => 'Usuario',
            'accion' => 'Acción',
            'descripcion' => 'Descripción',
            'fecha' => 'Fecha',
        ];
    }

    public function getProyecto()
    {
        return $this->hasOne(Proyectos::className(), ['idproyecto' => 'idproyecto']);
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['idusuario' => 'idusuario']);
    }

    /**
     * @inheritdoc
     */






}
