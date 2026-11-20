<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Sección de la página web de un proyecto (o de una plantilla: una de las dos columnas va llena).
 * En la web: número automático (si es "numerada"), antetítulo, título y sus bloques; el nombre corto
 * va al menú de anclas si tiene "en_menu". Lo edita PaginaProyectoService.
 *
 * @property int $idseccion
 * @property int|null $idproyecto
 * @property int|null $idplantilla
 * @property int $orden
 * @property string|null $antetitulo
 * @property string|null $titulo
 * @property string|null $menu       nombre corto del menú de anclas
 * @property string $ancla           id del HTML (#sintesis), único dentro de la página
 * @property string $fondo           blanco | gris | negro
 * @property string $diseno          arriba | columna
 * @property string $en_menu         '1' | '0'
 * @property string $numerada        '1' | '0'
 * @property string $visible         '1' | '0'
 * @property string $fecha_registro
 * @property string|null $fecha_actualizacion
 *
 * @property ProyectoBloque[] $bloques
 */
class ProyectoSeccion extends ActiveRecord
{
    const FONDOS = ['blanco' => 'Blanco', 'gris' => 'Gris claro', 'negro' => 'Negro'];
    const DISENOS = [
        'arriba' => 'Título arriba, bloques debajo',
        'columna' => 'Título en la columna izquierda (los bloques de la derecha quedan al lado)',
    ];

    /** Número en la web (01, 02…): lo pone PaginaProyecto al armar la página; null = sin número. */
    public $numero;

    public static function tableName()
    {
        return 'proyecto_seccion';
    }

    public function rules()
    {
        return [
            [['antetitulo', 'titulo', 'menu'], 'filter', 'filter' => fn($v) => ($v = trim((string) $v)) === '' ? null : $v],
            [['antetitulo'], 'string', 'max' => 120],
            [['titulo'], 'string', 'max' => 200],
            [['menu'], 'string', 'max' => 40],
            [['titulo'], 'required', 'when' => fn($m) => $m->antetitulo !== null, 'message' => 'Si pone antetítulo, ponga también el título.'],
            [['menu'], 'required', 'when' => fn($m) => $m->en_menu === '1' && $m->titulo === null,
                'message' => 'Escriba el nombre para el menú (o un título).'],
            [['fondo'], 'in', 'range' => array_keys(self::FONDOS)],
            [['diseno'], 'in', 'range' => array_keys(self::DISENOS)],
            [['en_menu', 'numerada', 'visible'], 'in', 'range' => ['0', '1']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'antetitulo' => 'Antetítulo',
            'titulo' => 'Título',
            'menu' => 'Nombre en el menú',
            'fondo' => 'Fondo',
            'diseno' => 'Diseño',
            'en_menu' => 'Mostrar en el menú de anclas',
            'numerada' => 'Numerar (01, 02…)',
            'visible' => 'Visible en la web',
        ];
    }

    public function getBloques()
    {
        return $this->hasMany(ProyectoBloque::class, ['idseccion' => 'idseccion'])->orderBy(['orden' => SORT_ASC, 'idbloque' => SORT_ASC]);
    }

    /** Lo que se ve en el menú de anclas. */
    public function nombreMenu(): string
    {
        return $this->menu ?? (string) $this->titulo;
    }

    /** Para el panel: "Síntesis", o el título, o "(sin título)". */
    public function nombrePanel(): string
    {
        return $this->menu ?? $this->titulo ?? '(sin título)';
    }

    /** Antetítulo de la web con el número: "01 · Síntesis ejecutiva". */
    public function antetituloWeb(): ?string
    {
        $num = $this->numero !== null ? sprintf('%02d', $this->numero) : null;
        $partes = array_filter([$num, $this->antetitulo], fn($v) => $v !== null && $v !== '');
        return $partes ? implode(' · ', $partes) : null;
    }

    public function tieneEncabezado(): bool
    {
        return $this->titulo !== null || $this->antetitulo !== null;
    }

    /**
     * Bloques en filas para la web: los de ancho completo van solos; los de columna que vienen seguidos
     * forman una fila de dos columnas (así se pueden mezclar en la misma sección). Un bloque "izquierda"
     * que viene después de uno "derecha" empieza otra fila (izq, der, izq, der = dos filas parejas).
     * @param ProyectoBloque[] $bloques
     * @return array [['completo' => ProyectoBloque] | ['izquierda' => ProyectoBloque[], 'derecha' => ProyectoBloque[]]]
     */
    public static function filas(array $bloques): array
    {
        $filas = [];
        $fila = null;
        foreach ($bloques as $b) {
            if (!$b->enColumna()) {
                if ($fila !== null) {
                    $filas[] = $fila;
                    $fila = null;
                }
                $filas[] = ['completo' => $b];
                continue;
            }
            if ($fila !== null && $b->ancho === 'izquierda' && $fila['derecha']) {
                $filas[] = $fila;
                $fila = null;
            }
            $fila ??= ['izquierda' => [], 'derecha' => []];
            $fila[$b->ancho][] = $b;
        }
        if ($fila !== null) {
            $filas[] = $fila;
        }
        return $filas;
    }

    /** ¿El título va arriba de la columna izquierda de la primera fila? (diseño "columna" y la primera fila es de columnas) */
    public static function tituloEnColumna(self $s, array $filas): bool
    {
        return $s->diseno === 'columna' && isset($filas[0]['izquierda']);
    }

    /**
     * Lo que falta en la página web para poder publicar el proyecto (lo usa ProyectoService al publicar).
     * @return string[]
     */
    public static function faltantesParaPublicar(int $idproyecto): array
    {
        $secciones = static::find()->with('bloques')->where(['idproyecto' => $idproyecto, 'visible' => '1'])->orderBy('orden')->all();
        $conContenido = false;
        $conGuias = [];
        foreach ($secciones as $s) {
            $visibles = array_filter($s->bloques, fn($b) => $b->visible === '1');
            $conContenido = $conContenido || $visibles;
            $guia = \app\models\bloques\Bloque::esGuia($s->titulo) || \app\models\bloques\Bloque::esGuia($s->antetitulo);
            foreach ($visibles as $b) {
                $guia = $guia || ($b->contenido() && $b->contenido()->tieneGuias());
            }
            if ($guia) {
                $conGuias[] = $s->nombrePanel();
            }
        }
        $f = [];
        if (!$conContenido) {
            $f[] = 'la página web (al menos una sección visible con bloques)';
        }
        if ($conGuias) {
            $f[] = 'los textos guía de la plantilla («…») en ' . implode(', ', $conGuias);
        }
        return $f;
    }
}
