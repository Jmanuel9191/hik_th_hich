<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Versión de los términos de uso y la política de privacidad. No se editan: cada cambio es una versión nueva
 * (la vigente es la de número más alto). Cada inversor guarda la versión que aceptó al registrarse (inversor.terminos_version).
 *
 * @property int $idversion
 * @property int $numero
 * @property string $titulo
 * @property string $texto     HTML limpio (HtmlEditor)
 * @property string|null $nota  qué cambió (interno)
 * @property int|null $idusuario
 * @property string|null $ip
 * @property string $fecha_publicacion
 *
 * @property Usuarios|null $usuario
 */
class TerminosVersion extends ActiveRecord
{
    public static function tableName()
    {
        return 'terminos_version';
    }

    public function rules()
    {
        return [
            [['titulo', 'nota'], 'filter', 'filter' => fn($v) => trim((string) $v)],
            [['titulo', 'texto'], 'required'],
            [['titulo'], 'string', 'max' => 200],
            [['nota'], 'string', 'max' => 300],
            [['texto'], 'string', 'max' => 200000],
        ];
    }

    public function attributeLabels()
    {
        return ['titulo' => 'Título', 'texto' => 'Texto', 'nota' => 'Qué cambió (interno, opcional)', 'numero' => 'Versión'];
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario']);
    }

    /** La versión vigente (o null si no hay ninguna). */
    public static function vigente()
    {
        return static::find()->orderBy(['numero' => SORT_DESC])->one();
    }
}
