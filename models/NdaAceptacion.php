<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Aceptación del acuerdo de confidencialidad por un inversor (qué versión, con qué firma, cuándo, desde dónde).
 *
 * @property int $idaceptacion
 * @property int $idusuario
 * @property int $idversion
 * @property string $firma       nombre completo que escribió al aceptar
 * @property string|null $ip
 * @property string|null $navegador
 * @property string $fecha
 *
 * @property NdaVersion $version
 */
class NdaAceptacion extends ActiveRecord
{
    public static function tableName()
    {
        return 'nda_aceptacion';
    }

    public function getVersion()
    {
        return $this->hasOne(NdaVersion::class, ['idversion' => 'idversion']);
    }

    /** ¿El inversor aceptó la versión vigente? */
    public static function aceptoVigente($idusuario)
    {
        $vigente = NdaVersion::vigente();
        return $vigente === null || static::find()->where(['idusuario' => (int) $idusuario, 'idversion' => $vigente->idversion])->exists();
    }
}
