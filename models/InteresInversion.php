<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * "Quiero invertir": interés de un inversor en un proyecto. No es un compromiso ni un pago:
 * el equipo lo contacta y, si se concreta, registra el pago en Inversiones.
 * Uno por inversor y proyecto (si lo vuelve a enviar, se actualiza). monto NULL = "a conversar".
 *
 * @property int $idinteres
 * @property int $idproyecto
 * @property int $idusuario
 * @property string|null $monto
 * @property int|null $idmoneda
 * @property string|null $participacion
 * @property string|null $plazo
 * @property string $contacto_medio
 * @property string|null $contacto_horario
 * @property string|null $comentario
 * @property string $estado
 * @property string|null $ip
 * @property string $fecha_registro
 * @property string $fecha_actualizacion
 *
 * @property Proyecto $proyecto
 * @property Usuarios $usuario
 * @property Moneda|null $moneda
 * @property InteresInversionNota[] $notas
 */
class InteresInversion extends ActiveRecord
{
    /** clave => [etiqueta, clase de la web, clase del panel] */
    const ESTADOS = [
        'nuevo' => ['Recibido', 'is-nuevo', 'label-warning'],
        'contacto' => ['En contacto', 'is-contacto', 'label-info'],
        'negociacion' => ['En negociación', 'is-negociacion', 'label-primary'],
        'concretado' => ['Concretado', 'is-concretado', 'label-success'],
        'descartado' => ['Cerrado sin inversión', 'is-descartado', 'label-default'],
    ];
    /** Los que siguen en curso (el inversor no puede mandar otro, solo actualizarlo). */
    const ACTIVOS = ['nuevo', 'contacto', 'negociacion'];
    const MEDIOS = [
        'whatsapp' => ['WhatsApp', 'fa-brands fa-whatsapp'],
        'llamada' => ['Llamada', 'fa-solid fa-phone'],
        'correo' => ['Correo', 'fa-solid fa-envelope'],
        'reunion' => ['Reunión', 'fa-solid fa-handshake'],
    ];
    /** Plazo de contacto que se le promete al inversor. */
    const HORAS_CONTACTO = 48;

    public static function tableName()
    {
        return 'interes_inversion';
    }

    public function getProyecto()
    {
        return $this->hasOne(Proyecto::class, ['idproyecto' => 'idproyecto']);
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario']);
    }

    public function getInversor()
    {
        return $this->hasOne(Inversor::class, ['idusuario' => 'idusuario']);
    }

    public function getMoneda()
    {
        return $this->hasOne(Moneda::class, ['idmoneda' => 'idmoneda']);
    }

    public function getNotas()
    {
        return $this->hasMany(InteresInversionNota::class, ['idinteres' => 'idinteres'])->orderBy(['fecha' => SORT_DESC, 'idnota' => SORT_DESC]);
    }

    public function activo(): bool
    {
        return in_array($this->estado, self::ACTIVOS, true);
    }

    public function estadoNombre(): string
    {
        return self::ESTADOS[$this->estado][0] ?? $this->estado;
    }

    public function estadoClaseWeb(): string
    {
        return self::ESTADOS[$this->estado][1] ?? '';
    }

    public function estadoClasePanel(): string
    {
        return self::ESTADOS[$this->estado][2] ?? 'label-default';
    }

    /** "USD 50.000" o "A conversar". */
    public function montoTexto(): string
    {
        if ($this->monto === null) {
            return 'A conversar';
        }
        $m = (float) $this->monto;
        return trim(($this->moneda->codigo ?? '') . ' ' . number_format($m, floor($m) == $m ? 0 : 2, ',', '.'));
    }

    public function medioTexto(): string
    {
        return self::MEDIOS[$this->contacto_medio][0] ?? $this->contacto_medio;
    }
}
