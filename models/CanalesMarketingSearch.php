<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\CanalesMarketing;

class CanalesMarketingSearch extends CanalesMarketing
{
    public function rules()
    {
        return [
            [['idcanal', 'estado'], 'integer'],
            [['nombre', 'descripcion', 'fecha_registro'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios(); // No cambiar
    }

    public function search($params)
    {
        $query = CanalesMarketing::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['idcanal' => $this->idcanal]);
        $query->andFilterWhere(['estado' => $this->estado]);
        $query->andFilterWhere(['like', 'nombre', $this->nombre]);
        $query->andFilterWhere(['like', 'descripcion', $this->descripcion]);

        return $dataProvider;
    }
}
