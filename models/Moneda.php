<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Moneda de los proyectos (Configuración → Monedas).
 *
 * @property int $idmoneda
 * @property string $codigo   ISO de 3 letras: USD, BOB, EUR…
 * @property string $simbolo
 * @property string $nombre
 * @property string $estado
 * @property int $orden
 */
class Moneda extends ActiveRecord
{
    public static function tableName()
    {
        return 'moneda';
    }

    public function rules()
    {
        return [
            [['codigo', 'simbolo', 'nombre'], 'filter', 'filter' => fn($v) => trim((string) $v)],
            [['codigo'], 'filter', 'filter' => 'mb_strtoupper'],
            [['codigo', 'simbolo', 'nombre'], 'required'],
            [['codigo'], 'match', 'pattern' => '/^[A-Z]{3}$/', 'message' => 'Tres letras, como USD o BOB.'],
            [['codigo'], 'unique', 'message' => 'Esa moneda ya existe.'],
            [['simbolo'], 'string', 'max' => 6],
            [['nombre'], 'string', 'max' => 60],
        ];
    }

    public function attributeLabels()
    {
        return ['codigo' => 'Código', 'simbolo' => 'Símbolo', 'nombre' => 'Nombre', 'estado' => 'Estado', 'orden' => 'Orden'];
    }

    /** id => "USD · Dólar estadounidense" (activas, más la actual aunque esté inactiva). */
    public static function lista($actual = null)
    {
        $q = static::find()->where(['estado' => '1'])->orderBy(['orden' => SORT_ASC, 'codigo' => SORT_ASC]);
        if ($actual) {
            $q->orWhere(['idmoneda' => $actual]);
        }
        $lista = [];
        foreach ($q->all() as $m) {
            $lista[$m->idmoneda] = $m->codigo . ' · ' . $m->nombre . ($m->estado !== '1' ? ' (inactiva)' : '');
        }
        return $lista;
    }
}
