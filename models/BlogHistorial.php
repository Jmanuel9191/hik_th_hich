<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Historial del blog: quién, qué, cuándo y desde qué IP. tipo = "noticia" o "categoria".
 *
 * @property int $idhistorial
 * @property string $tipo
 * @property int $idregistro
 * @property int|null $idusuario_actor
 * @property string $accion
 * @property string|null $descripcion
 * @property string|null $ip
 * @property string $fecha
 */
class BlogHistorial extends ActiveRecord
{
    const NOTICIA = 'noticia';
    const CATEGORIA = 'categoria';

    public static function tableName()
    {
        return 'blog_historial';
    }

    public function getUsuarioActor()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario_actor']);
    }

    public static function registrar(string $tipo, int $idregistro, string $accion, string $descripcion = ''): bool
    {
        $h = new static();
        $h->tipo = $tipo;
        $h->idregistro = $idregistro;
        $h->idusuario_actor = Yii::$app->user->id ?? null;
        $h->accion = $accion;
        $h->descripcion = $descripcion;
        $h->ip = Yii::$app->request instanceof \yii\web\Request ? Yii::$app->request->userIP : null;
        $h->fecha = date('Y-m-d H:i:s');
        return $h->save(false);
    }
}
