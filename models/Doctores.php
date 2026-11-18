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
class Doctores extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'doctores';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nombre',], 'required'],
              [['fecha_registro', 'email','telefono'], 'safe'],
            [['descripcion'], 'string'],
            ['nombre', 'validarTituloPorModulo'],
            [['estado'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'iddoctor ' => 'ID',
            'nombre' => 'Nombre Completo',
            'descripcion' => 'Algun detalle',
              'telefono' => 'Telefono o celular',
                'email' => 'Email',
            'estado' => 'Estado',
            'motivobloqueo' => 'Motivo del bloqueo',
        ];
    }


    public function validarTituloPorModulo($attribute, $params)
    {
        $iddoctor = $this->getPrimaryKey();

        $query = Doctores::find()
            ->where(['nombre' => $this->nombre]);

        if (!$this->isNewRecord && $iddoctor) {
            $query->andWhere(['<>', 'iddoctor', $iddoctor]);
        }

        if ($query->exists()) {
            $this->addError($attribute, 'Ya existe una tarea con este título en el mismo módulo.');
        }
    }


    public function getHistorialTareas()
    {
        return $this->hasMany(HistorialTareas::className(), ['iddoctor' => 'iddoctor'])
            ->orderBy(['fecha' => SORT_DESC]); // opcional para ver lo último primero
    }

    public static function getSelectTareascampanias()
    {

        $result = [];
        $temp = Doctores::find()
            ->where(['estado' => '1', 'modulo' => '23'])
            ->with('tipo') // importante para evitar consultas adicionales
            ->all();

        foreach ($temp as $item) {
            $nombreTipo = $item->tipo->nombre ?? '(sin tipo)';
            $result[$item->idtarea] = "{$item->titulo} - {$nombreTipo}";
        }

        return $result;
    }

    

    public function getTipo()
    {
        return $this->hasOne(TipoTareas::class, ['idtipo_tarea' => 'idtipo_tarea']);
    }

    


    public function getUsuarioCreador()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_creador']);
    }

    public function getTipoTarea()
    {
        return $this->hasOne(TipoTareas::class, ['idtipo_tarea' => 'idtipo_tarea']);
    }


    public static function getEstadosTarea()
    {
        return [
            '1' => 'Activo',
            '0'  => 'Bloqueado', // clave vacía como usás en tu BD
        ];
    }
    public static function obtenerTextoEstado($estado)
    {
        $estados = [
            '1' => 'Activo',
            '0'  => 'Bloqueado', // Importante: clave vacía para bloqueados
        ];

        return $estados[$estado] ?? 'Desconocido';
    }
    /*
     * funcion para sacar menu select2
     */


    /*
     * funcion para obtener categorias de primer nivel por alias
     */
  
}
