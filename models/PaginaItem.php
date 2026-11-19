<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\widgets\MapaPaises;
use yii\helpers\Html;

/**
 * Ítem de una lista dentro de un bloque (valores, principios, metas, bloques adicionales…).
 * Qué campos usa: la "lista" del bloque en PaginaSeccion::PAGINAS.
 *
 * @property int $iditem
 * @property int $idseccion
 * @property string $titulo
 * @property string|null $texto   HTML limpio (si la lista usa editor) o texto plano
 * @property string|null $cifra   número o dato corto (cifras, período de una fase, indicador)
 * @property string|null $codigo  país del mapa (app\widgets\MapaPaises::PAISES)
 * @property string|null $icono
 * @property string|null $imagen
 * @property int $orden
 * @property string $estado
 * @property int|null $idusuario_registro
 * @property string $fecha_registro
 * @property string|null $fecha_actualizacion
 *
 * @property PaginaSeccion $seccion
 */
class PaginaItem extends ActiveRecord
{
    /** Columnas de texto que se editan desde el formulario. */
    const CAMPOS = ['titulo', 'texto', 'cifra', 'codigo'];
    /** Columnas de imagen. */
    const IMAGENES = ['icono', 'imagen'];

    public static function tableName()
    {
        return 'pagina_item';
    }

    public function rules()
    {
        $reglas = [
            [['titulo'], 'filter', 'filter' => fn($v) => trim((string) $v)],
            [['cifra', 'codigo'], 'filter', 'filter' => fn($v) => trim((string) $v) === '' ? null : trim((string) $v)],
            [['titulo'], 'required'],
            [['titulo'], 'string', 'max' => 255],
            [['texto'], 'string', 'max' => $this->conEditor() ? 60000 : 600],
            [['cifra'], 'string', 'max' => 60],
            [['codigo'], 'in', 'range' => array_keys(MapaPaises::PAISES), 'message' => 'Elegí un país de la lista.'],
            [['codigo'], 'unique', 'targetAttribute' => ['idseccion', 'codigo'], 'message' => 'Ese país ya está en la lista.'],
        ];
        $obligatorios = array_values(array_intersect($this->config()['obligatorios'] ?? [], array_keys($this->campos())));
        if ($this->usa('codigo')) {
            $obligatorios[] = 'codigo';
        }
        if ($obligatorios) {
            $reglas[] = [$obligatorios, 'required'];
        }
        return $reglas;
    }

    public function attributeLabels()
    {
        return array_merge(['titulo' => 'Título', 'texto' => 'Texto', 'cifra' => 'Cifra', 'codigo' => 'País', 'icono' => 'Ícono', 'imagen' => 'Imagen', 'estado' => 'Estado'], $this->campos());
    }

    public function getSeccion()
    {
        return $this->hasOne(PaginaSeccion::class, ['idseccion' => 'idseccion']);
    }

    /** "lista" del bloque al que pertenece. */
    public function config(): array
    {
        return ($this->idseccion ? ($this->seccion->config()['lista'] ?? null) : null) ?? ['campos' => ['titulo' => 'Título']];
    }

    /** campo => etiqueta */
    public function campos(): array
    {
        return $this->config()['campos'];
    }

    public function usa($campo): bool
    {
        return isset($this->campos()[$campo]);
    }

    public function conEditor(): bool
    {
        return !empty($this->config()['editor']);
    }

    public static function url($archivo)
    {
        return PaginaSeccion::url($archivo);
    }

    /** Nombre del país (listas del mapa). */
    public function pais()
    {
        return $this->codigo ? MapaPaises::nombre($this->codigo) : null;
    }

    /** Igual que PaginaSeccion::textoHtml(). */
    public function textoHtml()
    {
        if ($this->texto === null || $this->texto === '') {
            return '';
        }
        return $this->conEditor() ? $this->texto : nl2br(Html::encode($this->texto));
    }
}
