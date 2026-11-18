<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/** Filtros del listado del equipo (fila de filtros del GridView). */
class EquipoSearch extends Model
{
    public $nombre;
    public $puesto;
    public $estado;

    public function rules()
    {
        return [
            [['nombre', 'puesto'], 'string', 'max' => 150],
            [['estado'], 'in', 'range' => ['0', '1']],
        ];
    }

    public function search($params)
    {
        $query = Equipo::find()->orderBy(['orden' => SORT_ASC, 'idmiembro' => SORT_ASC]);
        $dp = new ActiveDataProvider(['query' => $query, 'sort' => false, 'pagination' => ['pageSize' => 20]]);
        if (!$this->load($params) || !$this->validate()) {
            return $dp;
        }
        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'puesto', $this->puesto])
            ->andFilterWhere(['estado' => $this->estado]);
        return $dp;
    }
}
