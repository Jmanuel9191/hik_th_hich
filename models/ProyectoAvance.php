<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Avance de un proyecto (bitácora): cómo va, con fotos, videos, documentos o enlaces.
 * Si trae etapa o %, actualiza la ficha del proyecto (ver AvanceService).
 *
 * @property int $idavance
 * @property int $idproyecto
 * @property string $fecha
 * @property string $titulo
 * @property string|null $texto        HTML limpio (HtmlEditor)
 * @property string|null $etapa        clave de Proyecto::ETAPAS
 * @property int|null $porcentaje      0–100
 * @property string $estado            '1' visible / '0' oculto
 * @property int|null $idusuario_registro
 * @property string $fecha_registro
 * @property string|null $fecha_actualizacion
 *
 * @property Proyecto $proyecto
 * @property ProyectoAvanceArchivo[] $archivos
 * @property Usuarios|null $usuario
 */
class ProyectoAvance extends ActiveRecord
{
    /** Máximo de archivos por avance, por tipo. */
    const MAXIMOS = ['foto' => 10, 'video' => 2, 'documento' => 5, 'enlace' => 3];

    public static function tableName()
    {
        return 'proyecto_avance';
    }

    public function rules()
    {
        return [
            [['titulo'], 'filter', 'filter' => fn($v) => trim((string) $v)],
            [['etapa', 'porcentaje'], 'filter', 'filter' => fn($v) => trim((string) $v) === '' ? null : trim((string) $v)],
            [['fecha', 'titulo'], 'required'],
            [['fecha'], 'date', 'format' => 'php:Y-m-d'],
            [['titulo'], 'string', 'max' => 160],
            [['etapa'], 'in', 'range' => array_keys(Proyecto::ETAPAS)],
            [['porcentaje'], 'integer', 'min' => 0, 'max' => 100, 'message' => 'Un número del 0 al 100.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fecha' => 'Fecha',
            'titulo' => 'Título',
            'texto' => 'Qué pasó',
            'etapa' => 'Etapa (si cambió)',
            'porcentaje' => '% de avance del proyecto',
            'estado' => 'Estado',
        ];
    }

    public function getProyecto()
    {
        return $this->hasOne(Proyecto::class, ['idproyecto' => 'idproyecto']);
    }

    public function getArchivos()
    {
        return $this->hasMany(ProyectoAvanceArchivo::class, ['idavance' => 'idavance'])->orderBy(['orden' => SORT_ASC, 'idarchivo' => SORT_ASC]);
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_registro']);
    }

    public function etapaNombre()
    {
        return Proyecto::ETAPAS[$this->etapa] ?? null;
    }

    /** Archivos de un tipo (foto, video, documento, enlace). */
    public function archivosDe($tipo)
    {
        return array_values(array_filter($this->archivos, fn($a) => $a->tipo === $tipo));
    }
}
