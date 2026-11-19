<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Datos de la solicitud de un inversor (1 a 1 con usuarios, idrol 5).
 * La cuenta (nombre, email, contraseña, estado de bloqueo) sigue en `usuarios`.
 *
 * @property int $idusuario
 * @property string $tipo                 persona | empresa
 * @property string $documento_tipo
 * @property string $documento_numero
 * @property string|null $documento_archivo   privado/inversores/<id>/
 * @property string|null $documento_nombre
 * @property string|null $razon_social
 * @property string|null $nit
 * @property string|null $sitio_web
 * @property string $monto_minimo  USD
 * @property string $monto_maximo  USD
 * @property string|null $plazo_preferido
 * @property string|null $participacion_preferida
 * @property string|null $como_nos_conocio
 * @property string|null $mensaje
 * @property string $estado_solicitud     sin_verificar | pendiente | aprobado | rechazado
 * @property string|null $motivo_rechazo
 * @property string $origen               web | admin
 * @property int|null $idusuario_registro quién la cargó (si fue desde el panel)
 * @property string|null $terminos_fecha
 * @property string|null $terminos_ip
 * @property string|null $email_verificado
 * @property string|null $verificacion_hash
 * @property string|null $verificacion_expira
 * @property string|null $fecha_aprobacion
 * @property int|null $idusuario_aprobacion
 * @property string $fecha_registro
 * @property string|null $fecha_actualizacion
 *
 * @property Usuarios $usuario
 * @property Ecosistema[] $intereses
 * @property Ecosistema[] $areas
 * @property NdaAceptacion[] $aceptaciones
 */
class Inversor extends ActiveRecord
{
    const TIPOS = ['persona' => 'Persona', 'empresa' => 'Empresa'];
    const DOCUMENTOS = ['ci' => 'Cédula de identidad', 'pasaporte' => 'Pasaporte', 'dni' => 'DNI', 'otro' => 'Otro'];
    /** Tope de los montos (USD). */
    const MONTO_MAXIMO = 999999999999;
    const PLAZOS = ['corto' => 'Corto (hasta 2 años)', 'mediano' => 'Mediano (2 a 5 años)', 'largo' => 'Largo (más de 5 años)'];
    const PARTICIPACIONES = [
        'indistinto' => 'Indistinto',
        'acciones' => 'Acciones / participación societaria',
        'prestamo' => 'Préstamo / deuda',
        'jv' => 'Sociedad conjunta (Joint Venture)',
    ];
    /** clave => [etiqueta, clase del label] */
    const ESTADOS = [
        'sin_verificar' => ['Sin verificar el correo', 'label-default'],
        'pendiente' => ['Pendiente', 'label-warning'],
        'aprobado' => ['Aprobado', 'label-success'],
        'rechazado' => ['Rechazado', 'label-danger'],
    ];

    const CARPETA = 'privado/inversores';
    const MAX_DOCUMENTO_MB = 8;
    /** Formatos del documento de identidad. */
    const FORMATOS_DOCUMENTO = [
        'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'], 'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
    ];

    public static function tableName()
    {
        return 'inversor';
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario']);
    }

    public function getIntereses()
    {
        return $this->hasMany(Ecosistema::class, ['idecosistema' => 'idecosistema'])
            ->viaTable('inversor_interes', ['idusuario' => 'idusuario'])->orderBy('numero');
    }

    public function getAreas()
    {
        return $this->hasMany(Ecosistema::class, ['idecosistema' => 'idecosistema'])
            ->viaTable('inversor_area', ['idusuario' => 'idusuario'])->orderBy('numero');
    }

    public function getAceptaciones()
    {
        return $this->hasMany(NdaAceptacion::class, ['idusuario' => 'idusuario'])->orderBy(['fecha' => SORT_DESC]);
    }

    public function estadoNombre()
    {
        return self::ESTADOS[$this->estado_solicitud][0] ?? $this->estado_solicitud;
    }

    public function estadoClase()
    {
        return self::ESTADOS[$this->estado_solicitud][1] ?? 'label-default';
    }

    /** Nombre para mostrar: la razón social si es empresa. */
    public function nombreVisible()
    {
        $nombre = $this->usuario->nombrecompleto ?? '';
        return $this->tipo === 'empresa' && $this->razon_social ? $this->razon_social . ' (' . $nombre . ')' : $nombre;
    }

    public static function carpetaDe($id)
    {
        return \Yii::getAlias('@app/' . self::CARPETA . '/' . (int) $id);
    }

    public function urlDocumento()
    {
        return $this->documento_archivo ? Url::to(['/admin/inversores/documento', 'id' => $this->idusuario]) : null;
    }

    /** Ids de las áreas aprobadas (las que puede ver en el banco de proyectos). */
    public static function idsAreas($idusuario)
    {
        return array_map('intval', (new \yii\db\Query())->select('idecosistema')->from('inversor_area')->where(['idusuario' => (int) $idusuario])->column());
    }

    /** "USD 50.000 – 250.000" (o un solo número si mínimo y máximo son iguales). */
    public function montoTexto()
    {
        $f = fn($v) => number_format((float) $v, 0, ',', '.');
        if ($this->monto_minimo === null) {
            return '';
        }
        return 'USD ' . ((float) $this->monto_minimo === (float) $this->monto_maximo
            ? $f($this->monto_minimo) : $f($this->monto_minimo) . ' – ' . $f($this->monto_maximo));
    }
}
