<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * HistorialTareasSearch representa el modelo de búsqueda para `app\models\HistorialTareas`.
 */
class HistorialUsuariosSearch extends HistorialUsuarios
{
    public function rules()
    {
        return [
            [['accion', 'descripcion'], 'safe'],
            [['idusuario_actor', 'idusuario_afectado', 'idhistorial'], 'integer'],
            [['fecha'], 'safe'], // necesario para el filtro de rango
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = HistorialUsuarios::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        // Orden por fecha descendente por defecto
        $dataProvider->sort = [
            'defaultOrder' => ['fecha' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filtros exactos
        $query->andFilterWhere([
            'idhistorial' => $this->idhistorial,
            'idusuario_afectado' => $this->idusuario_afectado,
            'idusuario_actor' => $this->idusuario_actor,
        ]);

        // Filtro de texto
        $query->andFilterWhere(['accion' => $this->accion]); // Filtro exacto
        $query->andFilterWhere(['like', 'descripcion', $this->descripcion]);

        // Filtro por rango de fechas
        if (!empty($this->fecha) && strpos($this->fecha, ' - ') !== false) {
            [$fechaInicio, $fechaFin] = explode(' - ', $this->fecha);
            $query->andFilterWhere(['between', 'fecha', "$fechaInicio 00:00:00", "$fechaFin 23:59:59"]);
        }

        return $dataProvider;
    }
}
