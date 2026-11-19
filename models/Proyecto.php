<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Proyecto del banco de proyectos (lo ven los inversores aprobados de su área, después del NDA).
 * Dos tipos con la misma ficha: "financiamiento" (tiene proyecto, busca capital) e "inversion"
 * (tiene capital, busca proyecto). Algunos números cambian de nombre o solo existen en un tipo: ETIQUETAS / SOLO_TIPO.
 * Archivos en privado/proyectos/<id>/ (se sirven por BancoProyectosController, con permiso).
 *
 * @property int $idproyecto
 * @property string $codigo         HUK-ENE-2026-001 (automático, no cambia)
 * @property string $slug
 * @property string $titulo
 * @property string $tipo           financiamiento | inversion
 * @property int $idecosistema
 * @property int|null $idgerente    gerente responsable (recibe los avisos)
 * @property string|null $pais
 * @property string|null $ciudad
 * @property string|null $ubicacion
 * @property string|null $etapa           (financiamiento)
 * @property string|null $etapas_interes  (inversion) claves de ETAPAS separadas por coma
 * @property int|null $porcentaje_avance  % de avance (el último informado en Avances, o el de la etapa)
 * @property string $estado         borrador | revision | publicado | cerrado | archivado
 * @property string $resumen
 * @property string|null $descripcion     HTML limpio (HtmlEditor)
 * @property string|null $riesgos         HTML limpio
 * @property string|null $garantias
 * @property string|null $impacto
 * @property int|null $idmoneda
 * @property string|null $monto_total
 * @property string|null $monto_objetivo
 * @property string|null $inversion_minima
 * @property string|null $inversion_maxima
 * @property string|null $roi
 * @property int|null $plazo_meses
 * @property string|null $frecuencia_retorno
 * @property string|null $tipo_participacion
 * @property string|null $fecha_apertura
 * @property string|null $fecha_cierre
 * @property string|null $imagen_banner
 * @property string|null $foto_principal
 * @property int|null $idusuario_registro
 * @property string $fecha_registro
 * @property string|null $fecha_actualizacion
 * @property string|null $fecha_publicacion
 *
 * @property Ecosistema $ecosistema
 * @property Usuarios|null $gerente
 * @property Moneda|null $moneda
 * @property ProyectoImagen[] $imagenes
 * @property ProyectoVideo[] $videos
 * @property ProyectoDocumento[] $documentos
 * @property ProyectoAvance[] $avances
 */
class Proyecto extends ActiveRecord
{
    const TIPOS = [
        'financiamiento' => 'Busca financiamiento',
        'inversion' => 'Busca inversión',
    ];
    const AYUDA_TIPOS = [
        'financiamiento' => 'Hay un proyecto y necesita capital: los inversores ponen la plata.',
        'inversion' => 'Hay capital disponible y busca dónde invertir: HUK lo conecta con proyectos.',
    ];

    /** clave => [etiqueta, clase del label] */
    const ESTADOS = [
        'borrador' => ['Borrador', 'label-default'],
        'revision' => ['En revisión', 'label-warning'],
        'publicado' => ['Publicado', 'label-success'],
        'cerrado' => ['Inversión completa', 'label-info'],
        'archivado' => ['Archivado', 'label-default huk-label-archivado'],
    ];

    const ETAPAS = [
        'idea' => 'Idea',
        'prefactibilidad' => 'Prefactibilidad',
        'factibilidad' => 'Factibilidad',
        'desarrollo' => 'Desarrollo',
        'construccion' => 'Construcción',
        'operacion' => 'Operación',
    ];
    /** % de avance que se sugiere para cada etapa (se puede ajustar en cada avance). */
    const PORCENTAJE_ETAPA = [
        'idea' => 5,
        'prefactibilidad' => 15,
        'factibilidad' => 30,
        'desarrollo' => 50,
        'construccion' => 75,
        'operacion' => 100,
    ];
    const FRECUENCIAS = [
        'mensual' => 'Mensual',
        'trimestral' => 'Trimestral',
        'semestral' => 'Semestral',
        'anual' => 'Anual',
        'al_final' => 'Al final del plazo',
    ];
    const PARTICIPACIONES = [
        'acciones' => 'Acciones / participación societaria',
        'prestamo' => 'Préstamo / deuda',
        'jv' => 'Sociedad conjunta (Joint Venture)',
        'otro' => 'Otro',
    ];

    /** Campos que solo existen en un tipo (en el otro se vacían al guardar). */
    const SOLO_TIPO = [
        'financiamiento' => ['monto_total', 'etapa', 'frecuencia_retorno'],
        'inversion' => ['etapas_interes'],
    ];
    /** Etiquetas que cambian según el tipo: campo => [financiamiento, inversion] */
    const ETIQUETAS = [
        'monto_objetivo' => ['Monto a levantar', 'Capital disponible'],
        'inversion_minima' => ['Inversión mínima por inversor', 'Ticket por proyecto: desde'],
        'inversion_maxima' => ['Inversión máxima por inversor (opcional)', 'Ticket por proyecto: hasta (opcional)'],
        'roi' => ['ROI esperado (% anual)', 'Rentabilidad que busca (% anual)'],
        'plazo_meses' => ['Plazo del proyecto (meses)', 'Plazo que acepta (meses)'],
    ];

    /** Campos que se editan desde el formulario. */
    const CAMPOS = [
        'titulo', 'tipo', 'idecosistema', 'idgerente', 'pais', 'ciudad', 'ubicacion', 'etapa', 'etapas_interes',
        'resumen', 'descripcion', 'riesgos', 'garantias', 'impacto', 'idmoneda', 'monto_total', 'monto_objetivo',
        'inversion_minima', 'inversion_maxima', 'roi', 'plazo_meses', 'frecuencia_retorno', 'tipo_participacion',
        'fecha_apertura', 'fecha_cierre',
    ];
    const IMAGENES = ['imagen_banner' => 'Imagen de banner', 'foto_principal' => 'Foto principal'];
    const MEDIDAS = ['imagen_banner' => '1920 × 600 px (horizontal)', 'foto_principal' => '1200 × 800 px (horizontal)'];

    const CARPETA = 'privado/proyectos';
    /** Límites de subida (MB). */
    const MAX_IMAGEN_MB = 8;
    const MAX_VIDEO_MB = 100;
    const MAX_DOCUMENTO_MB = 20;
    /** Videos subidos como archivo por proyecto (los enlaces no tienen límite). */
    const MAX_VIDEOS_ARCHIVO = 5;

    /** Para el checkbox múltiple del formulario (etapas_interes como lista). */
    public $etapasInteres = [];

    public static function tableName()
    {
        return 'proyecto';
    }

    public function rules()
    {
        $nulo = fn($v) => trim((string) $v) === '' ? null : trim((string) $v);
        return [
            [['titulo', 'resumen'], 'filter', 'filter' => fn($v) => trim((string) $v)],
            [['pais', 'ciudad', 'ubicacion', 'etapa', 'garantias', 'impacto', 'frecuencia_retorno', 'tipo_participacion',
                'fecha_apertura', 'fecha_cierre', 'idgerente', 'idmoneda'], 'filter', 'filter' => $nulo],
            [['monto_total', 'monto_objetivo', 'inversion_minima', 'inversion_maxima', 'roi', 'plazo_meses'], 'filter',
                'filter' => fn($v) => ($v = str_replace([' ', ','], ['', '.'], trim((string) $v))) === '' ? null : $v],
            [['titulo', 'tipo', 'idecosistema', 'resumen'], 'required'],
            [['titulo'], 'string', 'max' => 160],
            [['resumen'], 'string', 'max' => 500],
            [['pais'], 'string', 'max' => 60],
            [['ciudad'], 'string', 'max' => 80],
            [['ubicacion'], 'string', 'max' => 200],
            [['garantias', 'impacto'], 'string', 'max' => 5000],
            [['tipo'], 'in', 'range' => array_keys(self::TIPOS)],
            [['etapa'], 'in', 'range' => array_keys(self::ETAPAS)],
            [['frecuencia_retorno'], 'in', 'range' => array_keys(self::FRECUENCIAS)],
            [['tipo_participacion'], 'in', 'range' => array_keys(self::PARTICIPACIONES)],
            [['idecosistema'], 'exist', 'targetClass' => Ecosistema::class],
            [['idmoneda'], 'exist', 'targetClass' => Moneda::class],
            [['idmoneda'], function ($a) {
                if (!$this->isNewRecord && $this->isAttributeChanged($a, false)
                    && InversionPago::find()->where(['idproyecto' => $this->idproyecto, 'estado' => 'vigente'])->exists()) {
                    $this->addError($a, 'No se puede cambiar la moneda: el proyecto ya tiene pagos registrados en Inversiones.');
                }
            }],
            [['idgerente'], 'integer'],
            [['monto_total', 'monto_objetivo', 'inversion_minima', 'inversion_maxima'], 'number', 'min' => 0, 'max' => 99999999999999,
                'message' => 'Escribí solo el número, sin puntos de miles. Ej.: 1500000 o 1500000.50'],
            [['roi'], 'number', 'min' => 0, 'max' => 9999],
            [['plazo_meses'], 'integer', 'min' => 1, 'max' => 600],
            [['fecha_apertura', 'fecha_cierre'], 'date', 'format' => 'php:Y-m-d'],
            [['fecha_cierre'], 'compare', 'compareAttribute' => 'fecha_apertura', 'operator' => '>=', 'type' => 'date',
                'when' => fn($m) => $m->fecha_apertura && $m->fecha_cierre, 'message' => 'La fecha "hasta" no puede ser antes de la fecha "desde".'],
            [['inversion_maxima'], 'compare', 'compareAttribute' => 'inversion_minima', 'operator' => '>=', 'type' => 'number',
                'when' => fn($m) => $m->inversion_minima !== null && $m->inversion_maxima !== null, 'message' => 'No puede ser menor que el mínimo.'],
            [['etapasInteres'], 'each', 'rule' => ['in', 'range' => array_keys(self::ETAPAS)]],
        ];
    }

    public function attributeLabels()
    {
        $i = $this->tipo === 'inversion' ? 1 : 0;
        return [
            'codigo' => 'Código',
            'titulo' => 'Título',
            'tipo' => 'Tipo',
            'idecosistema' => 'Ecosistema',
            'idgerente' => 'Gerente responsable',
            'pais' => 'País',
            'ciudad' => 'Ciudad',
            'ubicacion' => 'Ubicación (referencia)',
            'etapa' => 'Etapa del proyecto',
            'etapas_interes' => 'Etapas que le interesan',
            'etapasInteres' => 'Etapas que le interesan',
            'estado' => 'Estado',
            'resumen' => 'Resumen',
            'descripcion' => 'Descripción',
            'riesgos' => 'Riesgos y cómo se mitigan',
            'garantias' => 'Garantías',
            'impacto' => 'Impacto esperado',
            'idmoneda' => 'Moneda',
            'monto_total' => 'Monto total del proyecto',
            'frecuencia_retorno' => 'Cada cuánto se paga el retorno',
            'tipo_participacion' => 'Tipo de participación',
            'fecha_apertura' => 'Recibe inversiones desde',
            'fecha_cierre' => 'Recibe inversiones hasta',
            'porcentaje_avance' => 'Avance del proyecto',
            'imagen_banner' => 'Imagen de banner',
            'foto_principal' => 'Foto principal',
            'fecha_registro' => 'Creado',
            'fecha_actualizacion' => 'Última modificación',
            'fecha_publicacion' => 'Publicado el',
        ] + array_map(fn($e) => $e[$i], self::ETIQUETAS);
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->etapasInteres = $this->etapas_interes ? explode(',', $this->etapas_interes) : [];
    }

    /* ---------- Relaciones ---------- */

    public function getEcosistema()
    {
        return $this->hasOne(Ecosistema::class, ['idecosistema' => 'idecosistema']);
    }

    public function getGerente()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idgerente']);
    }

    public function getMoneda()
    {
        return $this->hasOne(Moneda::class, ['idmoneda' => 'idmoneda']);
    }

    public function getImagenes()
    {
        return $this->hasMany(ProyectoImagen::class, ['idproyecto' => 'idproyecto'])->orderBy(['orden' => SORT_ASC, 'idimagen' => SORT_ASC]);
    }

    public function getVideos()
    {
        return $this->hasMany(ProyectoVideo::class, ['idproyecto' => 'idproyecto'])->orderBy(['orden' => SORT_ASC, 'idvideo' => SORT_ASC]);
    }

    public function getPagos()
    {
        return $this->hasMany(InversionPago::class, ['idproyecto' => 'idproyecto']);
    }

    /** Suma de los pagos vigentes, en la moneda del proyecto. */
    public function recaudado()
    {
        return (float) InversionPago::find()->where(['idproyecto' => $this->idproyecto, 'estado' => 'vigente', 'idmoneda_proyecto' => $this->idmoneda])
            ->sum('monto_equivalente');
    }

    /** % de la ronda cubierto (null si no tiene monto objetivo). Puede pasar de 100. */
    public function porcentajeRonda($recaudado = null)
    {
        if (!(float) $this->monto_objetivo) {
            return null;
        }
        return round(($recaudado ?? $this->recaudado()) * 100 / (float) $this->monto_objetivo, 1);
    }

    public function getAvances()
    {
        return $this->hasMany(ProyectoAvance::class, ['idproyecto' => 'idproyecto'])->orderBy(['fecha' => SORT_DESC, 'idavance' => SORT_DESC]);
    }

    public function getDocumentos()
    {
        return $this->hasMany(ProyectoDocumento::class, ['idproyecto' => 'idproyecto'])->orderBy(['iddocumento' => SORT_DESC]);
    }

    /* ---------- Ayudas ---------- */

    public static function carpetaDe($id, $sub = '')
    {
        return Yii::getAlias('@app/' . self::CARPETA . '/' . (int) $id . ($sub !== '' ? '/' . $sub : ''));
    }

    /** URL (protegida) de la imagen de banner o la foto principal. */
    public function urlImagen($campo)
    {
        return $this->$campo ? Url::to(['/admin/banco-proyectos/imagen', 'id' => $this->idproyecto, 'campo' => $campo, 'v' => substr($this->$campo, 0, 6)]) : null;
    }

    public function estadoNombre()
    {
        return self::ESTADOS[$this->estado][0] ?? $this->estado;
    }

    public function estadoClase()
    {
        return self::ESTADOS[$this->estado][1] ?? 'label-default';
    }

    public function etapaNombre()
    {
        return self::ETAPAS[$this->etapa] ?? null;
    }

    public function etapasInteresNombres()
    {
        return array_values(array_intersect_key(self::ETAPAS, array_flip($this->etapasInteres)));
    }

    /** "US$ 1.500.000" (o "—"). */
    public function monto($campo)
    {
        if ($this->$campo === null || $this->$campo === '') {
            return null;
        }
        $n = (float) $this->$campo;
        $texto = number_format($n, fmod($n, 1.0) ? 2 : 0, ',', '.');
        return trim(($this->moneda->simbolo ?? '') . ' ' . $texto);
    }

    /** ¿El campo aplica a este tipo? */
    public function aplica($campo)
    {
        foreach (self::SOLO_TIPO as $tipo => $campos) {
            if (in_array($campo, $campos, true) && $tipo !== $this->tipo) {
                return false;
            }
        }
        return true;
    }

    /** Para la web (más adelante): publicados de los ecosistemas indicados. */
    public static function publicados(array $idsEcosistemas)
    {
        return static::find()->where(['estado' => 'publicado', 'idecosistema' => $idsEcosistemas ?: [0]])
            ->orderBy(['fecha_publicacion' => SORT_DESC]);
    }
}
