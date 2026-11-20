<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Historial de un "Quiero invertir": cambios de estado, notas internas del equipo y cambios del inversor.
 *
 * @property int $idnota
 * @property int $idinteres
 * @property int|null $idusuario
 * @property string $tipo    estado | nota | inversor
 * @property string $texto
 * @property string $fecha
 *
 * @property Usuarios|null $usuario
 */
class InteresInversionNota extends ActiveRecord
{
    public static function tableName()
    {
        return 'interes_inversion_nota';
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario']);
    }

    public static function registrar(int $idinteres, string $tipo, string $texto, ?int $idusuario = null): void
    {
        $n = new static();
        $n->setAttributes(['idinteres' => $idinteres, 'tipo' => $tipo, 'texto' => mb_substr($texto, 0, 1000), 'idusuario' => $idusuario,
            'fecha' => date('Y-m-d H:i:s')], false);
        $n->save(false);
    }
}
