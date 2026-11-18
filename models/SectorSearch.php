<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Sector;

class SectorSearch extends Sector
{
    public function rules()
    {
        return [
            [['idsector', 'estado'], 'integer'],
            [['nombre', 'fecha_registro'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = Sector::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'idsector' => $this->idsector,
            'estado' => $this->estado,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'fecha_registro', $this->fecha_registro]);

        return $dataProvider;
    }
}
