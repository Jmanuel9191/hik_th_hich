<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Sucursal;

class RolesSearch extends Roles
{
    public function rules()
    {
        return [
            [['estado', 'idrol'], 'integer'],
            [['nombre', 'alias', 'fecha_registro'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = Roles::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'idrol' => $this->idrol,
            'estado' => $this->estado,
            'alias' => $this->alias,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'fecha_registro', $this->fecha_registro]);

        return $dataProvider;
    }
}
