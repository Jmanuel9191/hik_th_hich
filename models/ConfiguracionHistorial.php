<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Historial de "Datos generales": quién cambió qué, cuándo y desde qué IP.
 *
 * @property int $idhistorial
 * @property int|null $idusuario_actor
 * @property string|null $ip
 * @property string $fecha
 * @property string $detalle
 *
 * @property Usuarios|null $usuarioActor
 */
class ConfiguracionHistorial extends ActiveRecord
{
    public static function tableName()
    {
        return 'configuracion_historial';
    }

    public function getUsuarioActor()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_actor']);
    }

    public static function registrar($detalle)
    {
        $h = new static();
        $h->idusuario_actor = Yii::$app->user->id ?? null;
        $h->ip = Yii::$app->request instanceof \yii\web\Request ? Yii::$app->request->userIP : null;
        $h->fecha = date('Y-m-d H:i:s');
        $h->detalle = $detalle;
        return $h->save(false);
    }
}
