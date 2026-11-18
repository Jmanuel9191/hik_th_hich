<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * HistorialRecursoSearch representa el modelo de búsqueda para `app\models\HistoriaRecursos`.
 */
class HistorialRecursoSearch extends HistoriaRecursos
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tipo', 'idhistorial', 'idusuario', 'idrecurso'], 'integer'],
            [['comentario', 'archivo', 'fecha_registro'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios(); // sin modificaciones
    }

    /**
     * Crea el data provider con la búsqueda aplicada.
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = HistoriaRecursos::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        // Ordenar por fecha descendente por defecto
        $dataProvider->sort = [
            'defaultOrder' => ['fecha_registro' => SORT_ASC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filtros
        $query->andFilterWhere([
            'idhistorial' => $this->idhistorial,
            'idusuario' => $this->idusuario,
            'idrecurso' => $this->idrecurso,
            'fecha_registro' => $this->fecha_registro,
            'tipo' => $this->tipo,
        ]);

        $query->andFilterWhere(['like', 'comentario', $this->comentario])
            ->andFilterWhere(['like', 'archivo', $this->archivo]);

        return $dataProvider;
    }
}
