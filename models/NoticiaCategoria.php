<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * Categoría del blog (Panel → Blog → Categorías). En la web: /noticias/categoria/<slug>.
 *
 * @property int $idcategoria
 * @property string $nombre
 * @property string $slug
 * @property int $orden
 * @property string $estado  '1' visible / '0' oculta (sus noticias siguen saliendo en el listado general)
 * @property int|null $idusuario_registro
 * @property string $fecha_registro
 * @property string|null $fecha_actualizacion
 *
 * @property Noticia[] $noticias
 */
class NoticiaCategoria extends ActiveRecord
{
    public static function tableName()
    {
        return 'noticia_categoria';
    }

    public function rules()
    {
        return [
            [['nombre'], 'filter', 'filter' => fn($v) => trim(preg_replace('/\s+/u', ' ', (string) $v))],
            [['nombre'], 'required'],
            [['nombre'], 'string', 'max' => 100],
            [['nombre'], 'unique', 'message' => 'Ya hay una categoría con ese nombre.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idcategoria' => 'N°',
            'nombre' => 'Nombre',
            'slug' => 'Dirección en la web',
            'orden' => 'Orden',
            'estado' => 'Estado',
            'fecha_registro' => 'Creada',
            'fecha_actualizacion' => 'Última modificación',
        ];
    }

    /** El slug sale del nombre (sin acentos ni espacios) y se actualiza si cambia el nombre. */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert || $this->isAttributeChanged('nombre')) {
            $base = Inflector::slug($this->nombre) ?: 'categoria';
            $slug = $base;
            $i = 2;
            while (static::find()->where(['slug' => $slug])->andFilterWhere(['<>', 'idcategoria', $this->idcategoria])->exists()) {
                $slug = $base . '-' . $i++;
            }
            $this->slug = $slug;
        }
        return true;
    }

    public function getNoticias()
    {
        return $this->hasMany(Noticia::class, ['idcategoria' => 'idcategoria']);
    }

    /** Para selectores del panel: id => nombre (todas, en orden). */
    public static function lista(): array
    {
        return static::find()->select(['nombre', 'idcategoria'])->orderBy(['orden' => SORT_ASC, 'nombre' => SORT_ASC])->indexBy('idcategoria')->column();
    }
}
