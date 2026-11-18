<?php

namespace app\models;
use yii;

/**
 * This is the model class for table "recursos".
 *
 * @property integer $idrecurso
 * @property string $titulo
 * @property string $descripcion
 * @property string $fechaentrega
 * @property integer $idasignartarea
 * @property integer $idusuario
 * @property string $estado
 * @property string $fecha_registro
 */
class Recursos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'recursos';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['titulo', 'descripcion', 'fechaentrega' ], 'required'],
            ['fechaentrega', 'validateFechaTarea'],
            [['descripcion'], 'string'],
            [['idasignartarea', 'idusuario', 'idcanal'], 'integer'],
            [['fecha_registro', 'fechaentrega'], 'safe'],
            [['titulo'], 'string', 'max' => 250],
            [['estado'], 'string', 'max' => 1],
        ];
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'idrecurso' => 'ID',
            'titulo' => 'Nombre del Recurso',
            'descripcion' => 'Descripcion del recurso',
            'idasignartarea' => 'Tarea',
            'idusuario' => 'Quien creo',
            'estado' => 'Estado',
            'fechaentrega' => 'Fecha limite de entrega',
            'idcanal' => 'Canales de marketing',

        ];
    }


    public function validateFechaTarea($attribute, $params)
    {
        $idAsignacion = Yii::$app->request->get('idasignartarea');
        $asignacion = \app\models\Asignacion::findOne($idAsignacion);

        if ($asignacion) {
            if ($this->$attribute < $asignacion->fechainicio) {
                $this->addError($attribute, 'La fecha no puede ser menor a la fecha de inicio de la tarea.');
            } elseif ($this->$attribute > $asignacion->fechafin) {
                $this->addError($attribute, 'La fecha no puede ser mayor a la fecha límite de la tarea.');
            }
        }
    }

    public function tieneHistorialtareas()
    {
        return Recursos::find()
            ->where(['idasignartarea' => $this->idasignartarea])
            ->exists();
    }
    public function tieneHistorial()
    {
        return HistoriaRecursos::find()
            ->where(['idrecurso' => $this->idrecurso])
            ->exists();
    }
    public static function getSelectRecursos()
    {
        $result =[];
        $temp = Recursos::find()->where(['estado'=> '1'])->all();
        foreach ($temp as $item) {
            $result[$item->idrecurso]="".$item->titulo;
        }
        return $result;
    }
    public function getAsignacion()
    {
        return $this->hasOne(Asignacion::class, ['idasignartarea' => 'idasignartarea']);
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario']);
    }

    public function getHistorialRecursos()
    {
        return $this->hasMany(HistoriaRecursos::class, ['idrecurso' => 'idrecurso']);
    }

    public function getFechaAprobacionCliente()
    {
        return $this->getHistorialRecursos()
            ->andWhere(['tipo' => 2])
            ->orderBy(['fecha_registro' => SORT_DESC])
            ->limit(1)
            ->select('fecha_registro')
            ->scalar(); // devuelve solo la fecha

    }


    public function getCanalesmarketing()
    {
        return $this->hasOne(CanalesMarketing::class, ['idcanal' => 'idcanal']);
    }

}
