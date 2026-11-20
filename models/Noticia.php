<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * Noticia del blog (Panel → Blog). En la web: /noticias/<slug>.
 * Imágenes en imagenes/noticias/<id>/ (portada y contenido) y /galeria/ (NoticiaImagen).
 *
 * @property int $idnoticia
 * @property string $titulo
 * @property string $slug           dirección en la web; vacío al guardar = sale del título
 * @property string $resumen        sale en la tarjeta del listado y al compartir (descripción para Google y redes)
 * @property string $descripcion    texto completo, HTML limpio (HtmlEditor)
 * @property string $fecha          fecha de la noticia; si no la ponen, la del día en que se crea
 * @property int $idcategoria
 * @property string $imagen_portada   tarjeta del listado y al compartir
 * @property string|null $imagen_contenido  dentro de la noticia (si no hay, va la portada)
 * @property string $estado         '1' publicada / '0' oculta
 * @property string $destacado      '1' destacada (para el carrusel del Inicio)
 * @property int|null $idusuario_registro
 * @property string $fecha_registro
 * @property string|null $fecha_actualizacion
 *
 * @property NoticiaCategoria $categoria
 * @property NoticiaImagen[] $imagenes
 */
class Noticia extends ActiveRecord
{
    const CARPETA = 'imagenes/noticias';

    /** Columna => [etiqueta, medida recomendada] */
    const IMAGENES = [
        'imagen_portada' => ['Imagen de portada', '1200 × 800 px (horizontal) · sale en el listado y al compartir en redes'],
        'imagen_contenido' => ['Imagen del contenido (opcional)', '1600 × 900 px (horizontal) · sale dentro de la noticia; si no hay, va la portada'],
    ];

    /** Direcciones que no puede usar una noticia (son otras páginas del blog). */
    const SLUGS_RESERVADOS = ['categoria', 'pagina', 'buscar'];

    /** Largo máximo del resumen: entra justo en la tarjeta y en la vista previa al compartir. */
    const RESUMEN_MAX = 300;

    public static function tableName()
    {
        return 'noticia';
    }

    public function rules()
    {
        return [
            [['titulo', 'slug', 'resumen', 'fecha'], 'filter', 'filter' => fn($v) => trim(preg_replace('/[ \t]+/u', ' ', (string) $v))],
            [['titulo', 'resumen', 'descripcion', 'idcategoria'], 'required'],
            [['titulo'], 'string', 'max' => 200],
            [['slug'], 'filter', 'filter' => fn($v) => $v === '' ? '' : Inflector::slug($v)],
            [['slug'], 'string', 'max' => 200],
            [['slug'], 'in', 'not' => true, 'range' => self::SLUGS_RESERVADOS, 'message' => 'Esa dirección está reservada, elija otra.'],
            [['slug'], 'unique', 'filter' => fn($q) => $q->andWhere(['<>', 'slug', '']), 'message' => 'Ya hay otra noticia con esa dirección.'],
            [['resumen'], 'string', 'max' => self::RESUMEN_MAX],
            [['fecha'], 'date', 'format' => 'php:Y-m-d', 'message' => 'Fecha no válida.', 'skipOnEmpty' => true],
            [['idcategoria'], 'exist', 'targetClass' => NoticiaCategoria::class, 'message' => 'Elija una categoría de la lista.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idnoticia' => 'N°',
            'titulo' => 'Título',
            'slug' => 'Dirección en la web',
            'resumen' => 'Resumen',
            'descripcion' => 'Descripción completa',
            'fecha' => 'Fecha de la noticia',
            'idcategoria' => 'Categoría',
            'estado' => 'Estado',
            'destacado' => 'Destacada',
            'fecha_registro' => 'Creada',
            'fecha_actualizacion' => 'Última modificación',
        ] + array_map(fn($i) => $i[0], self::IMAGENES);
    }

    /** Slug único: el que escribieron o, si está vacío, el del título. */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ((string) $this->slug === '') {
            $base = Inflector::slug($this->titulo) ?: 'noticia';
            if (in_array($base, self::SLUGS_RESERVADOS, true)) {
                $base .= '-noticia';
            }
            $base = mb_substr($base, 0, 200);
            $slug = $base;
            $i = 2;
            while (static::find()->where(['slug' => $slug])->andFilterWhere(['<>', 'idnoticia', $this->idnoticia])->exists()) {
                $slug = $base . '-' . $i++;
            }
            $this->slug = $slug;
        }
        return true;
    }

    public function getCategoria()
    {
        return $this->hasOne(NoticiaCategoria::class, ['idcategoria' => 'idcategoria']);
    }

    public function getImagenes()
    {
        return $this->hasMany(NoticiaImagen::class, ['idnoticia' => 'idnoticia'])->orderBy(['orden' => SORT_ASC, 'idimagen' => SORT_ASC]);
    }

    public function carpeta(): string
    {
        return Yii::getAlias('@webroot/' . self::CARPETA . '/' . (int) $this->idnoticia);
    }

    public function url($archivo): ?string
    {
        return $archivo ? Yii::getAlias('@web/' . self::CARPETA . '/' . (int) $this->idnoticia . '/' . $archivo) : null;
    }

    /** Imagen de adentro de la noticia: la de contenido o, si no hay, la portada. */
    public function imagenPrincipal(): ?string
    {
        return $this->url($this->imagen_contenido ?: $this->imagen_portada);
    }

    /** Para la web: publicadas, de la más nueva a la más vieja. */
    public static function publicadas()
    {
        return static::find()->alias('n')->where(['n.estado' => '1'])->orderBy(['n.fecha' => SORT_DESC, 'n.idnoticia' => SORT_DESC]);
    }
}
