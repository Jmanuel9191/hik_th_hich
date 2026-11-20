<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Quién descargó cada documento y cuándo (importante por el acuerdo de confidencialidad).
 *
 * @property int $iddescarga
 * @property int|null $iddocumento        documento del proyecto, o
 * @property int|null $idarchivo_avance   archivo de un avance
 * @property int $idproyecto
 * @property int|null $idusuario
 * @property string|null $ip
 * @property string $fecha
 */
class ProyectoDescarga extends ActiveRecord
{
    public static function tableName()
    {
        return 'proyecto_descarga';
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario']);
    }

    /** $idusuario: quien descarga (en la web, el inversor; si no se pasa, el usuario del panel). */
    public static function registrar(ProyectoDocumento $doc, ?int $idusuario = null)
    {
        $d = new static([
            'iddocumento' => $doc->iddocumento,
            'idproyecto' => $doc->idproyecto,
            'idusuario' => $idusuario ?? (Yii::$app->user->id ?? null),
            'ip' => Yii::$app->request instanceof \yii\web\Request ? Yii::$app->request->userIP : null,
            'fecha' => date('Y-m-d H:i:s'),
        ]);
        return $d->save(false);
    }

    public static function registrarAvance(ProyectoAvanceArchivo $a, $idproyecto)
    {
        $d = new static([
            'idarchivo_avance' => $a->idarchivo,
            'idproyecto' => (int) $idproyecto,
            'idusuario' => Yii::$app->user->id ?? null,
            'ip' => Yii::$app->request instanceof \yii\web\Request ? Yii::$app->request->userIP : null,
            'fecha' => date('Y-m-d H:i:s'),
        ]);
        return $d->save(false);
    }
}
