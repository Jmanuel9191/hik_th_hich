<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TareasSearch represents the model behind the search form about `app\models\Recursos`.
 */
class RecursoSearch extends Recursos
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idrecurso',  'idasignartarea', 'idusuario'], 'integer'],
            [['titulo', 'descripcion', 'estado', 'fecha_registro', 'fechaentrega'], 'safe'],

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
        $query = Recursos::find();

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
            'idrecurso' => $this->idrecurso,
            'idasignartarea' => $this->idasignartarea,
            'idusuario' => $this->idusuario,
            'fecha_registro' => $this->fecha_registro,
            'fechaentrega' => $this->fechaentrega,
        ]);

        $query->andFilterWhere(['like', 'titulo', $this->titulo])
            ->andFilterWhere(['like', 'descripcion', $this->descripcion])
            ->andFilterWhere(['like', 'estado', $this->estado]);

        return $dataProvider;
    }
}
