<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Historial de un miembro del equipo: quién, qué, cuándo y desde qué IP.
 *
 * @property int $idhistorial
 * @property int $idmiembro
 * @property int|null $idusuario_actor
 * @property string $accion
 * @property string|null $descripcion
 * @property string|null $ip
 * @property string $fecha
 */
class EquipoHistorial extends ActiveRecord
{
    public static function tableName()
    {
        return 'equipo_historial';
    }

    public function getUsuarioActor()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_actor']);
    }

    public static function registrar($idmiembro, $accion, $descripcion = '')
    {
        $h = new static();
        $h->idmiembro = $idmiembro;
        $h->idusuario_actor = Yii::$app->user->id ?? null;
        $h->accion = $accion;
        $h->descripcion = $descripcion;
        $h->ip = Yii::$app->request instanceof \yii\web\Request ? Yii::$app->request->userIP : null;
        $h->fecha = date('Y-m-d H:i:s');
        return $h->save(false);
    }
}
