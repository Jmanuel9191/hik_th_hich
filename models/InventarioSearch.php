<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * InventarioSearch represents the model behind the search form about `app\models\Inventario`.
 */
class InventarioSearch extends Inventario
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idinventario', 'idproducto', 'estado', 'idtalla'], 'integer'],
            [['movimiento', 'saldo'], 'number'],
            [['fecha_registro', 'detalle'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Inventario::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'idinventario' => $this->idinventario,
            'idproducto' => $this->idproducto,
            'movimiento' => $this->movimiento,
            'saldo' => $this->saldo,
            'fecha_registro' => $this->fecha_registro,
            'estado' => $this->estado,
            'idtalla' => $this->idtalla,
        ]);

        $query->andFilterWhere(['like', 'detalle', $this->detalle]);

        return $dataProvider;
    }
}
