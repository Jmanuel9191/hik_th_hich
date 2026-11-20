<?php

namespace app\models;

use app\models\bloques\Bloque;
use app\models\bloques\Catalogo;
use yii\db\ActiveRecord;

/**
 * Bloque de una sección: tipo (ver app\models\bloques\Catalogo), ancho y sus datos en JSON.
 * Los datos se leen y validan con la clase del tipo: $bloque->contenido() devuelve ese modelo ya cargado.
 *
 * @property int $idbloque
 * @property int $idseccion
 * @property int $orden
 * @property string $tipo
 * @property string $ancho      completo | izquierda | derecha
 * @property string|null $datos JSON
 * @property string $visible    '1' | '0'
 * @property string $fecha_registro
 * @property string|null $fecha_actualizacion
 *
 * @property ProyectoSeccion $seccion
 */
class ProyectoBloque extends ActiveRecord
{
    const ANCHOS = [
        'completo' => 'Ancho completo',
        'izquierda' => 'Columna izquierda',
        'derecha' => 'Columna derecha',
    ];

    private ?Bloque $contenido = null;

    public static function tableName()
    {
        return 'proyecto_bloque';
    }

    public function rules()
    {
        return [
            [['tipo'], 'in', 'range' => array_keys(Catalogo::TIPOS)],
            [['ancho'], 'in', 'range' => array_keys(self::ANCHOS)],
            [['visible'], 'in', 'range' => ['0', '1']],
        ];
    }

    public function getSeccion()
    {
        return $this->hasOne(ProyectoSeccion::class, ['idseccion' => 'idseccion']);
    }

    /** Modelo del tipo con los datos guardados (null si el tipo ya no existe). */
    public function contenido(): ?Bloque
    {
        if ($this->contenido === null && isset(Catalogo::TIPOS[$this->tipo])) {
            $this->contenido = Catalogo::crear($this->tipo, json_decode((string) $this->datos, true) ?: []);
        }
        return $this->contenido;
    }

    /** Guarda en "datos" lo que tiene el modelo del tipo. */
    public function setContenido(Bloque $b): void
    {
        $this->contenido = $b;
        $this->datos = json_encode($b->datos(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function enColumna(): bool
    {
        return $this->ancho !== 'completo';
    }
}
