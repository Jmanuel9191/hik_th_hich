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
class HistorialCampanias extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'historial_campanias';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idcampania', 'idusuario'], 'required'],
            [['descripcion', 'accion'], 'string'],
            [['fecha'], 'safe'],
            [['idproyecto', 'idusuario'], 'integer'],
        ];


    }
    public function attributeLabels()
    {
        return [
            'idhistorial' => 'ID',
            'idcampania' => 'Campaña',
            'idusuario' => 'Usuario',
            'accion' => 'Acción',
            'descripcion' => 'Descripción',
            'fecha' => 'Fecha',
        ];
    }

    public function getCampania()
    {
        return $this->hasOne(Campanias::className(), ['idcampania' => 'idcampania']);
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['idusuario' => 'idusuario']);
    }

    /**
     * @inheritdoc
     */






}
