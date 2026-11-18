<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * Roles es el modelo para la tabla 'roles'.
 */
class Roles extends ActiveRecord
{
    public $permisos; // ✅ Para el formulario (array de ids de permisos seleccionados)

    public static function tableName()
    {
        return 'roles';
    }

    public function rules()
    {
        return [
            [['nombre', 'alias'], 'required'],
            [['descripcion'], 'string'],
            [['estado'], 'string', 'max' => 1],
            [['nombre'], 'string', 'max' => 100],
            [['alias'], 'string', 'max' => 250],
            [['permisos'], 'safe'], // ✅ Importante: Permitir que el campo permisos pase la validación del formulario
        ];
    }

    public function attributeLabels()
    {
        return [
            'idrol' => 'ID',
            'nombre' => 'Nombre del Rol',
            'alias' => 'Clave Interna',
            'descripcion' => 'Descripción',
            'fecha_registro' => 'Fecha de Registro',
            'estado' => 'Estado',
            'permisos' => 'Permisos asignados', // Opcional, solo si quieres mostrar la etiqueta en el form
        ];
    }

    //  Relación: Un rol tiene muchos permisos a través de la tabla intermedia
    public function getPermisos()
    {
        return $this->hasMany(Permisos::class, ['idpermiso' => 'idpermiso'])
            ->viaTable('rol_permiso', ['idrol' => 'idrol']);
    }

    //  Relación: Acceso directo a la tabla intermedia
    public function getRolPermisos()
    {
        return $this->hasMany(RolPermiso::class, ['idrol' => 'idrol']);
    }

    //  Método para precargar los permisos seleccionados al editar
    public function afterFind()
    {
        parent::afterFind();
        $this->permisos = \yii\helpers\ArrayHelper::getColumn($this->rolPermisos, 'idpermiso');
    }

    //  Método extra opcional que ya tienes
    public static function getIdPorAlias($alias)
    {
        $rol = self::find()->where(['alias' => $alias])->one();
        return $rol ? $rol->idrol : null;
    }
}
