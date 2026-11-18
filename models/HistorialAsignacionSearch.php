<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * HistorialTareasSearch representa el modelo de búsqueda para `app\models\HistorialTareas`.
 */
class HistorialAsignacionSearch extends HistorialAsignaciones
{
    public $idasignartarea;
    public $idusuario;
    public $idproyecto;
    public $idtarea;
    public $idcampania;
    public $idusuario_asignado;
    public $fecha;

    public function rules()
    {
        return [
            [['accion', 'descripcion'], 'safe'],
            [['idasignartarea', 'idusuario', 'idproyecto', 'idtarea', 'idcampania', 'idconsultor'], 'integer'],
            [['fecha'], 'safe'], // necesario para el filtro de rango
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = HistorialAsignaciones::find()
            ->alias('h')
            ->joinWith(['asignacion a']); // 👈 usa el nombre correcto del método de relación



        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['fecha' => SORT_DESC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }



        // Filtros exactos
        $query->andFilterWhere([
            'idhistorial' => $this->idhistorial,
            'idusuario' => $this->idusuario,
            'idasignartarea' => $this->idasignartarea,
            'idconsultor' => $this->idconsultor,
            'idcampania' => $this->idcampania,
            'idtarea' => $this->idtarea,
        ]);

        // Filtro de texto
        $query->andFilterWhere(['accion' => $this->accion]);
        $query->andFilterWhere(['like', 'descripcion', $this->descripcion]);

        if (!empty($this->idproyecto)) {
            $query->andWhere(['a.idproyecto' => $this->idproyecto]); // 👈 ya funciona con el alias correcto
        }


        // Rango de fechas
        if (!empty($this->fecha) && strpos($this->fecha, ' - ') !== false) {
            [$fechaInicio, $fechaFin] = explode(' - ', $this->fecha);
            $query->andFilterWhere(['between', 'fecha', "$fechaInicio 00:00:00", "$fechaFin 23:59:59"]);
        }

        return $dataProvider;
    }

}
