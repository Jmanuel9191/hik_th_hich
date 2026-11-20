<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/** Filtros del listado de noticias del panel. */
class NoticiaSearch extends Model
{
    public $titulo;
    public $idcategoria;
    public $estado;
    public $destacado;

    public function rules()
    {
        return [
            [['titulo'], 'string', 'max' => 200],
            [['idcategoria'], 'integer'],
            [['estado', 'destacado'], 'in', 'range' => ['0', '1']],
        ];
    }

    public function search($params)
    {
        $query = Noticia::find()->with('categoria')->orderBy(['fecha' => SORT_DESC, 'idnoticia' => SORT_DESC]);
        $dp = new ActiveDataProvider(['query' => $query, 'sort' => false, 'pagination' => ['pageSize' => 20]]);
        if (!$this->load($params) || !$this->validate()) {
            return $dp;
        }
        $query->andFilterWhere(['like', 'titulo', $this->titulo])
            ->andFilterWhere(['idcategoria' => $this->idcategoria])
            ->andFilterWhere(['estado' => $this->estado])
            ->andFilterWhere(['destacado' => $this->destacado]);
        return $dp;
    }
}
