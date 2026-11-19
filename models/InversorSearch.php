<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/** Filtros del listado de inversores. El alcance por área (gerente) lo pone InversorService. */
class InversorSearch extends Model
{
    public $texto;
    public $tipo;
    public $interes;
    public $monto_desde;
    public $monto_hasta;
    public $nda;

    public function rules()
    {
        return [
            [['texto'], 'string', 'max' => 100],
            [['tipo'], 'in', 'range' => array_keys(Inversor::TIPOS)],
            [['interes'], 'integer'],
            [['monto_desde', 'monto_hasta'], 'number', 'min' => 0],
            [['nda'], 'in', 'range' => ['si', 'no']],
        ];
    }

    /**
     * @param string $pestana pendiente | aprobado | rechazado | sin_verificar | bloqueado
     * @param array|null $idsEcosistemas null = todos
     */
    public function search($params, $pestana, $idsEcosistemas = null, $idCreador = null)
    {
        $q = Inversor::find()->alias('i')->innerJoinWith('usuario u')->with(['intereses', 'areas'])
            ->orderBy(['i.fecha_registro' => SORT_DESC]);
        if ($pestana === 'bloqueado') {
            $q->andWhere(['<>', 'u.estado', '1']);
        } else {
            $q->andWhere(['u.estado' => '1', 'i.estado_solicitud' => $pestana]);
        }
        if ($idsEcosistemas !== null) {
            $ids = $idsEcosistemas ?: [0];
            $condiciones = ['or',
                ['exists', (new \yii\db\Query())->from('inversor_area a')->where('a.idusuario = i.idusuario')->andWhere(['a.idecosistema' => $ids])],
                ['exists', (new \yii\db\Query())->from('inversor_interes n')->where('n.idusuario = i.idusuario')->andWhere(['n.idecosistema' => $ids])],
            ];
            if ($idCreador) {
                $condiciones[] = ['i.idusuario_registro' => (int) $idCreador]; // los que cargó él
            }
            $q->andWhere($condiciones);
        }
        $dp = new ActiveDataProvider(['query' => $q, 'sort' => false, 'pagination' => ['pageSize' => 20]]);
        if (!$this->load($params) || !$this->validate()) {
            return $dp;
        }
        if ($this->texto !== null && $this->texto !== '') {
            $q->andWhere(['or', ['like', 'u.nombrecompleto', $this->texto], ['like', 'u.email', $this->texto], ['like', 'i.razon_social', $this->texto]]);
        }
        $q->andFilterWhere(['i.tipo' => $this->tipo]);
        // Rango que se cruza con el del inversor (su mínimo–máximo)
        if ($this->monto_desde !== null && $this->monto_desde !== '') {
            $q->andWhere(['>=', 'i.monto_maximo', (float) $this->monto_desde]);
        }
        if ($this->monto_hasta !== null && $this->monto_hasta !== '') {
            $q->andWhere(['<=', 'i.monto_minimo', (float) $this->monto_hasta]);
        }
        if ($this->interes) {
            $q->andWhere(['exists', (new \yii\db\Query())->from('inversor_interes n2')->where('n2.idusuario = i.idusuario')->andWhere(['n2.idecosistema' => (int) $this->interes])]);
        }
        if ($this->nda) {
            $vigente = NdaVersion::vigente();
            $sub = (new \yii\db\Query())->from('nda_aceptacion na')->where('na.idusuario = i.idusuario')->andWhere(['na.idversion' => $vigente->idversion ?? 0]);
            $q->andWhere([$this->nda === 'si' ? 'exists' : 'not exists', $sub]);
        }
        return $dp;
    }
}
