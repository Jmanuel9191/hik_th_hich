<?php
namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class OrdenesSearch extends Ordenes


{

 public $productos_ids;

    // Filtros independientes para el cuadre de caja: '1' = pagó con
    // ese método (aunque sea parte de un pago mixto), '0' = no.
    public $con_efectivo;
    public $con_qr;

    public function rules()
    {
        return [
            [['paciente_nombre', 'solicitante_nombre', 'numero_referencia','tipo_pago', 'estado_pago', 'tipo_orden', 'fecha_registro'], 'safe'],
            [['idorden', 'iddoctor', 'idusuario_registro'], 'integer'],
            [['monto_total', 'monto_pagado'], 'number'],
            [['productos_ids'], 'safe'], 
            [['con_efectivo', 'con_qr'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

public function search($params, $soloOrdenes = false)
{
    $query = Ordenes::find()->with(['doctor', 'usuarioRegistro', 'detalles.producto']); // <-- eager load agregado

        if ($soloOrdenes) {
            $query->andWhere(['tipo_orden' => 'orden']);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 30],
            'sort' => ['defaultOrder' => ['idorden' => SORT_DESC]],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['idorden' => $this->idorden])
              ->andFilterWhere(['iddoctor' => $this->iddoctor])
              ->andFilterWhere(['estado_pago' => $this->estado_pago])
              ->andFilterWhere(['tipo_orden' => $this->tipo_orden]);
        // NOTA: el filtro por 'tipo_pago' se sacó de acá porque ya no
        // tiene un control visible en index.php (se reemplazó por los
        // filtros independientes con_efectivo / con_qr, más abajo).
        // Dejarlo activo sin un dropdown para limpiarlo hacía que
        // quedara "pegado" en la URL sin forma de sacarlo desde la UI.

        $query->andFilterWhere(['like', 'paciente_nombre', $this->paciente_nombre])
              ->andFilterWhere(['like', 'numero_referencia', $this->numero_referencia])
              ->andFilterWhere(['like', 'solicitante_nombre', $this->solicitante_nombre]);

        if (!empty($this->fecha_registro)) {
            $query->andWhere(['DATE(fecha_registro)' => $this->fecha_registro]);
        }

        if ($this->estado_pago === 'cotizacion') {
    // Trae los que tienen estado_pago vacío o null
    $query->andWhere(['or', ['estado_pago' => null], ['estado_pago' => '']]);
} elseif (!empty($this->estado_pago)) {
    $query->andWhere(['estado_pago' => $this->estado_pago]);
}


        // ---- NUEVO: filtro por tipos de examen (1 o varios) ----
        if (!empty($this->productos_ids)) {
            $query->andWhere([
                'idorden' => OrdenDetalle::find()
                    ->select('idorden')
                    ->where(['idproducto' => $this->productos_ids]) // OR: cualquiera de los seleccionados
                    ->distinct(),
            ]);
        }

        // ---- NUEVO: filtro de cuadre de caja — quién pagó con cada
        // método, sin importar si fue puro o mixto ----
        if ($this->con_efectivo !== null && $this->con_efectivo !== '') {
            if ($this->con_efectivo == '1') {
                $query->andWhere(['>', 'monto_efectivo', 0]);
            } else {
                $query->andWhere(['or', ['monto_efectivo' => null], ['monto_efectivo' => 0]]);
            }
        }

        if ($this->con_qr !== null && $this->con_qr !== '') {
            if ($this->con_qr == '1') {
                $query->andWhere(['>', 'monto_qr', 0]);
            } else {
                $query->andWhere(['or', ['monto_qr' => null], ['monto_qr' => 0]]);
            }
        }

        return $dataProvider;
}
}