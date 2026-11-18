<?php

namespace app\models;

use PhpParser\Comment\Doc;

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
class HistorialTareas extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'historial_tareas';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['iddoctor', 'idusuario'], 'required'],
            [['descripcion', 'accion'], 'string'],
            [['fecha'], 'safe'],
            [['iddoctor', 'idusuario'], 'integer'],
        ];


    }
    public function attributeLabels()
    {
        return [
            'idhistorial' => 'ID',
            'iddoctor' => 'Doctor',
            'idusuario' => 'Usuario',
            'accion' => 'Acción',
            'descripcion' => 'Descripción',
            'fecha' => 'Fecha',
        ];
    }

    public function getTarea()
    {
        return $this->hasOne(Doctores::className(), ['iddoctor' => 'iddoctor']);
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['idusuario' => 'idusuario']);
    }

    /**
     * @inheritdoc
     */






}
