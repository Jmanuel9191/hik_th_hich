<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/** Filtros del listado de proyectos (fila de filtros). El alcance por área lo pone ProyectoService. */
class ProyectoSearch extends Model
{
    public $titulo;
    public $idecosistema;
    public $tipo;
    public $estado;
    public $etapa;

    public function rules()
    {
        return [
            [['titulo'], 'string', 'max' => 160],
            [['idecosistema'], 'integer'],
            [['tipo'], 'in', 'range' => array_keys(Proyecto::TIPOS)],
            [['estado'], 'in', 'range' => array_keys(Proyecto::ESTADOS)],
            [['etapa'], 'in', 'range' => array_keys(Proyecto::ETAPAS)],
        ];
    }

    /** @param array|null $idsEcosistemas null = todos (si no, solo esos) */
    public function search($params, $idsEcosistemas = null)
    {
        $query = Proyecto::find()->with(['ecosistema', 'moneda', 'gerente'])
            ->orderBy(['fecha_actualizacion' => SORT_DESC, 'idproyecto' => SORT_DESC]);
        if ($idsEcosistemas !== null) {
            $query->andWhere(['idecosistema' => $idsEcosistemas ?: [0]]);
        }
        $dp = new ActiveDataProvider(['query' => $query, 'sort' => false, 'pagination' => ['pageSize' => 20]]);
        if (!$this->load($params) || !$this->validate()) {
            return $dp;
        }
        $query->andFilterWhere(['or', ['like', 'titulo', $this->titulo], ['like', 'codigo', $this->titulo]])
            ->andFilterWhere(['idecosistema' => $this->idecosistema, 'tipo' => $this->tipo, 'estado' => $this->estado, 'etapa' => $this->etapa]);
        return $dp;
    }
}
