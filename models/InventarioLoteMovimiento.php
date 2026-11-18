<?php

namespace app\models;

use Yii;

class InventarioLoteMovimiento extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'inventario_lote_movimiento';
    }

    public function rules()
    {
        return [
            [['idinventario', 'idlote', 'cantidad'], 'required'],
            [['idinventario', 'idlote', 'cantidad'], 'integer'],
        ];
    }

    public function getLote()
    {
        return $this->hasOne(InventarioLote::class, ['idlote' => 'idlote']);
    }

    // Guarda el detalle de que lotes se usaron en una salida
    public static function registrar($idinventario, $detalle)
    {
        foreach ($detalle as $d) {
            $reg = new self();
            $reg->idinventario = $idinventario;
            $reg->idlote = $d['idlote'];
            $reg->cantidad = $d['cantidad'];
            $reg->save(false);
        }
    }

    // Texto tipo "L004: 10, L001: 10" para mostrar en el historial
    public static function getDetalleTexto($idinventario)
    {
        $registros = self::find()->where(['idinventario' => $idinventario])->all();
        if (empty($registros)) return '-';

        $partes = [];
        foreach ($registros as $r) {
            $codigo = $r->lote ? $r->lote->codigo_lote : '?';
            $partes[] = $codigo . ': ' . $r->cantidad;
        }
        return implode(', ', $partes);
    }
}