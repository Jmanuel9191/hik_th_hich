<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Foto de la galería de una noticia (imagenes/noticias/<id>/galeria/).
 *
 * @property int $idimagen
 * @property int $idnoticia
 * @property string $archivo
 * @property string|null $nombre_original
 * @property int $orden
 * @property int|null $idusuario
 * @property string $fecha_registro
 */
class NoticiaImagen extends ActiveRecord
{
    public static function tableName()
    {
        return 'noticia_imagen';
    }
}
