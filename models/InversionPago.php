<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * Pago de un inversor en un proyecto. Un inversor puede hacer varios pagos (se suman).
 * Se guarda en la moneda en que pagó; monto_equivalente = lo que vale en la moneda del proyecto
 * (es lo que suma al % de la ronda). No se borra: se anula con motivo.
 *
 * @property int $idpago
 * @property int $idproyecto
 * @property int $idusuario           inversor
 * @property string $fecha_pago
 * @property string $monto
 * @property int $idmoneda
 * @property string $monto_equivalente  en la moneda del proyecto
 * @property int $idmoneda_proyecto
 * @property string $tipo_pago
 * @property string|null $referencia
 * @property string|null $nota
 * @property string|null $comprobante_archivo
 * @property string|null $comprobante_nombre
 * @property string $estado           vigente | anulado
 * @property string|null $motivo_anulacion
 * @property int|null $idusuario_anulacion
 * @property string|null $fecha_anulacion
 * @property int|null $idusuario_registro
 * @property string $fecha_registro
 *
 * @property Proyecto $proyecto
 * @property Usuarios $inversor
 * @property Moneda $moneda
 * @property Moneda $monedaProyecto
 * @property Usuarios|null $registradoPor
 * @property Usuarios|null $anuladoPor
 */
class InversionPago extends ActiveRecord
{
    const TIPOS_PAGO = [
        'transferencia' => 'Transferencia bancaria',
        'deposito' => 'Depósito en cuenta',
        'efectivo' => 'Efectivo',
        'cheque' => 'Cheque',
        'qr' => 'Pago por QR',
        'otro' => 'Otro',
    ];
    /** Ayuda para el campo referencia según el tipo de pago. */
    const AYUDA_REFERENCIA = [
        'transferencia' => 'Banco y número de operación',
        'deposito' => 'Banco y número de depósito',
        'efectivo' => 'Quién lo recibió (opcional)',
        'cheque' => 'Banco y número de cheque',
        'qr' => 'Número de transacción',
        'otro' => 'Explique cómo pagó',
    ];
    const ESTADOS = ['vigente' => 'Vigente', 'anulado' => 'Anulado'];
    /** Proyectos en los que se pueden registrar pagos. */
    const ESTADOS_PROYECTO = ['publicado', 'cerrado'];

    const CARPETA = 'privado/inversiones';
    const MAX_COMPROBANTE_MB = 10;
    const FORMATOS_COMPROBANTE = [
        'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'], 'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
    ];

    /** @var UploadedFile|null */
    public $comprobante;

    public static function tableName()
    {
        return 'inversion_pago';
    }

    public function rules()
    {
        return [
            [['referencia', 'nota'], 'filter', 'filter' => fn($v) => trim(preg_replace('/[ \t]+/u', ' ', (string) $v))],
            [['monto', 'monto_equivalente'], 'filter', 'filter' => fn($v) => ($v = str_replace([' ', ','], ['', '.'], trim((string) $v))) === '' ? null : $v],
            [['idproyecto', 'idusuario', 'fecha_pago', 'monto', 'idmoneda', 'tipo_pago'], 'required'],
            [['idproyecto', 'idusuario', 'idmoneda'], 'integer'],
            [['fecha_pago'], 'date', 'format' => 'php:Y-m-d'],
            [['fecha_pago'], function ($a) {
                if ($this->$a > date('Y-m-d')) {
                    $this->addError($a, 'La fecha no puede ser futura.');
                }
            }],
            [['monto', 'monto_equivalente'], 'number', 'min' => 0.01, 'max' => 9999999999999, 'tooSmall' => 'Tiene que ser mayor a 0.'],
            [['monto_equivalente'], 'required', 'when' => fn() => $this->otraMoneda(),
                'whenClient' => "function () { return !$('[data-pago-equivalente]').hasClass('hidden'); }",
                'message' => 'El pago está en otra moneda: indique cuánto equivale en la moneda del proyecto.'],
            [['tipo_pago'], 'in', 'range' => array_keys(self::TIPOS_PAGO)],
            [['referencia'], 'required', 'when' => fn() => $this->tipo_pago === 'otro', 'message' => 'Explique cómo pagó.',
                'whenClient' => "function () { return $('[data-pago-tipo]:checked').val() === 'otro'; }"],
            [['referencia'], 'string', 'max' => 150],
            [['nota'], 'string', 'max' => 500],
            [['comprobante'], 'file', 'skipOnEmpty' => true, 'extensions' => array_keys(self::FORMATOS_COMPROBANTE),
                'maxSize' => self::MAX_COMPROBANTE_MB * 1024 * 1024, 'checkExtensionByMimeType' => true,
                'tooBig' => 'El comprobante pesa más de ' . self::MAX_COMPROBANTE_MB . ' MB.',
                'wrongExtension' => 'Formato no permitido: JPG, PNG, WEBP o PDF.'],
            [['idmoneda'], 'exist', 'targetClass' => Moneda::class],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idproyecto' => 'Proyecto',
            'idusuario' => 'Inversor',
            'fecha_pago' => 'Fecha del pago',
            'monto' => 'Monto pagado',
            'idmoneda' => 'Moneda en que pagó',
            'monto_equivalente' => 'Equivale a (moneda del proyecto)',
            'tipo_pago' => 'Tipo de pago',
            'referencia' => 'Referencia',
            'nota' => 'Nota interna (opcional)',
            'comprobante' => 'Comprobante (opcional)',
            'estado' => 'Estado',
            'motivo_anulacion' => 'Motivo de anulación',
        ];
    }

    public function beforeValidate()
    {
        if (!$this->comprobante instanceof UploadedFile) {
            $f = UploadedFile::getInstance($this, 'comprobante');
            $this->comprobante = $f && $f->error !== UPLOAD_ERR_NO_FILE ? $f : null;
        }
        return parent::beforeValidate();
    }

    /** ¿El pago está en una moneda distinta a la del proyecto? */
    public function otraMoneda()
    {
        $p = $this->proyecto;
        return $p && $this->idmoneda && (int) $this->idmoneda !== (int) $p->idmoneda;
    }

    public function getProyecto()
    {
        return $this->hasOne(Proyecto::class, ['idproyecto' => 'idproyecto']);
    }

    public function getInversor()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario']);
    }

    public function getFichaInversor()
    {
        return $this->hasOne(Inversor::class, ['idusuario' => 'idusuario']);
    }

    public function getMoneda()
    {
        return $this->hasOne(Moneda::class, ['idmoneda' => 'idmoneda']);
    }

    public function getMonedaProyecto()
    {
        return $this->hasOne(Moneda::class, ['idmoneda' => 'idmoneda_proyecto']);
    }

    public function getRegistradoPor()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_registro']);
    }

    public function getAnuladoPor()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_anulacion']);
    }

    public function esAnulado()
    {
        return $this->estado === 'anulado';
    }

    public static function carpetaDe($idproyecto)
    {
        return Yii::getAlias('@app/' . self::CARPETA . '/' . (int) $idproyecto);
    }

    /** "USD 5.000" / "BOB 1.250,50": siempre con el código de la moneda, igual en todo el sistema. */
    public static function dinero($monto, $moneda)
    {
        $m = (float) $monto;
        $dec = floor($m) == $m ? 0 : 2;
        return trim(($moneda->codigo ?? '') . ' ' . number_format($m, $dec, ',', '.'));
    }

    public function montoTexto()
    {
        return self::dinero($this->monto, $this->moneda);
    }

    public function equivalenteTexto()
    {
        return self::dinero($this->monto_equivalente, $this->monedaProyecto);
    }

    public function tipoPagoTexto()
    {
        return self::TIPOS_PAGO[$this->tipo_pago] ?? $this->tipo_pago;
    }

    /**
     * Totales de pagos vigentes agrupados por moneda: [['moneda' => Moneda, 'total' => float, 'pagos' => int], ...]
     * @param array $condicion  p.ej. ['idproyecto' => 3] o ['idusuario' => 7]
     */
    public static function totalesPorMoneda(array $condicion)
    {
        $filas = static::find()->select(['idmoneda', 'total' => 'SUM(monto)', 'pagos' => 'COUNT(*)'])
            ->where($condicion)->andWhere(['estado' => 'vigente'])->groupBy('idmoneda')->asArray()->all();
        $monedas = Moneda::find()->where(['idmoneda' => array_column($filas, 'idmoneda')])->indexBy('idmoneda')->all();
        return array_map(fn($f) => ['moneda' => $monedas[$f['idmoneda']] ?? null, 'total' => (float) $f['total'], 'pagos' => (int) $f['pagos']], $filas);
    }

    /** "BOB 7.000 + USD 5.000" */
    public static function totalesTexto(array $totales)
    {
        return $totales ? implode(' + ', array_map(fn($t) => self::dinero($t['total'], $t['moneda']), $totales)) : '—';
    }
}
