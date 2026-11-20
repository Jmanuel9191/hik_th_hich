<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * Bloque fijo de una página institucional (la web espera cada "clave"; no se crean ni se borran desde el panel).
 * Qué campos usa cada bloque y cómo se llaman en pantalla: const PAGINAS.
 *
 * @property int $idseccion
 * @property string $pagina
 * @property string $clave
 * @property string|null $titulo
 * @property string|null $subtitulo
 * @property string|null $palabra_fondo  palabra gigante en contorno detrás del título
 * @property string|null $frase
 * @property string|null $texto      HTML limpio (si el bloque usa editor) o texto plano
 * @property string|null $imagen
 * @property string|null $imagen_firma  firma manuscrita (Mensaje del CEO)
 * @property string|null $firma
 * @property string|null $firma_detalle
 * @property string $estado
 * @property int|null $idusuario_actualizacion
 * @property string|null $fecha_actualizacion
 *
 * @property PaginaItem[] $items
 * @property PaginaItem[] $itemsActivos
 */
class PaginaSeccion extends ActiveRecord
{
    const CARPETA = 'imagenes/paginas';

    /** Columnas que se editan desde el formulario (además de las imágenes). */
    const CAMPOS = ['titulo', 'palabra_fondo', 'subtitulo', 'frase', 'texto', 'firma', 'firma_detalle'];

    /** Columnas de imagen que puede usar un bloque (cada bloque declara las suyas en "campos"). */
    const IMAGENES = ['imagen', 'imagen_firma'];

    /**
     * Subpáginas de Nosotros en la web (NosotrosController): acción => [nombre en el menú y título, pestaña de Panel → Banners].
     * El orden es el del submenú.
     */
    const NOSOTROS_WEB = [
        'index'                   => ['Quiénes somos', 'nosotros'],
        'proposito-vision-mision' => ['Propósito, Visión y Misión', 'nosotros-pvm'],
        'liderazgo'               => ['Liderazgo', 'nosotros-liderazgo'],
        'ejes-estrategicos'       => ['Ejes estratégicos', 'nosotros-ejes'],
        'gobierno-y-socios'       => ['Gobierno y socios', 'nosotros-gobierno'],
    ];

    /**
     * Estructura de cada página: pestañas → bloques → campos.
     *  campos: columna => [etiqueta, ayuda, obligatorio]
     *  editor: el campo "texto" usa el editor con formato (si no, texto simple con saltos de línea)
     *  medida: tamaño recomendado de la imagen · medidas: columna => tamaño, para las otras imágenes (ej. imagen_firma)
     *  lista:  ítems que se pueden agregar, ordenar y ocultar (ver PaginaItem). ayuda: texto debajo del nombre de la lista
     *          campos: titulo | texto | cifra | codigo (país del mapa) | icono | imagen
     *          editor: texto con formato · obligatorios: campos que no pueden quedar vacíos (además del título)
     *  mapa:   muestra el mapa (app\widgets\MapaPaises) con los países de la lista
     * El orden de las páginas es el del menú lateral (Páginas) y sigue el submenú Nosotros de la web.
     * web: la página pública (botón "Ver en la web") · enlace + permiso: ítem del menú que lleva a otro módulo (sin bloques).
     */
    const PAGINAS = [
        'quienes' => [
            'nombre' => 'Quiénes somos',
            'icono' => 'building-o',
            'web' => ['/nosotros/index'],
            'descripcion' => 'Nosotros → Quiénes somos. El bloque "Quiénes somos", sus tarjetas, los destacados y las cifras también salen en el Inicio.',
            'pestanas' => [
                'quienes' => [
                    'nombre' => 'Quiénes somos',
                    'bloques' => [
                        'historia' => [
                            'nombre' => 'Quiénes somos',
                            'icono' => 'building-o',
                            'ayuda' => 'También se muestra en el Inicio (resumen de Quiénes somos, con los destacados, las cifras y el botón "Leer más"). Lo que quede vacío no se muestra.',
                            'editor' => true,
                            'medida' => '1000 × 1250 px (vertical)',
                            'campos' => [
                                'titulo' => ['Título', 'Texto chico en dorado. Ej.: Quiénes Somos', true],
                                'palabra_fondo' => ['Palabra de fondo', 'Palabra gigante en contorno, detrás del título. Ej.: Ecosistemas', false],
                                'subtitulo' => ['Bajada', 'Título grande. Ej.: De conectar negocios a desarrollar oportunidades.', false],
                                'texto' => ['Texto', '', false],
                                'firma' => ['Texto arriba de la frase', 'Ej.: La tesis estratégica es clara:', false],
                                'frase' => ['Frase destacada', 'Se muestra grande, como cita.', false],
                                'imagen' => ['Imagen', '', false],
                                'firma_detalle' => ['Pie de la foto', 'Ej.: Altiplano de La Paz, Bolivia.', false],
                            ],
                            'lista' => [
                                'nombre' => 'Tarjetas (Conectar · Transformar · Multiplicar)',
                                'singular' => 'tarjeta',
                                'ayuda' => 'Se muestran en el Inicio, debajo del banner, en carrusel: 3 en computadora, 2 en tablet y 1 en celular. Si hay más de las que entran, aparecen flechas y cambian solas cada 4 segundos.',
                                'medida' => '128 × 128 px · SVG o PNG con fondo transparente, en blanco (la tarjeta es negra)',
                                'campos' => ['icono' => 'Ícono (opcional)', 'titulo' => 'Título', 'texto' => 'Texto'],
                            ],
                        ],
                        'destacados' => [
                            'nombre' => 'Destacados',
                            'icono' => 'star-o',
                            'ayuda' => 'Ícono + texto corto, debajo de la frase destacada (en el Inicio y en Nosotros). Ej.: Territorios estratégicos.',
                            'campos' => [],
                            'lista' => [
                                'nombre' => 'Destacados',
                                'singular' => 'destacado',
                                'medida' => '128 × 128 px · SVG o PNG con fondo transparente, en dorado (el fondo es claro)',
                                'campos' => ['icono' => 'Ícono (opcional)', 'titulo' => 'Texto'],
                            ],
                        ],
                        'bloques' => [
                            'nombre' => 'Bloques adicionales',
                            'icono' => 'plus-square-o',
                            'ayuda' => 'Para agregar contenido nuevo sin programar: cada bloque sale al final de la pestaña "Quiénes somos", con su título, su texto y una imagen opcional.',
                            'campos' => [],
                            'lista' => [
                                'nombre' => 'Bloques',
                                'singular' => 'bloque',
                                'editor' => true,
                                'medida' => '1200 × 800 px (horizontal)',
                                'campos' => ['titulo' => 'Título', 'texto' => 'Texto', 'imagen' => 'Imagen (opcional)'],
                            ],
                        ],
                    ],
                ],
                'ceo' => [
                    'nombre' => 'Mensaje del CEO',
                    'bloques' => [
                        'ceo' => [
                            'nombre' => 'Mensaje del CEO',
                            'icono' => 'quote-left',
                            'ayuda' => 'Se muestra en Nosotros → Quiénes somos, como carta: frase arriba, mensaje en dos columnas y al final la foto, la firma, el nombre y el cargo.',
                            'editor' => true,
                            'medida' => '600 × 700 px (vertical)',
                            'medidas' => ['imagen_firma' => '600 × 200 px · PNG con fondo transparente, firma en negro o azul oscuro'],
                            'campos' => [
                                'titulo' => ['Título', '', true],
                                'frase' => ['Frase principal', 'La cita grande.', true],
                                'subtitulo' => ['Origen de la frase', 'Texto chico debajo de la cita (opcional).', false],
                                'texto' => ['Mensaje', '', true],
                                'firma' => ['Nombre', '', true],
                                'firma_detalle' => ['Cargo', '', true],
                                'imagen' => ['Foto del CEO', 'Retrato (no la firma); sale a la izquierda de la firma, al final de la carta.', false],
                                'imagen_firma' => ['Firma (imagen)', 'La firma escaneada; sale arriba del nombre.', false],
                            ],
                        ],
                    ],
                ],
                'cifras' => [
                    'nombre' => 'Cifras',
                    'bloques' => [
                        'cifras' => [
                            'nombre' => 'Cifras',
                            'icono' => 'bar-chart',
                            'ayuda' => 'Números grandes. Se muestran en el Inicio (en el resumen de Quiénes somos, todas las activas) y en Nosotros. Ej.: "10" Ecosistemas, "4" Países. Cargue solo cifras reales.',
                            'campos' => ['titulo' => ['Título (opcional)', 'Ej.: HUK en cifras', false]],
                            'lista' => [
                                'nombre' => 'Cifras',
                                'singular' => 'cifra',
                                'campos' => ['cifra' => 'Cifra', 'titulo' => 'Texto'],
                                'obligatorios' => ['cifra'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'pvm' => [
            'nombre' => 'Propósito, Visión y Misión',
            'icono' => 'compass',
            'web' => ['/nosotros/proposito-vision-mision'],
            'descripcion' => 'Nosotros → Propósito, Visión y Misión (con Valores y Principios debajo).',
            'pestanas' => [
                'pvm' => [
                    'nombre' => 'Propósito, Visión y Misión',
                    'bloques' => [
                        'proposito' => [
                            'nombre' => 'Propósito',
                            'icono' => 'compass',
                            'campos' => ['titulo' => ['Título', '', true], 'texto' => ['Texto', '', true]],
                        ],
                        'vision' => [
                            'nombre' => 'Visión',
                            'icono' => 'eye',
                            'campos' => ['titulo' => ['Título', 'Ej.: Visión 2045', true], 'texto' => ['Texto', '', true]],
                            'lista' => [
                                'nombre' => 'Metas de la visión (01, 02, 03…)',
                                'singular' => 'meta',
                                'campos' => ['titulo' => 'Meta'],
                            ],
                        ],
                        'mision' => [
                            'nombre' => 'Misión',
                            'icono' => 'flag-o',
                            'campos' => ['titulo' => ['Título', '', true], 'texto' => ['Texto', 'Cada renglón se muestra como un párrafo.', true]],
                        ],
                    ],
                ],
                'valores' => [
                    'nombre' => 'Valores y Principios',
                    'bloques' => [
                        'valores' => [
                            'nombre' => 'Valores',
                            'icono' => 'diamond',
                            'campos' => [
                                'titulo' => ['Etiqueta', 'Texto chico en dorado. Ej.: Valores corporativos', true],
                                'frase' => ['Título grande', 'Ej.: Lo que nos guía. Vacío: se usa la etiqueta como título.', false],
                                'subtitulo' => ['Bajada', '', false],
                            ],
                            'lista' => [
                                'nombre' => 'Valores',
                                'singular' => 'valor',
                                'medida' => '200 × 200 px (PNG o SVG con fondo transparente)',
                                'campos' => ['icono' => 'Ícono (opcional)', 'titulo' => 'Valor', 'texto' => 'Texto corto (opcional)'],
                            ],
                        ],
                        'principios' => [
                            'nombre' => 'Principios estratégicos',
                            'icono' => 'list-ol',
                            'campos' => ['titulo' => ['Título', 'Sale en dorado, con la cantidad adelante. Ej.: Principios estratégicos → "10 PRINCIPIOS ESTRATÉGICOS"', true], 'subtitulo' => ['Bajada', '', false]],
                            'lista' => [
                                'nombre' => 'Principios',
                                'singular' => 'principio',
                                'campos' => ['titulo' => 'Principio', 'texto' => 'Texto corto (opcional)'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'liderazgo' => [
            'nombre' => 'Liderazgo',
            'icono' => 'users',
            'enlace' => ['/admin/equipo/index'], // no tiene bloques: la página se arma con el módulo Equipo
            'permiso' => 'equipo_listar',
        ],
        'ejes' => [
            'nombre' => 'Ejes estratégicos',
            'icono' => 'line-chart',
            'web' => ['/nosotros/ejes-estrategicos'],
            'descripcion' => 'Nosotros → Ejes estratégicos: ejes (los Ecosistemas destacados), modelo de creación de valor y visión de expansión con sus fases.',
            'pestanas' => [
                'ejes' => [
                    'nombre' => 'Ejes estratégicos',
                    'bloques' => [
                        'ejes' => [
                            'nombre' => 'Ejes estratégicos de crecimiento',
                            'icono' => 'th-large',
                            'ayuda' => 'Los ejes son los Ecosistemas marcados como destacados (Panel → Ecosistemas), por número: 01, 02, 03… con su nombre y su resumen. Para agregar o quitar uno, márquelo o desmárquelo como destacado.',
                            'campos' => [
                                'titulo' => ['Texto chico (dorado)', 'Ej.: {n} ejes prioritarios de expansión e inversión 2026–2035 — {n} se cambia solo por la cantidad de ejes.', true],
                                'subtitulo' => ['Título', 'Ej.: Ejes Estratégicos de Crecimiento', true],
                            ],
                        ],
                    ],
                ],
                'modelo' => [
                    'nombre' => 'Modelo de valor',
                    'bloques' => [
                        'modelo' => [
                            'nombre' => 'Modelo de creación de valor',
                            'icono' => 'random',
                            'campos' => [
                                'subtitulo' => ['Texto chico (dorado)', 'Ej.: Cómo creamos valor', false],
                                'titulo' => ['Título', '', true],
                                'frase' => ['Fórmula', 'Ej.: Territorio + Recursos + … = Desarrollo. Lo que va después del "=" sale en dorado.', false],
                            ],
                            'lista' => [
                                'nombre' => 'Pasos (01, 02, 03…)',
                                'singular' => 'paso',
                                'campos' => ['titulo' => 'Paso'],
                            ],
                        ],
                        'propuesta_valor' => [
                            'nombre' => 'Propuesta de valor',
                            'icono' => 'handshake-o',
                            'campos' => ['titulo' => ['Título', '', true]],
                            'lista' => [
                                'nombre' => 'Propuestas',
                                'singular' => 'propuesta',
                                'campos' => ['titulo' => 'Título', 'texto' => 'Texto'],
                            ],
                        ],
                    ],
                ],
                'expansion' => [
                    'nombre' => 'Expansión 2026–2035',
                    'bloques' => [
                        'expansion' => [
                            'nombre' => 'Mapa de expansión',
                            'icono' => 'globe',
                            'ayuda' => 'Sale en Nosotros → Ejes estratégicos: a la izquierda la foto (o el mapa) con la cifra; a la derecha la lista de países para elegir.',
                            'mapa' => true,
                            'medida' => '1000 × 1400 px (vertical)',
                            'campos' => [
                                'titulo' => ['Título', '', true],
                                'subtitulo' => ['Región', 'Ej.: HUK South America. Sale debajo de la cifra hasta que se elige un país.', false],
                                'firma' => ['Texto sobre la cifra', 'Ej.: Objetivo de capital movilizado 2026–2035', false],
                                'frase' => ['Cifra destacada', 'Ej.: USD 5 - 10 mil millones', false],
                                'imagen' => ['Foto (opcional)', 'Fondo del bloque en Nosotros → Ejes estratégicos. Sin foto se dibuja el mapa con los países de la lista.', false],
                            ],
                            'lista' => [
                                'nombre' => 'Países',
                                'singular' => 'país',
                                'campos' => ['codigo' => 'País', 'titulo' => 'Enfoque'],
                            ],
                        ],
                        'fases' => [
                            'nombre' => 'Fases',
                            'icono' => 'road',
                            'campos' => ['titulo' => ['Título', 'Ej.: Horizonte 2035 — tres fases', true]],
                            'lista' => [
                                'nombre' => 'Fases (I, II, III…)',
                                'singular' => 'fase',
                                'campos' => ['cifra' => 'Período', 'titulo' => 'Nombre de la fase', 'texto' => 'Descripción (opcional)'],
                                'obligatorios' => ['cifra'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'gobierno' => [
            'nombre' => 'Gobierno y socios',
            'icono' => 'university',
            'web' => ['/nosotros/gobierno-y-socios'],
            'descripcion' => 'Nosotros → Gobierno y socios: gobierno corporativo y ESG, propuesta de valor para socios y por qué asociarse.',
            'pestanas' => [
                'corporativo' => [
                    'nombre' => 'Gobierno corporativo y ESG',
                    'bloques' => [
                        'gobierno' => [
                            'nombre' => 'Principios organizacionales',
                            'icono' => 'university',
                            'ayuda' => 'Tarjeta clara de la izquierda: cada principio sale con un check dorado y el lema abajo, en letra chica.',
                            'campos' => [
                                'firma' => ['Texto chico de la sección', 'Arriba del título, en dorado. Ej.: Gobierno Corporativo y ESG', false],
                                'subtitulo' => ['Título de la sección', 'Ej.: Crecimiento con propósito', false],
                                'titulo' => ['Título de la tarjeta', 'Ej.: Principios organizacionales', true],
                                'frase' => ['Lema', 'Ej.: Centralized governance · Disciplined capital allocation', false],
                            ],
                            'lista' => [
                                'nombre' => 'Principios',
                                'singular' => 'principio',
                                'campos' => ['titulo' => 'Principio'],
                            ],
                        ],
                        'impacto' => [
                            'nombre' => 'Desarrollo sostenible e impacto',
                            'icono' => 'leaf',
                            'ayuda' => 'Tarjeta negra de la derecha: título chico, texto grande, los indicadores como etiquetas y la frase al final.',
                            'campos' => [
                                'titulo' => ['Título', '', true],
                                'texto' => ['Texto', 'Sale grande. Ej.: Cada proyecto incorpora un sistema de medición de impacto:', false],
                                'frase' => ['Frase final', '', false],
                            ],
                            'lista' => [
                                'nombre' => 'Indicadores',
                                'singular' => 'indicador',
                                'campos' => ['titulo' => 'Indicador'],
                            ],
                        ],
                    ],
                ],
                'socios' => [
                    'nombre' => 'Propuesta para socios',
                    'bloques' => [
                        'socios' => [
                            'nombre' => 'Propuesta de valor para socios',
                            'icono' => 'users',
                            'ayuda' => 'Una columna: título, texto y la lista "quién aporta qué" (al pasar el mouse, la fila se pone negra).',
                            'campos' => [
                                'titulo' => ['Texto chico', 'En dorado, arriba del título. Ej.: Propuesta de valor para socios', true],
                                'subtitulo' => ['Título', 'Ej.: Un socio estratégico para crecer', false],
                                'texto' => ['Texto', '', false],
                                'firma' => ['Texto del botón', 'Lleva al registro de inversores. Ej.: Quiero ser socio', false],
                            ],
                            'lista' => [
                                'nombre' => 'Quién aporta qué',
                                'singular' => 'participante',
                                'campos' => ['titulo' => 'Quién', 'texto' => 'Qué aporta'],
                            ],
                        ],
                        'razones' => [
                            'nombre' => '¿Por qué asociarse?',
                            'icono' => 'check-square-o',
                            'ayuda' => 'Debajo de la propuesta: el título y las razones en tarjetas de 2 en 2, numeradas 01, 02…',
                            'campos' => ['titulo' => ['Título', '', true]],
                            'lista' => [
                                'nombre' => 'Razones',
                                'singular' => 'razón',
                                'campos' => ['titulo' => 'Razón'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'general' => [
            'nombre' => 'Pie de página',
            'icono' => 'columns',
            'descripcion' => 'Texto y lemas del pie: salen en todas las páginas de la web.',
            'pestanas' => [
                'pie' => [
                    'nombre' => 'Pie de página',
                    'bloques' => [
                        'pie' => [
                            'nombre' => 'Texto y lemas del pie de página',
                            'icono' => 'quote-right',
                            'ayuda' => 'Se muestran en el pie de todas las páginas de la web.',
                            'campos' => [
                                'texto' => ['Texto (debajo del logo)', 'Ej.: Plataforma corporativa internacional de desarrollo e inversión.', false],
                                'frase' => ['Frase', 'Ej.: Sembramos progreso, extraemos futuro.', false],
                                'subtitulo' => ['Lema', 'Ej.: One vision • Infinite opportunities', false],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    public static function tableName()
    {
        return 'pagina_seccion';
    }

    public function rules()
    {
        $reglas = [
            [['titulo', 'palabra_fondo', 'subtitulo', 'frase', 'firma', 'firma_detalle'], 'filter', 'filter' => fn($v) => trim((string) $v) === '' ? null : trim((string) $v)],
            [['titulo'], 'string', 'max' => 200],
            [['subtitulo', 'frase'], 'string', 'max' => 300],
            [['firma', 'firma_detalle'], 'string', 'max' => 150],
            [['palabra_fondo'], 'string', 'max' => 60],
            [['texto'], 'string', 'max' => 60000],
        ];
        $obligatorios = array_keys(array_filter($this->campos(), fn($c, $campo) => $c[2] && !in_array($campo, self::IMAGENES, true), ARRAY_FILTER_USE_BOTH));
        if ($obligatorios) {
            $reglas[] = [$obligatorios, 'required'];
        }
        return $reglas;
    }

    public function attributeLabels()
    {
        $etiquetas = ['titulo' => 'Título', 'palabra_fondo' => 'Palabra de fondo', 'subtitulo' => 'Bajada', 'frase' => 'Frase', 'texto' => 'Texto', 'imagen' => 'Imagen', 'imagen_firma' => 'Firma (imagen)',
            'firma' => 'Nombre', 'firma_detalle' => 'Cargo', 'estado' => 'Estado'];
        foreach ($this->campos() as $campo => $c) {
            $etiquetas[$campo] = $c[0];
        }
        return $etiquetas;
    }

    public function getItems()
    {
        return $this->hasMany(PaginaItem::class, ['idseccion' => 'idseccion'])->orderBy(['orden' => SORT_ASC, 'iditem' => SORT_ASC]);
    }

    public function getItemsActivos()
    {
        return $this->getItems()->andWhere(['estado' => '1']);
    }

    /* ---------- Estructura (const PAGINAS) ---------- */

    /** Configuración de este bloque. */
    public function config(): array
    {
        foreach (self::PAGINAS[$this->pagina]['pestanas'] ?? [] as $idPestana => $p) {
            if (isset($p['bloques'][$this->clave])) {
                return $p['bloques'][$this->clave] + ['pestana' => $idPestana];
            }
        }
        return ['nombre' => $this->clave, 'campos' => [], 'pestana' => null];
    }

    /** campo => [etiqueta, ayuda, obligatorio] */
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

    /* ---------- Para mostrar ---------- */

    public static function carpeta()
    {
        return Yii::getAlias('@webroot/' . self::CARPETA);
    }

    public static function url($archivo)
    {
        return $archivo ? Yii::getAlias('@web/' . self::CARPETA . '/' . $archivo) : null;
    }

    /** HTML listo para imprimir: el del editor ya viene limpio; el texto simple se escapa y cada renglón es un párrafo. */
    public function textoHtml()
    {
        if ($this->texto === null || $this->texto === '') {
            return '';
        }
        if ($this->conEditor()) {
            return $this->texto;
        }
        $renglones = array_filter(array_map('trim', preg_split('/\R/u', $this->texto)), 'strlen');
        return implode('', array_map(fn($r) => Html::tag('p', Html::encode($r)), $renglones));
    }

    /**
     * Para la web: los bloques activos de una página, por clave, con sus ítems activos.
     * Ej.: $pvm = PaginaSeccion::dePagina('pvm'); $pvm['vision']->itemsActivos · también varias: dePagina(['quienes', 'pvm'])
     * @return PaginaSeccion[]
     */
    public static function dePagina($pagina)
    {
        return static::find()->with('itemsActivos')->where(['pagina' => $pagina, 'estado' => '1'])->indexBy('clave')->all();
    }
}
