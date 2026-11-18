<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * Ecosistema empresarial (una de las 10 áreas del Grupo).
 *
 * @property int $idecosistema
 * @property int $numero
 * @property string $nombre
 * @property string $slug
 * @property string $resumen
 * @property string|null $descripcion   HTML ya limpio (HtmlPurifier) al guardar
 * @property string|null $icono
 * @property string|null $foto_portada
 * @property string|null $foto_contenido
 * @property string|null $foto_banner
 * @property string $estado              '1' activo (se ve en la web) / '0' inactivo
 * @property string $fecha_registro
 * @property string|null $fecha_actualizacion
 * @property int|null $idusuario_registro
 *
 * @property EcosistemaImagen[] $imagenes
 * @property Usuarios[] $usuarios
 */
class Ecosistema extends ActiveRecord
{
    /** Imágenes sueltas del ecosistema: campo => etiqueta. */
    const IMAGENES = [
        'icono' => 'Ícono',
        'foto_portada' => 'Foto de portada',
        'foto_contenido' => 'Foto de contenido',
        'foto_banner' => 'Foto de banner',
    ];

    /** Medida sugerida que se muestra debajo de cada campo de imagen. */
    const MEDIDAS = [
        'icono' => 'SVG o PNG cuadrado, fondo transparente (máx. 1 MB).',
        'foto_portada' => '800 × 600 px aprox. (máx. 5 MB).',
        'foto_contenido' => '1200 × 800 px aprox. (máx. 5 MB).',
        'foto_banner' => '1920 × 600 px aprox. (máx. 5 MB).',
    ];

    /** Carpeta pública (dentro de imagenes/) donde van las imágenes de los ecosistemas. */
    const CARPETA = 'imagenes/ecosistemas';

    public static function tableName()
    {
        return 'ecosistema';
    }

    public function rules()
    {
        return [
            [['nombre', 'resumen'], 'required'],
            [['nombre'], 'string', 'max' => 120],
            [['resumen'], 'string', 'max' => 500],
            [['numero'], 'integer', 'min' => 0, 'max' => 999],
            [['numero'], 'default', 'value' => 0],
            [['descripcion'], 'string'],
            [['nombre'], 'unique', 'message' => 'Ya existe un ecosistema con ese nombre.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idecosistema' => 'ID',
            'numero' => 'Número',
            'nombre' => 'Título',
            'slug' => 'URL',
            'resumen' => 'Resumen',
            'descripcion' => 'Descripción',
            'estado' => 'Estado',
            'fecha_registro' => 'Fecha de registro',
            'fecha_actualizacion' => 'Última modificación',
        ] + self::IMAGENES;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert || $this->isAttributeChanged('nombre')) {
            $this->slug = $this->slugUnico(Inflector::slug($this->nombre));
        }
        return true;
    }

    private function slugUnico($base)
    {
        $slug = $base !== '' ? $base : 'ecosistema';
        $i = 2;
        while (static::find()->where(['slug' => $slug])->andFilterWhere(['<>', 'idecosistema', $this->idecosistema])->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function getImagenes()
    {
        return $this->hasMany(EcosistemaImagen::class, ['idecosistema' => 'idecosistema'])
            ->orderBy(['orden' => SORT_ASC, 'idimagen' => SORT_ASC]);
    }

    public function getUsuarios()
    {
        return $this->hasMany(Usuarios::class, ['idusuario' => 'idusuario'])
            ->viaTable('usuario_ecosistema', ['idecosistema' => 'idecosistema']);
    }

    /** Carpeta en disco de las imágenes de este ecosistema. */
    public function carpeta()
    {
        return Yii::getAlias('@webroot/' . self::CARPETA . '/' . (int) $this->idecosistema);
    }

    /** URL pública de un archivo de este ecosistema (o null). */
    public function url($archivo)
    {
        return $archivo ? Yii::getAlias('@web/' . self::CARPETA . '/' . (int) $this->idecosistema . '/' . $archivo) : null;
    }

    /** Lista id => "01 · Nombre" para selectores. */
    public static function lista($soloActivos = true)
    {
        $q = static::find()->orderBy(['numero' => SORT_ASC, 'nombre' => SORT_ASC]);
        if ($soloActivos) {
            $q->where(['estado' => '1']);
        }
        $lista = [];
        foreach ($q->all() as $e) {
            $lista[$e->idecosistema] = sprintf('%02d · %s', $e->numero, $e->nombre);
        }
        return $lista;
    }
}
