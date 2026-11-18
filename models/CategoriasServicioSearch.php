<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\CategoriasServicio;

class CategoriasServicioSearch extends CategoriasServicio
{
    public function rules()
    {
        return [
            [['idcategoria', 'estado', 'idpadre'], 'integer'],
            [['nombre', 'alias', 'fecha_registro'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = CategoriasServicio::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'idcategoria' => $this->idcategoria,
            'estado' => $this->estado,
            'idpadre' => $this->idpadre,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'alias', $this->alias])
            ->andFilterWhere(['like', 'fecha_registro', $this->fecha_registro]);

        return $dataProvider;
    }
}
