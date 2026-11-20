<?php

namespace app\models\bloques;

use app\components\HtmlEditor;
use yii\base\Model;

/**
 * Base de los bloques de la página de proyecto. Cada tipo declara sus campos en campos() y con eso:
 *  - el panel arma el formulario solo (modules/admin/views/paginas-proyecto/_bloque_form.php),
 *  - se validan y limpian los datos (validarCampo),
 *  - se sabe qué imágenes usa (para copiarlas o borrarlas).
 * La vista de la web de cada tipo está en views/proyectos/bloques/<tipo>.php.
 *
 * Tipos de campo:
 *  texto    una línea                 ['max' => 120]
 *  parrafo  varias líneas, sin formato ['max' => 1500]
 *  html     texto con negrita, cursiva, listas y enlaces (se limpia con HtmlEditor::limpiarSimple)
 *  enlace   https://…, /ruta o #ancla
 *  opciones ['opciones' => [valor => texto]] (radio en el panel)
 *  casilla  sí / no
 *  imagen   nombre del archivo (se sube aparte, ver asignarImagen)
 *  numero   número con decimales (coma o punto) ['min' => 0]
 *  lineas   una cosa por línea (etiquetas, viñetas); se lee con Bloque::lineas()
 *  tabla    ['columnas' => [...], 'filas' => [[...], ...]] (ver BloqueTabla::celda para negrita y chips)
 *  elegir   ids de la biblioteca del proyecto ['fuente' => 'fotos' | 'videos' | 'documentos']
 *  lista    filas repetibles ['campos' => [...], 'min' => 1, 'maxItems' => 12, 'item' => 'Foto']
 * Comunes: 'etiqueta', 'ayuda', 'requerido', 'defecto', 'placeholder',
 *          'si' => 'mostrar:elegidas' (en el panel el campo se ve solo con esa opción marcada).
 */
abstract class Bloque extends Model
{
    /** Clave del tipo (la misma que en Catalogo::TIPOS y que el nombre de la vista). */
    const TIPO = '';
    const NOMBRE = '';
    const DESCRIPCION = '';
    /** Grupo del catálogo: basicos | datos | tarjetas | pasos | proyecto */
    const GRUPO = 'basicos';
    /** Ícono de Font Awesome 4 (panel). */
    const ICONO = 'square-o';
    /** true = toma los datos del proyecto (documentos, condiciones de la ficha). */
    const AUTOMATICO = false;

    /** Límites del campo tabla. */
    const TABLA_MAX_COLUMNAS = 8;
    const TABLA_MAX_FILAS = 80;
    const TABLA_MAX_CELDA = 300;

    /** Archivos de imagen válidos (los que genera SubidaImagen). */
    const PATRON_IMAGEN = '/^[A-Za-z0-9_-]+\.(jpe?g|png|webp)$/';

    private array $valores = [];

    abstract public static function campos(): array;

    public function __construct(array $datos = [], $config = [])
    {
        foreach (static::campos() as $nombre => $c) {
            $this->valores[$nombre] = $c['defecto'] ?? self::vacio($c);
        }
        $this->cargarDatos($datos);
        parent::__construct($config);
    }

    /* ---------- Atributos dinámicos (los campos) ---------- */

    public function attributes()
    {
        return array_keys(static::campos());
    }

    public function __get($name)
    {
        return array_key_exists($name, $this->valores) ? $this->valores[$name] : parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->valores)) {
            $this->valores[$name] = $value;
            return;
        }
        parent::__set($name, $value);
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->valores) ? $this->valores[$name] !== null : parent::__isset($name);
    }

    public function attributeLabels()
    {
        return array_map(fn($c) => $c['etiqueta'] ?? '', static::campos());
    }

    /** Carga solo los campos conocidos (lo demás se ignora). */
    public function cargarDatos(array $datos): void
    {
        foreach (static::campos() as $nombre => $c) {
            if (array_key_exists($nombre, $datos)) {
                $v = $datos[$nombre];
                $this->valores[$nombre] = $c['tipo'] === 'lista' ? array_values(array_filter((array) $v, 'is_array')) : $v;
            }
        }
    }

    /** Lo que se guarda en JSON. */
    public function datos(): array
    {
        return $this->valores;
    }

    /* ---------- Validación ---------- */

    public function rules()
    {
        $reglas = [];
        foreach (static::campos() as $nombre => $c) {
            $reglas[] = [$nombre, 'validarCampo', 'params' => $c, 'skipOnEmpty' => false];
        }
        return array_merge($reglas, $this->reglas());
    }

    /** Reglas propias del tipo (además de las de los campos). */
    protected function reglas(): array
    {
        return [];
    }

    public function validarCampo($atributo, $c)
    {
        if ($c['tipo'] === 'lista') {
            $this->validarLista($atributo, $c);
            return;
        }
        $this->$atributo = self::limpiar($c, $this->$atributo);
        if (($e = self::error($c, $this->$atributo)) !== null) {
            $this->addError($atributo, $e);
        } elseif ($c['tipo'] === 'elegir' && !empty($c['si']) && !$this->$atributo && $this->condicion($c)) {
            $this->addError($atributo, 'Marque al menos uno en «' . ($c['etiqueta'] ?? $atributo) . '», o elija mostrar todos.');
        }
    }

    private function validarLista($atributo, array $c)
    {
        $items = [];
        foreach ((array) $this->$atributo as $fila) {
            if (!is_array($fila)) {
                continue;
            }
            $item = [];
            foreach ($c['campos'] as $sub => $cs) {
                $item[$sub] = self::limpiar($cs, $fila[$sub] ?? ($cs['defecto'] ?? self::vacio($cs)));
            }
            if (self::filaVacia($c['campos'], $item)) {
                continue; // filas sin nada escrito no cuentan
            }
            $items[] = $item;
        }
        $this->$atributo = $items;

        $nombreItem = mb_strtolower($c['item'] ?? 'ítem');
        $min = $c['min'] ?? (!empty($c['requerido']) ? 1 : 0);
        if (count($items) < $min) {
            $this->addError($atributo, 'Agregue al menos ' . self::cantidad($min, $nombreItem) . '.');
        }
        if (isset($c['maxItems']) && count($items) > $c['maxItems']) {
            $this->addError($atributo, 'Máximo ' . self::cantidad($c['maxItems'], $nombreItem) . '.');
        }
        foreach ($items as $i => $item) {
            foreach ($c['campos'] as $sub => $cs) {
                if (($e = self::error($cs, $item[$sub])) !== null) {
                    $this->addError($atributo, ($c['item'] ?? 'Ítem') . ' ' . ($i + 1) . ': ' . $e);
                }
            }
        }
    }

    /** Valor normalizado según el tipo de campo. */
    public static function limpiar(array $c, $v)
    {
        switch ($c['tipo']) {
            case 'html':
                return HtmlEditor::limpiarSimple($v);
            case 'casilla':
                return $v === true || $v === 1 || $v === '1' || $v === 'on';
            case 'imagen':
                $v = trim((string) $v);
                return preg_match(self::PATRON_IMAGEN, $v) ? $v : '';
            case 'parrafo':
                return trim(str_replace("\r\n", "\n", (string) $v));
            case 'opciones':
                $v = trim((string) $v);
                return $v === '' ? (string) array_key_first($c['opciones']) : $v;
            case 'numero':
                $v = str_replace([' ', ','], ['', '.'], trim((string) $v));
                return $v;
            case 'lineas':
                return implode("\n", self::lineas((string) $v));
            case 'elegir':
                return array_values(array_unique(array_filter(array_map('intval', (array) $v))));
            case 'tabla':
                return self::limpiarTabla($v);
            default: // texto, enlace
                return trim((string) $v);
        }
    }

    /** Mensaje de error del valor (ya limpio) o null. */
    public static function error(array $c, $v): ?string
    {
        $etiqueta = $c['etiqueta'] ?? 'este campo';
        if (!empty($c['requerido']) && self::estaVacio($c, $v)) {
            return match ($c['tipo']) {
                'imagen' => 'Falta la imagen.',
                'tabla' => 'La tabla no tiene filas con datos.',
                default => 'Complete «' . $etiqueta . '».',
            };
        }
        if (isset($c['max']) && in_array($c['tipo'], ['texto', 'parrafo', 'enlace'], true) && mb_strlen($v) > $c['max']) {
            return '«' . $etiqueta . '»: máximo ' . $c['max'] . ' caracteres.';
        }
        if ($c['tipo'] === 'numero' && $v !== '') {
            if (!is_numeric($v)) {
                return '«' . $etiqueta . '»: escriba solo el número (ej. 128 o 12,5).';
            }
            if (isset($c['min']) && (float) $v < $c['min']) {
                return '«' . $etiqueta . '»: tiene que ser ' . $c['min'] . ' o más.';
            }
        }
        if ($c['tipo'] === 'lineas' && isset($c['max']) && mb_strlen($v) > $c['max']) {
            return '«' . $etiqueta . '»: máximo ' . $c['max'] . ' caracteres en total.';
        }
        if ($c['tipo'] === 'opciones' && $v !== '' && !array_key_exists($v, $c['opciones'])) {
            return '«' . $etiqueta . '»: elija una opción de la lista.';
        }
        if ($c['tipo'] === 'enlace' && $v !== '' && !preg_match('~^(https?://[^\s<>"]+|/[^\s<>"]*|#[A-Za-z0-9_-]+|mailto:[^\s<>"]+)$~', $v)) {
            return '«' . $etiqueta . '»: tiene que empezar con https://, con / o con #.';
        }
        return null;
    }

    private static function vacio(array $c)
    {
        return match ($c['tipo']) {
            'lista', 'elegir' => [],
            'tabla' => ['columnas' => [], 'filas' => []],
            'casilla' => false,
            'opciones' => (string) array_key_first($c['opciones']),
            default => '',
        };
    }

    public static function estaVacio(array $c, $v): bool
    {
        if ($c['tipo'] === 'html') {
            return trim(html_entity_decode(strip_tags((string) $v), ENT_QUOTES | ENT_HTML5, 'UTF-8'), " \t\n\r\0\x0B\xC2\xA0") === '';
        }
        if (in_array($c['tipo'], ['lista', 'elegir'], true)) {
            return !$v;
        }
        if ($c['tipo'] === 'tabla') {
            return empty($v['filas']);
        }
        return $c['tipo'] === 'casilla' ? false : trim((string) $v) === '';
    }

    private static function filaVacia(array $campos, array $item): bool
    {
        foreach ($campos as $sub => $cs) {
            if (!in_array($cs['tipo'], ['casilla', 'opciones'], true) && !self::estaVacio($cs, $item[$sub])) {
                return false;
            }
        }
        return true;
    }

    /* ---------- Imágenes ---------- */

    /** Nombres de archivo de todas las imágenes que usa el bloque. */
    public function imagenes(): array
    {
        $lista = [];
        foreach (static::campos() as $nombre => $c) {
            if ($c['tipo'] === 'imagen' && $this->valores[$nombre] !== '') {
                $lista[] = $this->valores[$nombre];
            } elseif ($c['tipo'] === 'lista') {
                foreach ($c['campos'] as $sub => $cs) {
                    if ($cs['tipo'] === 'imagen') {
                        foreach ((array) $this->valores[$nombre] as $item) {
                            if (!empty($item[$sub])) {
                                $lista[] = $item[$sub];
                            }
                        }
                    }
                }
            }
        }
        return array_values(array_unique($lista));
    }

    /** Cambia los nombres de las imágenes (al copiar un bloque o una plantilla): $mapa[viejo] = nuevo. */
    public function renombrarImagenes(array $mapa): void
    {
        foreach (static::campos() as $nombre => $c) {
            if ($c['tipo'] === 'imagen') {
                $this->valores[$nombre] = $mapa[$this->valores[$nombre]] ?? $this->valores[$nombre];
            } elseif ($c['tipo'] === 'lista') {
                foreach ($c['campos'] as $sub => $cs) {
                    if ($cs['tipo'] === 'imagen') {
                        foreach ($this->valores[$nombre] as $i => $item) {
                            if (isset($item[$sub], $mapa[$item[$sub]])) {
                                $this->valores[$nombre][$i][$sub] = $mapa[$item[$sub]];
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Pone una imagen recién subida en su campo. $ruta: "foto" o "fotos.2.imagen" (lista, fila, campo).
     * @return bool false si la ruta no es un campo de imagen de este bloque.
     */
    public function asignarImagen(string $ruta, string $archivo): bool
    {
        $partes = explode('.', $ruta);
        $campos = static::campos();
        if (count($partes) === 1) {
            if (($campos[$partes[0]]['tipo'] ?? null) !== 'imagen') {
                return false;
            }
            $this->valores[$partes[0]] = $archivo;
            return true;
        }
        [$nombre, $i, $sub] = array_pad($partes, 3, null);
        if (($campos[$nombre]['tipo'] ?? null) !== 'lista' || ($campos[$nombre]['campos'][$sub]['tipo'] ?? null) !== 'imagen' || !ctype_digit((string) $i)) {
            return false;
        }
        $lista = (array) $this->valores[$nombre];
        $lista[(int) $i][$sub] = $archivo;
        $this->valores[$nombre] = $lista;
        return true;
    }

    /* ---------- Panel ---------- */

    /** Una línea para la tarjeta del bloque en el panel. */
    public function resumen(): string
    {
        // primero lo que más dice del contenido: texto largo, después las listas, al final los títulos cortos
        foreach (['html', 'parrafo', 'tabla', 'lista', 'lineas', 'texto'] as $tipo) {
            foreach (static::campos() as $nombre => $c) {
                $v = $this->valores[$nombre];
                if ($c['tipo'] !== $tipo || self::estaVacio($c, $v)) {
                    continue;
                }
                if ($tipo === 'lista') {
                    return self::cantidad(count($v), mb_strtolower($c['item'] ?? 'ítem'));
                }
                if ($tipo === 'tabla') {
                    return 'Tabla: ' . self::cantidad(count($v['filas']), 'fila') . ' × ' . self::cantidad(count($v['columnas']), 'columna');
                }
                $plano = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace('<', ' <', (string) $v)), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                return mb_strimwidth($plano, 0, 140, '…');
            }
        }
        return static::AUTOMATICO ? 'Se arma solo con los datos del proyecto.' : '';
    }

    /** Texto de un campo "lineas" → lista sin vacías. */
    public static function lineas(?string $texto): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $texto)), 'strlen'));
    }

    /**
     * Tabla limpia: textos recortados, todas las filas con la misma cantidad de celdas que columnas,
     * sin filas vacías, con los límites de columnas, filas y largo de celda.
     */
    public static function limpiarTabla($v): array
    {
        if (is_string($v)) { // el panel la manda en JSON (editor de grilla)
            $v = json_decode($v, true);
        }
        $v = is_array($v) ? $v : [];
        $txt = fn($x) => mb_substr(trim(str_replace(["\r", "\n", "\t"], ' ', (string) $x)), 0, self::TABLA_MAX_CELDA);
        $columnas = array_slice(array_map($txt, array_values((array) ($v['columnas'] ?? []))), 0, self::TABLA_MAX_COLUMNAS);
        $filas = [];
        foreach (array_values((array) ($v['filas'] ?? [])) as $fila) {
            $fila = array_map($txt, array_values((array) $fila));
            $ancho = max(count($columnas), count($fila));
            $fila = array_slice(array_pad($fila, $ancho, ''), 0, self::TABLA_MAX_COLUMNAS);
            if (implode('', $fila) !== '') {
                $filas[] = $fila;
            }
        }
        $n = max(count($columnas), $filas ? max(array_map('count', $filas)) : 0);
        $columnas = array_pad($columnas, $n, '');
        $filas = array_map(fn($f) => array_pad($f, $n, ''), array_slice($filas, 0, self::TABLA_MAX_FILAS));
        return ['columnas' => $columnas, 'filas' => $filas];
    }

    /** Campos "elegir" del bloque: nombre => fuente (fotos | videos | documentos). */
    public static function camposElegir(): array
    {
        $r = [];
        foreach (static::campos() as $nombre => $c) {
            if ($c['tipo'] === 'elegir') {
                $r[$nombre] = $c['fuente'];
            }
        }
        return $r;
    }

    /** ¿Se cumple la condición 'si' del campo ("mostrar:elegidas,elegidos")? Sin 'si', siempre. */
    public function condicion(array $c): bool
    {
        if (empty($c['si'])) {
            return true;
        }
        [$campo, $valores] = explode(':', $c['si'], 2);
        $v = $this->valores[$campo] ?? null;
        $v = is_bool($v) ? ($v ? '1' : '0') : (string) $v;
        return in_array($v, explode(',', $valores), true);
    }

    /**
     * En una plantilla no hay fotos, videos ni documentos para elegir: los "elegir" quedan vacíos y la opción
     * que los pide ("Solo las que marco") vuelve a la primera ("Todas").
     */
    public function sinElegir(): void
    {
        foreach (static::campos() as $nombre => $c) {
            if ($c['tipo'] !== 'elegir') {
                continue;
            }
            if (!empty($c['si'])) {
                $campo = explode(':', $c['si'], 2)[0];
                if ($this->condicion($c)) {
                    $this->valores[$campo] = (string) array_key_first(static::campos()[$campo]['opciones']);
                }
            }
            $this->valores[$nombre] = [];
        }
    }

    /** Deja en los campos "elegir" solo ids que existen: $permitidos[fuente] = [id, …]. */
    public function filtrarElegidos(array $permitidos): void
    {
        foreach (static::camposElegir() as $nombre => $fuente) {
            $this->valores[$nombre] = array_values(array_intersect((array) $this->valores[$nombre], $permitidos[$fuente] ?? []));
        }
    }

    /**
     * Elementos de la biblioteca del proyecto que muestra el bloque, en el orden de la pestaña:
     * todos, o solo los marcados en el campo "elegir" ($campo). $id: atributo id de los modelos.
     */
    protected function elegidosDe(array $todos, string $campo, string $id): array
    {
        $c = static::campos()[$campo];
        if (!$this->condicion($c)) {
            return $todos;
        }
        $marcados = array_flip(array_map('intval', (array) $this->valores[$campo]));
        return array_values(array_filter($todos, fn($m) => isset($marcados[(int) $m->$id])));
    }

    /** "1 foto", "3 fotos", "2 botones". */
    public static function cantidad(int $n, string $item): string
    {
        if ($n === 1) {
            return '1 ' . $item;
        }
        $plural = preg_match('/[aeiouáéó]$/u', $item) ? $item . 's' : (str_ends_with($item, 'ón') ? mb_substr($item, 0, -2) . 'ones' : $item . 'es');
        return $n . ' ' . $plural;
    }

    /** ¿Tiene textos guía de plantilla sin reemplazar? (un texto, párrafo o ítem que es entero «…») */
    public function tieneGuias(): bool
    {
        $hay = false;
        array_walk_recursive($this->valores, function ($v) use (&$hay) {
            $hay = $hay || (is_string($v) && self::esGuia($v));
        });
        return $hay;
    }

    public static function esGuia(?string $texto): bool
    {
        return $texto !== null && preg_match('/(^|>)\s*«[^«»]*»\s*(<|$)/u', $texto) === 1;
    }
}
