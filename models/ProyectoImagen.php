<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Foto de la galería de un proyecto (privado/proyectos/<id>/galeria).
 *
 * @property int $idimagen
 * @property int $idproyecto
 * @property string $archivo
 * @property string $nombre_original
 * @property int $orden
 * @property int|null $idusuario
 * @property string $fecha_registro
 */
class ProyectoImagen extends ActiveRecord
{
    public static function tableName()
    {
        return 'proyecto_imagen';
    }

    public function url()
    {
        return Url::to(['/admin/banco-proyectos/galeria-imagen', 'id' => $this->idimagen]);
    }
}
