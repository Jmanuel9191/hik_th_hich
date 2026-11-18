<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ParametrosSearch extends Parametros
{
    public function rules()
    {
        return [
            [['nombre', 'unidad'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Parametros::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['nombre' => SORT_ASC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'unidad', $this->unidad]);

        return $dataProvider;
    }
}