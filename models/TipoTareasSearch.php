<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Sucursal;

class TipoTareasSearch extends TipoTareas
{
    public function rules()
    {
        return [
            [['idtipo_tarea', 'estado'], 'integer'],
            [['nombre', 'fecha_registro'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = TipoTareas::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'idtipo_tarea' => $this->idtipo_tarea,
            'estado' => $this->estado,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'fecha_registro', $this->fecha_registro]);

        return $dataProvider;
    }
}
