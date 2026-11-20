<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Miembro del equipo (se muestra en la web).
 *
 * @property int $idmiembro
 * @property string $nombre
 * @property string $puesto
 * @property string|null $telefono
 * @property string|null $email
 * @property string|null $linkedin
 * @property string|null $facebook
 * @property string|null $instagram
 * @property string|null $twitter
 * @property string $biografia  resumen de la persona, HTML limpio (HtmlEditor); obligatorio
 * @property string|null $foto
 * @property int $orden
 * @property string $estado
 * @property string $destacado  '1' sale en el Inicio y primero en Liderazgo
 * @property int|null $idusuario_registro
 * @property string $fecha_registro
 * @property string|null $fecha_actualizacion
 */
class Equipo extends ActiveRecord
{
    const CARPETA = 'imagenes/equipo';
    const MEDIDA_FOTO = '600 × 700 px (vertical)';
    /** Largo máximo del resumen (texto sin formato): así las tarjetas de Liderazgo quedan parejas. */
    const BIO_MAX = 500;

    /** campo => [etiqueta, ícono Font Awesome 4] */
    const REDES = [
        'linkedin' => ['LinkedIn', 'linkedin'],
        'facebook' => ['Facebook', 'facebook'],
        'instagram' => ['Instagram', 'instagram'],
        'twitter' => ['X (Twitter)', 'twitter'],
    ];

    public static function tableName()
    {
        return 'equipo';
    }

    public function rules()
    {
        $redes = array_keys(self::REDES);
        return [
            [array_merge(['nombre', 'puesto', 'telefono', 'email'], $redes), 'filter', 'filter' => fn($v) => trim((string) $v) === '' ? null : trim((string) $v)],
            [['nombre', 'puesto'], 'required'],
            [['biografia'], 'required', 'message' => 'Escriba un resumen corto de la persona: sale en Nosotros → Liderazgo.'],
            [['biografia'], 'validarLargoBio'],
            [['nombre', 'puesto', 'email'], 'string', 'max' => 150],
            [['telefono'], 'string', 'max' => 50],
            [['telefono'], 'match', 'pattern' => '/^[0-9+()\s\-]+$/', 'message' => 'Solo números, espacios, +, ( ) y guiones.'],
            [['email'], 'email'],
            [$redes, 'string', 'max' => 255],
            [$redes, 'url', 'validSchemes' => ['https', 'http'], 'defaultScheme' => 'https',
                'message' => 'Pegue el enlace completo del perfil. Ej.: https://www.linkedin.com/in/nombre'],
        ];
    }

    /** El resumen se cuenta sin las etiquetas del editor. */
    public function validarLargoBio($attr)
    {
        $largo = mb_strlen(self::textoPlano($this->$attr));
        if ($largo > self::BIO_MAX) {
            $this->addError($attr, 'El resumen es muy largo (' . $largo . ' caracteres). Máximo ' . self::BIO_MAX . '.');
        }
    }

    /** Texto sin etiquetas ni espacios repetidos (para contar y exportar). */
    public static function textoPlano($html): string
    {
        return trim(preg_replace('/[\s\x{00A0}]+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    public function attributeLabels()
    {
        return [
            'idmiembro' => 'N°',
            'nombre' => 'Nombre completo',
            'puesto' => 'Puesto en la empresa',
            'telefono' => 'Teléfono',
            'email' => 'Email',
            'biografia' => 'Resumen (biografía)',
            'foto' => 'Foto',
            'orden' => 'Orden',
            'estado' => 'Estado',
            'destacado' => 'Destacado',
            'fecha_registro' => 'Creado',
            'fecha_actualizacion' => 'Última modificación',
        ] + array_map(fn($r) => $r[0], self::REDES);
    }

    public static function carpeta()
    {
        return Yii::getAlias('@webroot/' . self::CARPETA);
    }

    public static function url($archivo)
    {
        return $archivo ? Yii::getAlias('@web/' . self::CARPETA . '/' . $archivo) : null;
    }

    /** Para la web: activos, primero los destacados y después en el orden de la lista. */
    public static function activos()
    {
        return static::find()->where(['estado' => '1'])->orderBy(['destacado' => SORT_DESC, 'orden' => SORT_ASC, 'idmiembro' => SORT_ASC]);
    }

    /** Para el Inicio y Liderazgo: solo los destacados activos. */
    public static function destacados()
    {
        return static::activos()->andWhere(['destacado' => '1']);
    }
}
