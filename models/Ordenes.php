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
        [['monto_efectivo', 'monto_qr'], 'number', 'min' => 0],
        [['monto_efectivo', 'monto_qr'], 'default', 'value' => 0],
        [['monto_pagado'], 'required', 'when' => fn($m) => $m->tipo_orden !== 'cotizacion',
            'whenClient' => "function(attr, val) { return \$('input[name=\"Ordenes[tipo_orden]\"]:checked').val() !== 'cotizacion'; }"],
        [['tipo_pago'], 'required', 'when' => fn($m) => $m->tipo_orden !== 'cotizacion',
            'whenClient' => "function(attr, val) { return \$('input[name=\"Ordenes[tipo_orden]\"]:checked').val() !== 'cotizacion'; }"],
        [['tipo_pago'], 'in', 'range' => ['efectivo', 'qr', 'mixto']],
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
        'monto_efectivo'      => 'Pagado en efectivo (Bs)',
        'monto_qr'            => 'Pagado por QR (Bs)',
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

    // NUEVO: quién cargó/validó los resultados — distinto de quién
    // registró la orden (facturación/recepción). De acá sale la foto
    // del sello/firma para el PDF de resultados.
    public function getUsuarioResultados()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_resultados']);
    }

    public function getDetalles()
    {
        return $this->hasMany(OrdenDetalle::class, ['idorden' => 'idorden']);
    }

    // Helpers
    public static function getTiposPago()
    {
        return ['efectivo' => 'Efectivo', 'qr' => 'QR', 'mixto' => 'Mixto (Efectivo + QR)'];
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

    /**
     * Para el listado (index.php): si el pago fue mixto, muestra el
     * desglose real ("100.00 QR + 50.00 Efectivo") en vez de solo la
     * palabra "Mixto". Es puramente para mostrar — el dato que manda
     * en el sistema sigue siendo monto_pagado.
     */
    public function getTipoPagoDetalle()
    {
        if ($this->tipo_pago === 'mixto') {
            return number_format($this->monto_qr, 2) . ' QR + '
                 . number_format($this->monto_efectivo, 2) . ' Efectivo';
        }
        return $this->getTipoPagoLabel();
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

    /**
     * Blindaje del lado del servidor: monto_pagado y tipo_pago se
     * recalculan siempre a partir de monto_efectivo + monto_qr, sin
     * confiar en lo que venga del formulario. Así evitamos que alguien
     * edite a mano el campo oculto de tipo_pago o el readonly de
     * monto_pagado antes de enviar el POST.
     *
     * En cotizaciones no aplica: ahí el pago no importa todavía.
     */
    public function beforeValidate()
    {
        if ($this->tipo_orden !== 'cotizacion') {
            $efectivo = (float)$this->monto_efectivo;
            $qr       = (float)$this->monto_qr;

            $this->monto_pagado = $efectivo + $qr;

            if ($efectivo > 0 && $qr > 0) {
                $this->tipo_pago = 'mixto';
            } elseif ($qr > 0) {
                $this->tipo_pago = 'qr';
            } else {
                $this->tipo_pago = 'efectivo';
            }
        }

        return parent::beforeValidate();
    }
}