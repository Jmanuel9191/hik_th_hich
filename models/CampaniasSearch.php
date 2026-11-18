<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class CampaniasSearch extends Campanias
{
    public function rules()
    {
        return [
            [['idcampania', 'idcliente', 'idgestor'], 'integer'],
            [['titulo', 'descripcion', 'fecha_inicio', 'finalizado' , 'fecha_fin', 'fecha_registro', 'fechafinalizacion', 'estado', 'prioridad', 'motivobloqueo', 'motivodefinal'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $query = null)
    {
        if ($query === null) {
            $query = Campanias::find();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);

        // Guarda los filtros enviados para depurar
        file_put_contents(\Yii::getAlias('@runtime') . '/debug_valores.txt', print_r($this->attributes, true));

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['idcampania' => $this->idcampania]);
        $query->andFilterWhere(['idcliente' => $this->idcliente]);
        $query->andFilterWhere(['idgestor' => $this->idgestor]);
        $query->andFilterWhere(['fecha_inicio' => $this->fecha_inicio]);
        $query->andFilterWhere(['fecha_fin' => $this->fecha_fin]);
        $query->andFilterWhere(['fechafinalizacion' => $this->fechafinalizacion]);

        // importante: prioridad como string con LIKE o exacto
        $query->andFilterWhere(['like', 'prioridad', $this->prioridad]);

        // Estado: considerar campo vacío como bloqueado
        if ($this->estado !== null && $this->estado !== '') {
            $query->andWhere(['estado' => $this->estado]);
        }
        if ($this->finalizado !== null && $this->finalizado !== '') {
            $query->andWhere(['finalizado' => $this->finalizado]);
        }

        // Filtros tipo LIKE
        $query->andFilterWhere(['like', 'titulo', $this->titulo])
            ->andFilterWhere(['like', 'descripcion', $this->descripcion])
            ->andFilterWhere(['like', 'motivobloqueo', $this->motivobloqueo])
            ->andFilterWhere(['like', 'motivodefinal', $this->motivodefinal]);

        // Guardar SQL para depuración
        file_put_contents(\Yii::getAlias('@runtime') . '/debug_sql.txt', $query->createCommand()->getRawSql());

        return $dataProvider;
    }
}
