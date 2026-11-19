<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Filtros del listado de pagos de inversiones. El gerente solo ve los de los proyectos de sus áreas.
 */
class InversionSearch extends Model
{
    public $texto;
    public $idproyecto;
    public $tipo_pago;
    public $idmoneda;
    public $estado;
    public $desde;
    public $hasta;

    public function rules()
    {
        return [
            [['texto'], 'string', 'max' => 100],
            [['idproyecto', 'idmoneda'], 'integer'],
            [['tipo_pago'], 'in', 'range' => array_keys(InversionPago::TIPOS_PAGO)],
            [['estado'], 'in', 'range' => array_keys(InversionPago::ESTADOS)],
            [['desde', 'hasta'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @param array|null $idsEcosistemas null = todos
     * @param array $fijo condiciones fijas (p.ej. ['p.idproyecto' => 3])
     */
    public function search($params, $idsEcosistemas = null, array $fijo = [])
    {
        $q = $this->consulta($idsEcosistemas)->andWhere($fijo)->orderBy(['ip.fecha_pago' => SORT_DESC, 'ip.idpago' => SORT_DESC]);
        $dp = new ActiveDataProvider(['query' => $q, 'sort' => false, 'pagination' => ['pageSize' => 25]]);
        if ($this->load($params) && $this->validate()) {
            $this->filtrar($q);
        }
        return $dp;
    }

    public function consulta($idsEcosistemas = null)
    {
        $q = InversionPago::find()->alias('ip')
            ->innerJoinWith(['proyecto p', 'inversor u'], false)
            ->with(['proyecto.moneda', 'inversor', 'fichaInversor', 'moneda', 'monedaProyecto', 'registradoPor', 'anuladoPor']);
        if ($idsEcosistemas !== null) {
            $q->andWhere(['p.idecosistema' => $idsEcosistemas ?: [0]]);
        }
        return $q;
    }

    public function filtrar($q)
    {
        if ($this->texto !== null && $this->texto !== '') {
            $q->leftJoin('inversor i', 'i.idusuario = ip.idusuario')->andWhere(['or',
                ['like', 'u.nombrecompleto', $this->texto], ['like', 'u.email', $this->texto], ['like', 'i.razon_social', $this->texto],
                ['like', 'ip.referencia', $this->texto]]);
        }
        $q->andFilterWhere(['ip.idproyecto' => $this->idproyecto, 'ip.tipo_pago' => $this->tipo_pago, 'ip.idmoneda' => $this->idmoneda, 'ip.estado' => $this->estado]);
        $q->andFilterWhere(['>=', 'ip.fecha_pago', $this->desde])->andFilterWhere(['<=', 'ip.fecha_pago', $this->hasta]);
        return $q;
    }
}
