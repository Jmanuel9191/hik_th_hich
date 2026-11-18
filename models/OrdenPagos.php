<?php
namespace app\models;

use Yii;

class OrdenPagos extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'orden_pagos';
    }

    public function rules()
    {
        return [
            [['idorden', 'fecha_pago'], 'required'],
            [['idorden', 'idusuario_registro', 'idusuario_edicion'], 'integer'],
            [['monto', 'monto_efectivo', 'monto_qr'], 'number', 'min' => 0],
            [['tipo_pago'], 'in', 'range' => ['efectivo', 'qr', 'mixto']],
            [['fecha_pago', 'fecha_edicion'], 'safe'],
        ];
    }

    public function getOrden()
    {
        return $this->hasOne(Ordenes::class, ['idorden' => 'idorden']);
    }

    public function getUsuarioRegistro()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_registro']);
    }

    public function getUsuarioEdicion()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_edicion']);
    }

    /**
     * Solo el último pago registrado de una orden se puede editar.
     * Si ya entró un pago más nuevo después de este, queda congelado
     * para siempre — así no se puede reescribir el historial viejo,
     * solo corregir el error más reciente.
     */
    public function esElUltimoPagoDeLaOrden()
    {
        $idUltimo = (int)self::find()
            ->where(['idorden' => $this->idorden])
            ->max('idpago');

        return $idUltimo === (int)$this->idpago;
    }

    public function fueEditado()
    {
        return !empty($this->fecha_edicion);
    }

    public function getTipoPagoLabel()
    {
        return Ordenes::getTiposPago()[$this->tipo_pago] ?? '—';
    }
}