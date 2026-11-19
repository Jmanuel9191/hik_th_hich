<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Versión del acuerdo de confidencialidad. No se editan: cada cambio de texto es una versión nueva
 * (la vigente es la de número más alto) y los inversores la vuelven a aceptar.
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
class NdaVersion extends ActiveRecord
{
    public static function tableName()
    {
        return 'nda_version';
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
        return ['titulo' => 'Título', 'texto' => 'Texto del acuerdo', 'nota' => 'Qué cambió (interno, opcional)', 'numero' => 'Versión'];
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
