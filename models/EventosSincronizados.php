<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "eventos_sincronizados".
 *
 * @property int $id
 * @property int $idusuario
 * @property string $titulo
 * @property string $fecha_inicio
 * @property string|null $google_event_id
 * @property string $fecha_sincronizado
 */
class EventosSincronizados extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eventos_sincronizados';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idusuario', 'titulo', 'fecha_inicio'], 'required'],
            [['idusuario'], 'integer'],
            [['fecha_inicio', 'fecha_sincronizado'], 'safe'],
            [['titulo', 'google_event_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idusuario' => 'Usuario',
            'titulo' => 'Título',
            'fecha_inicio' => 'Fecha de Inicio',
            'google_event_id' => 'ID de Evento Google',
            'fecha_sincronizado' => 'Fecha de Sincronización',
        ];
    }
}
