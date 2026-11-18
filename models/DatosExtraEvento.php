<?php
namespace app\models;

use Yii;

class Ordenes extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'ordenes';
    }

    public function rules()
    {
        return [
            [['paciente_nombre', 'tipo_pago', 'tipo_orden'], 'required'],
            [['paciente_nombre', 'solicitante_nombre'], 'string', 'max' => 200],
            [['iddoctor', 'idusuario_registro'], 'integer'],
            [['monto_total', 'monto_pagado', 'saldo'], 'number', 'min' => 0],
            [['tipo_pago'], 'in', 'range' => ['efectivo', 'qr']],
            [['estado_pago'], 'in', 'range' => ['pagado', 'pendiente']],
            [['tipo_orden'], 'in', 'range' => ['orden', 'cotizacion']],
            [['solicitante_nombre', 'iddoctor'], 'default', 'value' => null],
            [['fecha_registro'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idorden'             => 'N° Orden',
            'paciente_nombre'     => 'Paciente',
            'solicitante_nombre'  => 'Solicitante',
            'iddoctor'            => 'Doctor',
            'idusuario_registro'  => 'Registrado por',
            'monto_total'         => 'Total (Bs)',
            'monto_pagado'        => 'Monto pagado (Bs)',
            'saldo'               => 'Saldo (Bs)',
            'tipo_pago'           => 'Tipo de pago',
            'estado_pago'         => 'Estado',
            'tipo_orden'          => 'Tipo',
            'fecha_registro'      => 'Fecha',
        ];
    }

    // Relaciones
    public function getDoctor()
    {
        return $this->hasOne(Doctores::class, ['iddoctor' => 'iddoctor']);
    }

    public function getUsuarioRegistro()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_registro']);
    }

    public function getDetalles()
    {
        return $this->hasMany(OrdenDetalle::class, ['idorden' => 'idorden']);
    }

    // Helpers
    public static function getTiposPago()
    {
        return ['efectivo' => 'Efectivo', 'qr' => 'QR'];
    }

    public static function getEstadosPago()
    {
        return ['pagado' => 'Pagado', 'pendiente' => 'Pendiente'];
    }

    public static function getTiposOrden()
    {
        return ['orden' => 'Orden', 'cotizacion' => 'Cotización'];
    }

    public function getTipoPagoLabel()
    {
        return self::getTiposPago()[$this->tipo_pago] ?? '—';
    }

    public function getEstadoPagoLabel()
    {
        return self::getEstadosPago()[$this->estado_pago] ?? '—';
    }

    public function getTipoOrdenLabel()
    {
        return self::getTiposOrden()[$this->tipo_orden] ?? '—';
    }

    public function esCotizacion()
    {
        return $this->tipo_orden === 'cotizacion';
    }
}