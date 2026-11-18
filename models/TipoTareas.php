<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tipo_tarea".
 *
 * @property int $idtipo_tarea
 * @property string $nombre
 * @property string|null $descripcion
 * @property string|null $estado
 * @property string|null $fecha_registro
 *
 * @property Tareas[] $tareas
 */
class TipoTareas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tipo_tarea';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['descripcion'], 'string'],
            [['estado'], 'string', 'max' => 1],
            [['fecha_registro'], 'safe'],
            [['nombre'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idtipo_tarea' => 'ID',
            'nombre' => 'Tipo de tarea',
            'descripcion' => 'Descripción',
            'estado' => 'Estado',
            'fecha_registro' => 'Fecha de registro',
        ];
    }

    /**
     * Relación con tareas
     */
    public function getTareas()
    {
        return $this->hasMany(Tareas::class, ['idtipo_tarea' => 'idtipo_tarea']);
    }
}