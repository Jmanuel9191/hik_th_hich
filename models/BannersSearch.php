<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/** Filtros del listado de banners (fila de filtros del GridView). */
class BannersSearch extends Model
{
    public $titulo;
    public $situacion;

    public function rules()
    {
        return [
            [['titulo'], 'string', 'max' => 150],
            [['situacion'], 'in', 'range' => ['activo', 'inactivo', 'programado', 'vencido']],
        ];
    }

    public function search($params)
    {
        $query = Banners::find()->orderBy(['orden' => SORT_ASC, 'idbanner' => SORT_ASC]);
        $dp = new ActiveDataProvider(['query' => $query, 'sort' => false, 'pagination' => ['pageSize' => 20]]);
        if (!$this->load($params) || !$this->validate()) {
            return $dp;
        }
        $query->andFilterWhere(['or', ['like', 'titulo', $this->titulo], ['like', 'texto', $this->titulo]]);
        $hoy = date('Y-m-d');
        switch ($this->situacion) {
            case 'inactivo':
                $query->andWhere(['<>', 'estado', '1']);
                break;
            case 'programado':
                $query->andWhere(['estado' => '1'])->andWhere(['>', 'fecha_inicio', $hoy]);
                break;
            case 'vencido':
                $query->andWhere(['estado' => '1'])->andWhere(['<', 'fecha_fin', $hoy]);
                break;
            case 'activo':
                $query->andWhere(['estado' => '1'])
                    ->andWhere(['or', ['fecha_inicio' => null], ['<=', 'fecha_inicio', $hoy]])
                    ->andWhere(['or', ['fecha_fin' => null], ['>=', 'fecha_fin', $hoy]]);
                break;
        }
        return $dp;
    }
}
