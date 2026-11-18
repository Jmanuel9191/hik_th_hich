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
        [['paciente_nombre', 'tipo_orden'], 'required'],
        [['paciente_nombre', 'solicitante_nombre'], 'string', 'max' => 200],
        [['paciente_edad', 'paciente_telefono'], 'string', 'max' => 20],
        [['iddoctor', 'idusuario_registro'], 'integer'],
        [['monto_total', 'monto_pagado', 'saldo'], 'number', 'min' => 0],
        [['monto_pagado'], 'required', 'when' => fn($m) => $m->tipo_orden !== 'cotizacion',
            'whenClient' => "function(attr, val) { return \$('input[name=\"Ordenes[tipo_orden]\"]:checked').val() !== 'cotizacion'; }"],
        [['tipo_pago'], 'required', 'when' => fn($m) => $m->tipo_orden !== 'cotizacion',
            'whenClient' => "function(attr, val) { return \$('input[name=\"Ordenes[tipo_orden]\"]:checked').val() !== 'cotizacion'; }"],
        [['tipo_pago'], 'in', 'range' => ['efectivo', 'qr']],
        [['estado_pago'], 'in', 'range' => ['pagado', 'pendiente', 'cotizacion']],
        [['tipo_orden'], 'in', 'range' => ['orden', 'cotizacion']],
        [['numero_referencia'], 'string', 'max' => 50],
        [['solicitante_nombre', 'iddoctor', 'paciente_edad', 'paciente_telefono'], 'default', 'value' => null],
        
        [['fecha_registro', 'observaciones', 'conclusion'], 'safe'],
        [['descuento'], 'number', 'min' => 0],
        [['descuento'], 'default', 'value' => 0],
// En attributeLabels():
  
    ];
}

public function attributeLabels()
{
    return [
        'idorden'             => 'N° Orden',
        'paciente_nombre'     => 'Paciente',
        'paciente_edad'       => 'Edad',
        'paciente_telefono'   => 'Teléfono',
        'solicitante_nombre'  => 'Solicitante',
        'iddoctor'            => 'Doctor',
        'idusuario_registro'  => 'Registrado por',           
        'monto_total'         => 'Total (Bs)',
        'monto_pagado'        => 'Monto pagado (Bs)',
        'saldo'               => 'Saldo (Bs)',
        'tipo_pago'           => 'Tipo de pago',
        'estado_pago'         => 'Estado',
        'tipo_orden'          => 'Tipo',
        'numero_referencia' => 'N° Orden',
        'descuento' => 'Descuento (Bs)',  
        'fecha_registro'      => 'Fecha',
        'observaciones'      => 'OBSERVACIONES:',
                'conclusion'      => 'CONCLUSIÓN:',
        
        
    ];
}

public static function getEstadosPago()
{
    return ['pagado' => 'Pagado', 'pendiente' => 'Pendiente', ' ' => 'Cotización'];
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



// NUEVA relación many-to-many hacia Productos, a través de orden_detalle
public function getProductos()
{
    return $this->hasMany(Productos::class, ['idproducto' => 'idproducto'])
        ->viaTable('orden_detalle', ['idorden' => 'idorden']);
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
        return self::getEstadosPago()[$this->estado_pago] ?? 'cotizacion';
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