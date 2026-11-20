<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Banner de una página de la web (const UBICACIONES).
 * Inicio: carrusel grande con antetítulo, texto y botón. Páginas internas: encabezado con breadcrumb y título.
 * Prefooter: cierre arriba del footer (título, línea dorada y botón). Fuera del Inicio se muestra solo el primero vigente.
 *
 * @property int $idbanner
 * @property string $ubicacion  página donde se muestra (const UBICACIONES)
 * @property string|null $titulo
 * @property string|null $antetitulo  texto corto en dorado arriba del título
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
        'imagen' => ['Imagen para computadora', 'Inicio: 1920 × 1000 px · Páginas internas: 1920 × 600 px · Prefooter: 1920 × 800 px'],
        'imagen_movil' => ['Imagen para celular', 'Inicio: 800 × 1000 px · Páginas internas: 800 × 600 px · Prefooter: 800 × 900 px'],
    ];

    /**
     * Dónde puede ir un banner (una pestaña por cada una en el panel).
     *  nombre:   para el formulario y el historial · pestana: nombre corto de la pestaña
     *  campos:   textos que usa (antetitulo, titulo, texto, boton); los demás se vacían al guardar
     *  carrusel: true = se muestran todos rotando (Inicio); false = solo el primero vigente de la lista
     *  ayuda:    qué es cada campo en esa ubicación
     * Las subpáginas de Nosotros coinciden con PaginaSeccion::NOSOTROS_WEB.
     */
    const UBICACIONES = [
        'inicio' => ['nombre' => 'Inicio', 'pestana' => 'Inicio', 'campos' => ['antetitulo', 'titulo', 'texto', 'boton'], 'carrusel' => true,
            'ayuda' => 'Carrusel grande de la portada. Todo es opcional: si lo deja vacío, el banner muestra solo la imagen.'],
        'nosotros' => ['nombre' => 'Nosotros → Quiénes somos', 'pestana' => 'Quiénes somos', 'campos' => ['titulo']],
        'nosotros-pvm' => ['nombre' => 'Nosotros → Propósito, Visión y Misión', 'pestana' => 'Propósito', 'campos' => ['titulo']],
        'nosotros-liderazgo' => ['nombre' => 'Nosotros → Liderazgo', 'pestana' => 'Liderazgo', 'campos' => ['titulo']],
        'nosotros-ejes' => ['nombre' => 'Nosotros → Ejes estratégicos', 'pestana' => 'Ejes', 'campos' => ['titulo']],
        'nosotros-gobierno' => ['nombre' => 'Nosotros → Gobierno y socios', 'pestana' => 'Gobierno', 'campos' => ['titulo']],
        'ecosistemas' => ['nombre' => 'Ecosistemas', 'pestana' => 'Ecosistemas', 'campos' => ['titulo']],
        'proyectos' => ['nombre' => 'Proyectos', 'pestana' => 'Proyectos', 'campos' => ['titulo']],
        'noticias' => ['nombre' => 'Blog (listado y noticias)', 'pestana' => 'Blog', 'campos' => ['titulo'],
            'ayuda' => 'Encabezado del Blog. En el listado sale el título del banner (vacío: "Blog"); dentro de cada noticia, el título de la noticia.'],
        'contacto' => ['nombre' => 'Contacto', 'pestana' => 'Contacto', 'campos' => ['titulo']],
        'cuenta-ingreso' => ['nombre' => 'Acceso inversores (ingreso)', 'pestana' => 'Acceso inversores', 'campos' => ['antetitulo', 'titulo', 'texto'],
            'ayuda' => 'Encabezado de la página de ingreso de los inversores. Antetítulo (arriba, dorado), título y texto: todo opcional. Vacío: "Área privada" / "Acceso inversores".'],
        'cuenta-registro' => ['nombre' => 'Quiero ser socio (registro)', 'pestana' => 'Quiero ser socio', 'campos' => ['antetitulo', 'titulo', 'texto'],
            'ayuda' => 'Encabezado del formulario de solicitud. Antetítulo (arriba, dorado), título y texto: todo opcional. Vacío: "Inversores y socios" / "Quiero ser socio".'],
        'prefooter' => ['nombre' => 'Prefooter (cierre arriba del footer)', 'pestana' => 'Prefooter', 'campos' => ['titulo', 'texto', 'boton'],
            'ayuda' => 'Banner de cierre, arriba del footer, en las páginas que lo tengan (widget Prefooter). Arriba sale el logo. Título = primera línea, en blanco · Texto = segunda línea, en dorado.'],
    ];

    /** Campo de UBICACIONES => columnas que se vacían si la ubicación no lo usa. */
    const COLUMNAS_OPCIONALES = [
        'antetitulo' => ['antetitulo'],
        'texto' => ['texto'],
        'boton' => ['boton_texto', 'boton_url'],
    ];

    /** Ayuda por defecto para las páginas internas (encabezado con breadcrumb). */
    const AYUDA_INTERNA = 'Encabezado de la página: el título sale debajo del breadcrumb. Si lo deja vacío, dice el nombre de la página.';

    public static function tableName()
    {
        return 'banners';
    }

    public function rules()
    {
        return [
            [['ubicacion'], 'required'],
            [['ubicacion'], 'in', 'range' => array_keys(self::UBICACIONES), 'message' => 'Elija una página de la lista.'],
            [['antetitulo', 'titulo', 'texto', 'boton_texto', 'boton_url', 'fecha_inicio', 'fecha_fin'], 'filter', 'filter' => fn($v) => trim((string) $v) === '' ? null : trim((string) $v)],
            [['titulo'], 'string', 'max' => 150],
            [['antetitulo'], 'string', 'max' => 120],
            [['texto'], 'string', 'max' => 400],
            [['boton_texto'], 'string', 'max' => 60],
            [['boton_url'], 'string', 'max' => 500],
            [['boton_url'], 'validarUrl'],
            [['boton_url'], 'required', 'when' => fn($m) => $m->boton_texto !== null, 'message' => 'Si el botón tiene texto, indique a dónde lleva.',
                'whenClient' => "function () { return $.trim($('#banners-boton_texto').val()) !== ''; }"],
            [['boton_texto'], 'required', 'when' => fn($m) => $m->boton_url !== null, 'message' => 'Si pone un enlace, escriba el texto del botón.',
                'whenClient' => "function () { return $.trim($('#banners-boton_url').val()) !== ''; }"],
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
            'ubicacion' => 'Página',
            'antetitulo' => 'Antetítulo',
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

    /** Nombre de la página donde se muestra. */
    public function ubicacionNombre()
    {
        return self::UBICACIONES[$this->ubicacion]['nombre'] ?? $this->ubicacion;
    }

    /** La ubicación del banner usa este campo (antetitulo, titulo, texto, boton). */
    public function usa($campo)
    {
        return in_array($campo, self::UBICACIONES[$this->ubicacion]['campos'] ?? [], true);
    }

    /** true: se muestran todos rotando (Inicio). false: solo el primero vigente de la lista. */
    public function esCarrusel()
    {
        return !empty(self::UBICACIONES[$this->ubicacion]['carrusel']);
    }

    /** Ayuda del formulario para una ubicación. */
    public static function ayuda($ubicacion)
    {
        return self::UBICACIONES[$ubicacion]['ayuda'] ?? self::AYUDA_INTERNA;
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

    /** Para la web pública: los de una página que se muestran hoy, en orden. */
    public static function vigentes($ubicacion = 'inicio')
    {
        $hoy = date('Y-m-d');
        return static::find()
            ->where(['ubicacion' => $ubicacion, 'estado' => '1'])
            ->andWhere(['or', ['fecha_inicio' => null], ['<=', 'fecha_inicio', $hoy]])
            ->andWhere(['or', ['fecha_fin' => null], ['>=', 'fecha_fin', $hoy]])
            ->orderBy(['orden' => SORT_ASC, 'idbanner' => SORT_ASC]);
    }
}
