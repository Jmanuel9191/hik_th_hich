<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Banner de la portada.
 *
 * @property int $idbanner
 * @property string|null $titulo
 * @property string|null $texto
 * @property string|null $boton_texto
 * @property string|null $boton_url
 * @property string $imagen
 * @property string|null $imagen_movil
 * @property int $orden
 * @property string $estado  '1' activo, '0' inactivo
 * @property string|null $fecha_inicio
 * @property string|null $fecha_fin
 * @property int|null $idusuario_registro
 * @property string $fecha_registro
 * @property string|null $fecha_actualizacion
 */
class Banners extends ActiveRecord
{
    const CARPETA = 'imagenes/banners';

    /** Campo => [etiqueta, medida recomendada] */
    const IMAGENES = [
        'imagen' => ['Imagen para computadora', '1920 × 600 px'],
        'imagen_movil' => ['Imagen para celular', '800 × 1000 px'],
    ];

    public static function tableName()
    {
        return 'banners';
    }

    public function rules()
    {
        return [
            [['titulo', 'texto', 'boton_texto', 'boton_url', 'fecha_inicio', 'fecha_fin'], 'filter', 'filter' => fn($v) => trim((string) $v) === '' ? null : trim((string) $v)],
            [['titulo'], 'string', 'max' => 150],
            [['texto'], 'string', 'max' => 400],
            [['boton_texto'], 'string', 'max' => 60],
            [['boton_url'], 'string', 'max' => 500],
            [['boton_url'], 'validarUrl'],
            [['boton_url'], 'required', 'when' => fn($m) => $m->boton_texto !== null, 'message' => 'Si el botón tiene texto, poné a dónde lleva.'],
            [['boton_texto'], 'required', 'when' => fn($m) => $m->boton_url !== null, 'message' => 'Si ponés un enlace, escribí el texto del botón.'],
            [['fecha_inicio', 'fecha_fin'], 'date', 'format' => 'php:Y-m-d', 'message' => 'Fecha no válida.'],
            [['fecha_fin'], 'compare', 'compareAttribute' => 'fecha_inicio', 'operator' => '>=', 'type' => 'string',
                'when' => fn($m) => $m->fecha_inicio !== null, 'message' => 'La fecha de fin no puede ser antes del inicio.'],
        ];
    }

    /** Enlace del botón: de la misma web (/contacto, #seccion) o completo (https://...). */
    public function validarUrl($attr)
    {
        $url = $this->$attr;
        if ($url === null) {
            return;
        }
        if (preg_match('#^(/(?!/)|\#)[^\s"<>]*$#', $url)) {
            return;
        }
        if (!preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
            $url = 'https://' . $url;
        }
        if (!preg_match('#^https?://[a-z0-9.-]+\.[a-z]{2,}(:\d+)?([/?\#][^\s"<>]*)?$#i', $url)) {
            $this->addError($attr, 'Enlace no válido. Ej.: /contacto o https://www.sitio.com');
            return;
        }
        $this->$attr = $url;
    }

    public function attributeLabels()
    {
        return [
            'idbanner' => 'N°',
            'titulo' => 'Título',
            'texto' => 'Texto descriptivo',
            'boton_texto' => 'Texto del botón',
            'boton_url' => 'Enlace del botón',
            'imagen' => 'Imagen para computadora',
            'imagen_movil' => 'Imagen para celular',
            'orden' => 'Orden',
            'estado' => 'Estado',
            'fecha_inicio' => 'Mostrar desde',
            'fecha_fin' => 'Mostrar hasta',
            'fecha_registro' => 'Creado',
            'fecha_actualizacion' => 'Última modificación',
        ];
    }

    public static function carpeta()
    {
        return Yii::getAlias('@webroot/' . self::CARPETA);
    }

    public static function url($archivo)
    {
        return $archivo ? Yii::getAlias('@web/' . self::CARPETA . '/' . $archivo) : null;
    }

    /** Nombre para mensajes e historial. */
    public function nombre()
    {
        return $this->titulo ?: 'Banner N° ' . $this->idbanner;
    }

    /** activo | inactivo | programado (empieza más adelante) | vencido (ya terminó) */
    public function situacion()
    {
        if ($this->estado !== '1') {
            return 'inactivo';
        }
        $hoy = date('Y-m-d');
        if ($this->fecha_inicio !== null && $this->fecha_inicio > $hoy) {
            return 'programado';
        }
        if ($this->fecha_fin !== null && $this->fecha_fin < $hoy) {
            return 'vencido';
        }
        return 'activo';
    }

    /** El enlace es de otro sitio: en la web se abre en otra pestaña. */
    public function enlaceExterno()
    {
        if ($this->boton_url === null || !preg_match('#^https?://#i', $this->boton_url)) {
            return false;
        }
        $host = parse_url($this->boton_url, PHP_URL_HOST);
        $actual = Yii::$app->request instanceof \yii\web\Request ? Yii::$app->request->hostName : null;
        return strcasecmp((string) $host, (string) $actual) !== 0;
    }

    /** Para la web pública: los que se muestran hoy, en orden. */
    public static function vigentes()
    {
        $hoy = date('Y-m-d');
        return static::find()
            ->where(['estado' => '1'])
            ->andWhere(['or', ['fecha_inicio' => null], ['<=', 'fecha_inicio', $hoy]])
            ->andWhere(['or', ['fecha_fin' => null], ['>=', 'fecha_fin', $hoy]])
            ->orderBy(['orden' => SORT_ASC, 'idbanner' => SORT_ASC]);
    }
}
