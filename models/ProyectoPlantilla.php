<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Plantilla de página de proyecto: un conjunto de secciones con sus bloques que se copia al proyecto.
 * Puede sugerirse para un ecosistema (al crear un proyecto de ese ecosistema viene preseleccionada).
 * Las fotos de sus bloques están en privado/plantillas/<id>/bloques.
 *
 * @property int $idplantilla
 * @property string $nombre
 * @property string|null $descripcion
 * @property int|null $idecosistema
 * @property int $orden
 * @property string $estado '1' activa | '0' oculta
 * @property int|null $idusuario_registro
 * @property string $fecha_registro
 * @property string|null $fecha_actualizacion
 *
 * @property Ecosistema|null $ecosistema
 * @property ProyectoSeccion[] $secciones
 */
class ProyectoPlantilla extends ActiveRecord
{
    const CARPETA = 'privado/plantillas';

    public static function tableName()
    {
        return 'proyecto_plantilla';
    }

    public function rules()
    {
        return [
            [['nombre', 'descripcion'], 'filter', 'filter' => fn($v) => trim((string) $v)],
            [['descripcion'], 'filter', 'filter' => fn($v) => $v === '' ? null : $v],
            [['nombre'], 'required'],
            [['nombre'], 'string', 'max' => 100],
            [['nombre'], 'unique', 'message' => 'Ya hay una plantilla con ese nombre.'],
            [['descripcion'], 'string', 'max' => 300],
            [['idecosistema'], 'filter', 'filter' => fn($v) => $v === '' || $v === null ? null : (int) $v],
            [['idecosistema'], 'exist', 'targetClass' => Ecosistema::class, 'skipOnEmpty' => true],
            [['estado'], 'in', 'range' => ['0', '1']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'nombre' => 'Nombre',
            'descripcion' => 'Para qué sirve',
            'idecosistema' => 'Se sugiere para el ecosistema',
            'estado' => 'Activa',
        ];
    }

    public function getEcosistema()
    {
        return $this->hasOne(Ecosistema::class, ['idecosistema' => 'idecosistema']);
    }

    public function getSecciones()
    {
        return $this->hasMany(ProyectoSeccion::class, ['idplantilla' => 'idplantilla'])->orderBy(['orden' => SORT_ASC, 'idseccion' => SORT_ASC]);
    }

    public static function carpetaDe($id, $sub = '')
    {
        return Yii::getAlias('@app/' . self::CARPETA . '/' . (int) $id . ($sub !== '' ? '/' . $sub : ''));
    }

    /** id => nombre de las activas (para elegir al crear un proyecto o cargar una plantilla). */
    public static function lista(): array
    {
        return static::find()->select(['nombre', 'idplantilla'])->where(['estado' => '1'])
            ->orderBy(['orden' => SORT_ASC, 'nombre' => SORT_ASC])->indexBy('idplantilla')->column();
    }

    /** La plantilla sugerida para un ecosistema (la primera activa de ese ecosistema). */
    public static function sugerida($idecosistema): ?int
    {
        $id = static::find()->select('idplantilla')->where(['estado' => '1', 'idecosistema' => (int) $idecosistema])
            ->orderBy(['orden' => SORT_ASC])->scalar();
        return $id ? (int) $id : null;
    }
}
