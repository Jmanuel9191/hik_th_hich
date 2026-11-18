<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class AudienciasSearch extends Audiencias
{
    public function rules()
    {
        return [
            [['idaudiencia', 'estado'], 'integer'],
            [['nombre', 'descripcion', 'fecha_registro'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = Audiencias::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'idaudiencia' => $this->idaudiencia,
            'estado' => $this->estado,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'descripcion', $this->descripcion])
            ->andFilterWhere(['like', 'fecha_registro', $this->fecha_registro]);

        return $dataProvider;
    }
}
