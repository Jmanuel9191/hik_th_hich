<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Solicitud de inversor: la misma para la web (escenario "web", con contraseña, términos y documento
 * obligatorio) y para el panel (escenario "admin", documento opcional, sin contraseña: se le manda un
 * correo para que la cree). La guarda InversorService.
 */
class SolicitudInversorForm extends Model
{
    public $tipo = 'persona';
    public $nombre;
    public $documento_tipo = 'ci';
    public $documento_numero;
    /** @var UploadedFile|null */
    public $documento;
    public $pais;
    public $ciudad;
    public $email;
    public $celular;
    public $razon_social;
    public $nit;
    public $cargo;
    public $sitio_web;
    public $intereses = [];
    public $monto_minimo;
    public $monto_maximo;
    /** Moneda del monto (Configuración → Monedas, solo activas). */
    public $idmoneda;

    /** Error del correo cuando ya tiene una cuenta (el registro lo muestra con "Ingresar" y "Recuperar contraseña"). */
    const CORREO_REPETIDO = 'Ese correo ya tiene una cuenta. Si es suyo, ingrese o recupere su contraseña.';

    /** ¿El error del correo es que ya existe una cuenta con él? */
    public function correoRepetido(): bool
    {
        return $this->getFirstError('email') === self::CORREO_REPETIDO;
    }
    /** Moneda que ya tenía el inversor (edición en el panel): sigue siendo válida aunque esté inactiva. */
    public $idmonedaActual;

    public function init()
    {
        parent::init();
        if ($this->idmoneda === null) { // por defecto, dólares (si existe)
            $usd = Moneda::find()->select('idmoneda')->where(['codigo' => 'USD', 'estado' => '1'])->scalar();
            $this->idmoneda = $usd ? (string) $usd : null;
        }
    }
    public $plazo_preferido;
    public $participacion_preferida = 'indistinto';
    public $como_nos_conocio;
    public $mensaje;
    public $contrasena;
    public $contrasena2;
    public $terminos;
    /** Trampa para robots: un humano no la ve ni la completa. */
    public $sitio_hp;
    /**
     * Trampa de tiempo (antispam): hora en que se abrió el formulario, firmada por el servidor (no se puede falsificar).
     * Si se envía en menos de SEGUNDOS_MINIMOS es un robot: ninguna persona llena el formulario tan rápido.
     */
    public $inicio;
    const SEGUNDOS_MINIMOS = 4;

    /** Valor firmado para el campo oculto "inicio" (se genera cada vez que se muestra el formulario). */
    public static function marcaInicio(): string
    {
        return \Yii::$app->security->hashData((string) time(), \Yii::$app->request->cookieValidationKey);
    }
    /** Panel: aprobar en el mismo paso (solo con permiso inversores_aprobar). */
    public $aprobar_ahora;

    /** Al editar: el usuario que se edita (para que el email no choque consigo mismo). */
    public $idusuario;

    /** Países sugeridos (se puede escribir otro). */
    const PAISES = ['Bolivia', 'Argentina', 'Brasil', 'Chile', 'Colombia', 'Ecuador', 'Paraguay', 'Perú', 'Uruguay', 'Venezuela',
        'México', 'Estados Unidos', 'Canadá', 'España', 'Alemania', 'Francia', 'Italia', 'Reino Unido', 'Suiza',
        'China', 'Japón', 'Corea del Sur', 'Emiratos Árabes Unidos'];

    public function scenarios()
    {
        $comunes = ['tipo', 'nombre', 'documento_tipo', 'documento_numero', 'documento', 'pais', 'ciudad', 'email', 'celular',
            'razon_social', 'nit', 'cargo', 'sitio_web', 'intereses', 'monto_minimo', 'monto_maximo', 'idmoneda', 'plazo_preferido',
            'participacion_preferida', 'como_nos_conocio', 'mensaje'];
        return [
            'web' => array_merge($comunes, ['contrasena', 'contrasena2', 'terminos', 'sitio_hp', 'inicio']),
            'admin' => array_merge($comunes, ['aprobar_ahora']),
        ];
    }

    public function rules()
    {
        $esEmpresa = fn($m) => $m->tipo === 'empresa';
        $esEmpresaJs = "function () { return $('[name$=\"[tipo]\"]:checked').val() === 'empresa'; }";
        return [
            [['nombre', 'documento_numero', 'pais', 'ciudad', 'email', 'celular', 'razon_social', 'nit', 'cargo', 'sitio_web',
                'como_nos_conocio', 'mensaje'], 'filter', 'filter' => fn($v) => trim(preg_replace('/[ \t]+/u', ' ', (string) $v))],
            [['email'], 'filter', 'filter' => 'mb_strtolower'],
            [['tipo', 'nombre', 'documento_tipo', 'documento_numero', 'pais', 'ciudad', 'email', 'celular', 'monto_minimo', 'monto_maximo', 'idmoneda', 'plazo_preferido'], 'required'],
            [['idmoneda'], 'in', 'range' => fn() => array_map('strval', array_keys(Moneda::lista($this->idmonedaActual))), 'message' => 'Elija una moneda de la lista.'],
            [['intereses'], 'required', 'message' => 'Elija al menos un área de interés.'],
            [['razon_social', 'nit', 'cargo'], 'required', 'when' => $esEmpresa, 'whenClient' => $esEmpresaJs],
            [['tipo'], 'in', 'range' => array_keys(Inversor::TIPOS)],
            [['documento_tipo'], 'in', 'range' => array_keys(Inversor::DOCUMENTOS)],
            [['monto_minimo', 'monto_maximo'], 'filter', 'filter' => fn($v) => is_string($v) ? str_replace([' ', ','], '', $v) : $v],
            [['monto_minimo', 'monto_maximo'], 'number', 'min' => 1, 'max' => Inversor::MONTO_MAXIMO, 'tooSmall' => 'Tiene que ser mayor a 0.'],
            [['monto_maximo'], 'compare', 'compareAttribute' => 'monto_minimo', 'operator' => '>=', 'type' => 'number',
                'message' => 'Tiene que ser igual o mayor al monto mínimo.'],
            [['plazo_preferido'], 'in', 'range' => array_keys(Inversor::PLAZOS)],
            [['participacion_preferida'], 'in', 'range' => array_keys(Inversor::PARTICIPACIONES)],
            [['nombre'], 'string', 'min' => 5, 'max' => 100],
            [['documento_numero', 'nit'], 'string', 'max' => 40],
            [['documento_numero', 'nit'], 'match', 'pattern' => '/^[A-Za-z0-9.\-\/ ]+$/', 'message' => 'Solo letras, números, puntos y guiones.'],
            [['pais'], 'string', 'max' => 50],
            [['ciudad'], 'string', 'max' => 50],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 100],
            [['email'], 'unique', 'targetClass' => Usuarios::class, 'targetAttribute' => 'email',
                'filter' => fn($q) => $this->idusuario ? $q->andWhere(['<>', 'idusuario', (int) $this->idusuario]) : $q,
                'message' => self::CORREO_REPETIDO],
            [['celular'], 'string', 'max' => 30],
            [['celular'], 'match', 'pattern' => '/^\+?[0-9 ()\-]{7,25}$/', 'message' => 'Escríbalo con el código de país. Ej.: +591 70000000'],
            [['razon_social'], 'string', 'max' => 200],
            [['cargo'], 'string', 'max' => 100],
            [['sitio_web'], 'url', 'defaultScheme' => 'https', 'validSchemes' => ['http', 'https']],
            [['sitio_web'], 'string', 'max' => 255],
            [['intereses'], 'each', 'rule' => ['exist', 'targetClass' => Ecosistema::class, 'targetAttribute' => 'idecosistema']],
            [['como_nos_conocio'], 'string', 'max' => 150],
            [['mensaje'], 'string', 'max' => 1000],
            [['documento'], 'file', 'skipOnEmpty' => $this->scenario !== 'web', 'extensions' => array_keys(Inversor::FORMATOS_DOCUMENTO),
                'checkExtensionByMimeType' => false, 'maxSize' => Inversor::MAX_DOCUMENTO_MB * 1024 * 1024,
                'uploadRequired' => 'Suba una foto o PDF de su documento de identidad.',
                'tooBig' => 'El archivo pesa más de ' . Inversor::MAX_DOCUMENTO_MB . ' MB.',
                'wrongExtension' => 'Formato no permitido: JPG, PNG, WEBP o PDF.'],
            // Solo web
            [['contrasena', 'contrasena2'], 'required', 'on' => 'web'],
            [['contrasena'], 'string', 'max' => 72, 'on' => 'web'],
            [['contrasena'], 'match', 'on' => 'web', 'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/',
                'message' => 'Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.'],
            [['contrasena2'], 'compare', 'compareAttribute' => 'contrasena', 'on' => 'web', 'message' => 'Las contraseñas no coinciden.'],
            [['terminos'], 'required', 'on' => 'web', 'requiredValue' => '1', 'message' => 'Debe aceptar los términos y la política de privacidad.'],
            [['sitio_hp'], 'string', 'on' => 'web'],
            [['inicio'], function ($atributo) {
                $hora = is_string($this->inicio) ? \Yii::$app->security->validateData($this->inicio, \Yii::$app->request->cookieValidationKey) : false;
                if ($hora === false || !ctype_digit($hora)) {
                    $this->addError($atributo, 'El formulario venció o llegó incompleto. Vuelva a enviarlo.');
                } elseif (time() - (int) $hora < self::SEGUNDOS_MINIMOS) {
                    $this->addError($atributo, 'Se envió demasiado rápido. Espere unos segundos y vuelva a enviarlo.');
                }
            }, 'on' => 'web', 'skipOnEmpty' => false],
            [['aprobar_ahora'], 'boolean', 'on' => 'admin'],
        ];
    }

    public function attributeLabels()
    {
        $empresa = $this->tipo === 'empresa';
        return [
            'tipo' => 'Invierto como',
            'nombre' => $empresa ? 'Nombre completo del representante' : 'Nombre completo',
            'documento_tipo' => 'Documento',
            'documento_numero' => 'Número de documento',
            'documento' => 'Foto o PDF del documento de identidad',
            'pais' => 'País',
            'ciudad' => 'Ciudad',
            'email' => 'Correo electrónico',
            'celular' => 'Celular (WhatsApp)',
            'razon_social' => 'Razón social',
            'nit' => 'NIT / registro de la empresa',
            'cargo' => 'Cargo en la empresa',
            'sitio_web' => 'Sitio web (opcional)',
            'intereses' => 'Áreas de interés',
            'idmoneda' => 'Moneda',
            'monto_minimo' => 'Monto mínimo a invertir',
            'monto_maximo' => 'Monto máximo a invertir',
            'plazo_preferido' => 'Plazo que prefiere',
            'participacion_preferida' => 'Tipo de participación que prefiere',
            'como_nos_conocio' => '¿Cómo nos conoció? (opcional)',
            'mensaje' => 'Mensaje (opcional)',
            'contrasena' => 'Contraseña',
            'contrasena2' => 'Repita la contraseña',
            'terminos' => 'Acepto los términos de uso y la política de privacidad',
            'inicio' => 'Envío',
            'aprobar_ahora' => 'Aprobar ahora (con las áreas de interés)',
        ];
    }

    public function beforeValidate()
    {
        $this->intereses = array_values(array_filter((array) $this->intereses, 'strlen'));
        if (!$this->documento instanceof UploadedFile) { // load() deja "" (input oculto del fileInput)
            $f = UploadedFile::getInstance($this, 'documento');
            $this->documento = $f && $f->error !== UPLOAD_ERR_NO_FILE ? $f : null;
        }
        return parent::beforeValidate();
    }

    /** Carga los datos de un inversor existente (para editar en el panel). */
    public static function desde(Inversor $inv)
    {
        $f = new static(['scenario' => 'admin']);
        $u = $inv->usuario;
        $f->setAttributes([
            'tipo' => $inv->tipo, 'nombre' => $u->nombrecompleto, 'documento_tipo' => $inv->documento_tipo,
            'documento_numero' => $inv->documento_numero, 'pais' => $u->pais, 'ciudad' => $u->ciudad, 'email' => $u->email,
            'celular' => $u->movil, 'razon_social' => $inv->razon_social, 'nit' => $inv->nit, 'cargo' => $u->cargo,
            'sitio_web' => $inv->sitio_web, 'intereses' => array_map('strval', array_column($inv->intereses, 'idecosistema')),
            'monto_minimo' => (string) (float) $inv->monto_minimo, 'monto_maximo' => (string) (float) $inv->monto_maximo, 'idmoneda' => $inv->idmoneda ? (string) $inv->idmoneda : null, 'plazo_preferido' => $inv->plazo_preferido,
            'participacion_preferida' => $inv->participacion_preferida ?: 'indistinto', 'como_nos_conocio' => $inv->como_nos_conocio,
            'mensaje' => $inv->mensaje,
        ], false);
        $f->idusuario = $inv->idusuario;
        $f->idmonedaActual = $inv->idmoneda; // aunque después se haya desactivado, sigue siendo válida para este inversor
        return $f;
    }
}
