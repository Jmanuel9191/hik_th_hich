<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Documento privado de un proyecto (privado/proyectos/<id>/documentos). Cada descarga queda en proyecto_descarga.
 *
 * @property int $iddocumento
 * @property int $idproyecto
 * @property string $nombre
 * @property string $tipo
 * @property string $archivo
 * @property string $nombre_original
 * @property string|null $mime
 * @property int|null $tamano
 * @property string $visible_inversor   '1' lo ven los inversores / '0' solo HUK
 * @property int|null $idusuario
 * @property string $fecha_registro
 *
 * @property Usuarios|null $usuario
 */
class ProyectoDocumento extends ActiveRecord
{
    const TIPOS = [
        'plan_negocios' => 'Plan de negocios',
        'factibilidad' => 'Estudio de factibilidad',
        'financiero' => 'Estados / modelo financiero',
        'legal' => 'Legal / contratos',
        'tecnico' => 'Técnico',
        'presentacion' => 'Presentación',
        'otro' => 'Otro',
    ];

    /** Extensión => tipos MIME aceptados. */
    const FORMATOS = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword', 'application/vnd.ms-office', 'application/CDFV2'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        'xls' => ['application/vnd.ms-excel', 'application/vnd.ms-office', 'application/CDFV2'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
        'ppt' => ['application/vnd.ms-powerpoint', 'application/vnd.ms-office', 'application/CDFV2'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip', 'application/octet-stream'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
    ];

    public static function tableName()
    {
        return 'proyecto_documento';
    }

    public function rules()
    {
        return [
            [['nombre'], 'filter', 'filter' => fn($v) => trim((string) $v)],
            [['nombre', 'tipo'], 'required'],
            [['nombre'], 'string', 'max' => 150],
            [['tipo'], 'in', 'range' => array_keys(self::TIPOS)],
            [['visible_inversor'], 'in', 'range' => ['0', '1']],
        ];
    }

    public function attributeLabels()
    {
        return ['nombre' => 'Nombre del documento', 'tipo' => 'Tipo', 'visible_inversor' => 'Lo ven los inversores', 'archivo' => 'Archivo'];
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario']);
    }

    public function tipoNombre()
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }
}
