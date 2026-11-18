<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Imagen de la galería de un ecosistema.
 *
 * @property int $idimagen
 * @property int $idecosistema
 * @property string $archivo
 * @property string $nombre_original
 * @property int $orden
 * @property string $fecha_registro
 * @property int|null $idusuario
 *
 * @property Ecosistema $ecosistema
 */
class EcosistemaImagen extends ActiveRecord
{
    public static function tableName()
    {
        return 'ecosistema_imagen';
    }

    public function getEcosistema()
    {
        return $this->hasOne(Ecosistema::class, ['idecosistema' => 'idecosistema']);
    }
}
