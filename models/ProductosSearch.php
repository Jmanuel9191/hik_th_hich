<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ProductosSearch extends Productos
{
    public function rules()
    {
        return [
            [['idproducto', 'idcategoria', 'idusuario', 'posicion', 'visitas', 'preciocaja'], 'integer'],
            [['titulo', 'resumen', 'descripcion', 'titulo2', 'tags', 'estado', 'destacado', 'fecha_registro', 'fecha_vencimiento', 'fecha_vencimientof'], 'safe'],
            [['precio', 'preciooferta', 'preciocajaventa'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

   public function search($params, $query = null, $aliasCategoria = 'productosg', $excluirAlias = false)
{
    if ($query === null) {
        $query = Productos::find()
            ->select(['productos.*'])
            ->innerJoinWith('categoria');

        if ($excluirAlias) {
            $query->andWhere(['<>', 'categorias_servicio.alias', $aliasCategoria]);
        } else {
            $query->andWhere(['categorias_servicio.alias' => $aliasCategoria]);
        }
    }

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'pagination' => ['pageSize' => 20],
    ]);

    $this->load($params);

    if (!$this->validate()) {
        return $dataProvider;
    }

    $query->andFilterWhere(['productos.idproducto' => $this->idproducto]);
    $query->andFilterWhere(['productos.idcategoria' => $this->idcategoria]);
    $query->andFilterWhere(['productos.idusuario' => $this->idusuario]);
    $query->andFilterWhere(['productos.precio' => $this->precio]);
    $query->andFilterWhere(['productos.preciooferta' => $this->preciooferta]);
    $query->andFilterWhere(['productos.preciocaja' => $this->preciocaja]);
    $query->andFilterWhere(['productos.preciocajaventa' => $this->preciocajaventa]);
    $query->andFilterWhere(['productos.fecha_registro' => $this->fecha_registro]);
    $query->andFilterWhere(['productos.fecha_vencimiento' => $this->fecha_vencimiento]);
    $query->andFilterWhere(['productos.fecha_vencimientof' => $this->fecha_vencimientof]);

    if ($this->estado !== null && $this->estado !== '') {
        $query->andWhere(['productos.estado' => $this->estado]);
    }
    if ($this->destacado !== null && $this->destacado !== '') {
        $query->andWhere(['productos.destacado' => $this->destacado]);
    }

    $query->andFilterWhere(['like', 'productos.titulo', $this->titulo])
        ->andFilterWhere(['like', 'productos.titulo2', $this->titulo2])
        ->andFilterWhere(['like', 'productos.resumen', $this->resumen])
        ->andFilterWhere(['like', 'productos.descripcion', $this->descripcion]);

    return $dataProvider;
}
}