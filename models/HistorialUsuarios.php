<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "historial_usuarios".
 *
 * @property integer $idcategoria
 * @property string $nombre
 * @property string $descripcion
 * @property string $imagen
 * @property integer $idpadre
 * @property integer $modulo
 * @property string $estado
 */
class HistorialUsuarios extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'historial_usuarios';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idusuario_afectado', 'idusuario_actor', 'accion'], 'required'],
            [['descripcion', 'accion'], 'string'],
            [['fecha'], 'safe'],
            [['idusuario_afectado', 'idusuario_actor'], 'integer'],
            [['ip'], 'string', 'max' => 45],

        ];


    }
    public function attributeLabels()
    {
        return [
            'idhistorial' => 'ID',
            'idusuario_afectado' => 'Usuario afectado',
            'idusuario_actor' => 'Acción realizada por',
            'accion' => 'Acción',
            'descripcion' => 'Descripción',
            'fecha' => 'Fecha',
            'ip' => 'IP',
        ];

    }

    public function getUsuarioAfectado()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_afectado']);
    }

    public function getUsuarioActor()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_actor']);
    }

    /** Guarda la IP de quien hizo la acción, en todos los registros del historial. */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // hasAttribute: por si el código se sube antes de correr la migración que crea la columna.
        if ($insert && $this->hasAttribute('ip') && empty($this->ip) && Yii::$app->request instanceof \yii\web\Request) {
            $this->ip = Yii::$app->request->userIP;
        }
        if ($insert && empty($this->fecha)) {
            $this->fecha = date('Y-m-d H:i:s');
        }
        return true;
    }

    /** Atajo para registrar en el historial. $idActor null = el sistema o el propio usuario sin sesión. */
    public static function registrar($idAfectado, $accion, $descripcion, $idActor = null)
    {
        $h = new static();
        $h->idusuario_afectado = $idAfectado;
        $h->idusuario_actor = $idActor;
        $h->accion = $accion;
        $h->descripcion = $descripcion;
        return $h->save(false);
    }

    /**
     * @inheritdoc
     */






}
