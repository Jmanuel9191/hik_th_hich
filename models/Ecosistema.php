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
 * @property string|null $subtitulo   opcional (ej. el nombre en inglés): chico debajo del título en las tarjetas
 * @property string $slug
 * @property string $resumen
 * @property string|null $etiquetas   JSON: etiquetas del banner de su página (listaEtiquetas())
 * @property string|null $enfoque     recuadro negro "Enfoque del grupo" de su página
 * @property string|null $paises      JSON: códigos de país de la visión de expansión (listaPaises())
 * @property string $mostrar_oportunidades  '1' muestra el bloque "Oportunidades" en su página
 * @property string|null $oportunidades  texto de ese bloque (vacío = texto general)
 * @property string|null $descripcion   HTML ya limpio (HtmlPurifier) al guardar
 * @property string|null $icono
 * @property string|null $foto_portada
 * @property string|null $foto_contenido
 * @property string|null $foto_banner
 * @property string $estado              '1' activo (se ve en la web) / '0' inactivo
 * @property string $destacado           '1' prioritario 2026–2035: sale en el Inicio, en Estrategia y primero en la web
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
            [['subtitulo'], 'filter', 'filter' => fn($v) => trim((string) $v) === '' ? null : trim((string) $v)],
            [['subtitulo'], 'string', 'max' => 120],
            [['resumen'], 'string', 'max' => 500],
            [['numero'], 'integer', 'min' => 0, 'max' => 999],
            [['numero'], 'default', 'value' => 0],
            [['descripcion'], 'string'],
            [['enfoque', 'oportunidades'], 'filter', 'filter' => fn($v) => trim((string) $v) === '' ? null : trim((string) $v)],
            [['enfoque'], 'string', 'max' => 1500],
            [['oportunidades'], 'string', 'max' => 1000],
            [['mostrar_oportunidades'], 'in', 'range' => ['0', '1']],
            [['nombre'], 'unique', 'message' => 'Ya existe un ecosistema con ese nombre.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idecosistema' => 'ID',
            'numero' => 'Número',
            'nombre' => 'Título',
            'subtitulo' => 'Subtítulo',
            'etiquetas' => 'Etiquetas',
            'enfoque' => 'Enfoque del grupo',
            'paises' => 'Presencia en la visión de expansión',
            'mostrar_oportunidades' => 'Mostrar el bloque "Oportunidades"',
            'oportunidades' => 'Texto de "Oportunidades"',
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

    /** Máximo de etiquetas y largo de cada una (banner de su página). */
    const ETIQUETAS_MAX = 12;
    const ETIQUETA_LARGO = 40;

    /** @return string[] etiquetas del banner, en el orden en que se cargaron */
    public function listaEtiquetas(): array
    {
        $lista = json_decode((string) $this->etiquetas, true);
        return is_array($lista) ? array_values(array_filter($lista, 'is_string')) : [];
    }

    /** @return string[] códigos de país (app\widgets\MapaPaises) */
    public function listaPaises(): array
    {
        $lista = json_decode((string) $this->paises, true);
        return is_array($lista) ? array_values(array_filter($lista, 'is_string')) : [];
    }

    /** Para la web: activos, primero los destacados y después por número. */
    public static function paraWeb()
    {
        return static::find()->where(['estado' => '1'])->orderBy(['destacado' => SORT_DESC, 'numero' => SORT_ASC]);
    }

    /** Para el Inicio y Estrategia → Sectores prioritarios: solo los destacados activos. */
    public static function destacados()
    {
        return static::find()->where(['estado' => '1', 'destacado' => '1'])->orderBy(['numero' => SORT_ASC]);
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
