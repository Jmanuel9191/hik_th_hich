<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Sucursal;

class SucursalSearch extends Sucursal
{
    public function rules()
    {
        return [
            [['idsucursal', 'estado'], 'integer'],
            [['nombre', 'ciudad', 'pais', 'fecha_registro'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = Sucursal::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'idsucursal' => $this->idsucursal,
            'estado' => $this->estado,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'ciudad', $this->ciudad])
            ->andFilterWhere(['like', 'pais', $this->pais])
            ->andFilterWhere(['like', 'fecha_registro', $this->fecha_registro]);

        return $dataProvider;
    }
}
