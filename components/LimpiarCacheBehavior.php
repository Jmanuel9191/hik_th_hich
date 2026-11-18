<?php

declare(strict_types=1);

namespace app\components;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Borra claves de caché cuando el modelo se crea, edita o elimina.
 * Uso: ['class' => LimpiarCacheBehavior::class, 'claves' => ['sitio_config']]
 */
class LimpiarCacheBehavior extends Behavior
{
    /** @var string[] claves de caché que dependen de este modelo */
    public array $claves = [];

    public function events(): array
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'limpiar',
            ActiveRecord::EVENT_AFTER_UPDATE => 'limpiar',
            ActiveRecord::EVENT_AFTER_DELETE => 'limpiar',
        ];
    }

    public function limpiar(): void
    {
        foreach ($this->claves as $clave) {
            Yii::$app->cache->delete($clave);
        }
    }
}