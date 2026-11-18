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
class HistorialAsignaciones extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'historial_asignaciones';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idasignartarea', 'idusuario'], 'required'],
            [['descripcion', 'accion'], 'string'],
            [['fecha'], 'safe'],
            [['idasignartarea', 'idusuario', 'idproyecto', 'idtarea', 'idcampania', 'idconsultor'], 'integer'],
        ];


    }
    public function attributeLabels()
    {
        return [
            'idhistorial' => 'ID',
            'idasignartarea' => 'Asigancion',
            'idusuario' => 'Usuario',
            'accion' => 'Acción',
            'idproyecto' => 'Proyecto',
            'idconsultor' => 'Usuario asiginado',
            'idtarea' => 'Tarea',
            'idcampania' => 'Campaña',
            'descripcion' => 'Descripción',
            'fecha' => 'Fecha',
        ];
    }

    public function getAsignacion()
    {
        return $this->hasOne(Asignacion::className(), ['idasignartarea' => 'idasignartarea']);
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['idusuario' => 'idusuario']);
    }

// En app\models\Asignacion

    public function getProyecto()
    {
        return $this->hasOne(Proyectos::className(), ['idproyecto' => 'idproyecto']);
    }

    public function getCampania()
    {
        return $this->hasOne(Campanias::className(), ['idcampania' => 'idcampania']);
    }

    public function getTarea()
    {
        return $this->hasOne(Tareas::className(), ['idtarea' => 'idtarea']);
    }

    public function getUsuarioAsignado()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idconsultor']);
    }

    public function getNombreOrigen()
    {
        if ($this->idproyecto) {
            return $this->proyecto->titulo ?? '(Sin nombre)';
        } elseif ($this->idcampania) {
            return $this->campania->titulo ?? '(Sin nombre)';
        }
        return '(No asignado)';
    }

    public function getAsignartarea()
    {
        return $this->hasOne(Asignacion::class, ['idasignartarea' => 'idasignartarea']);
    }


    public function getNombreTarea()
    {
        return $this->tarea->nombre ?? '(Sin tarea)';
    }


    /**
     * @inheritdoc
     */






}
