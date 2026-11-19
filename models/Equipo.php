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
 * @property string|null $biografia  HTML limpio (HtmlEditor)
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
            [['nombre', 'puesto', 'email'], 'string', 'max' => 150],
            [['telefono'], 'string', 'max' => 50],
            [['telefono'], 'match', 'pattern' => '/^[0-9+()\s\-]+$/', 'message' => 'Solo números, espacios, +, ( ) y guiones.'],
            [['email'], 'email'],
            [$redes, 'string', 'max' => 255],
            [$redes, 'url', 'validSchemes' => ['https', 'http'], 'defaultScheme' => 'https',
                'message' => 'Pegá el enlace completo del perfil. Ej.: https://www.linkedin.com/in/nombre'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idmiembro' => 'N°',
            'nombre' => 'Nombre completo',
            'puesto' => 'Puesto en la empresa',
            'telefono' => 'Teléfono',
            'email' => 'Email',
            'biografia' => 'Biografía',
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
