<?php

namespace app\models;

use Yii;
use yii\base\Security;
use yii\web\IdentityInterface;
use sadovojav\cutter\behaviors\CutterBehavior;

/**
 * This is the model class for table "usuarios".
 *
 * @property integer $idusuario
 * @property string $nombre
 * @property string $apellido
 * @property string $direccion
 * @property string $telefono
 * @property string $pais
 * @property string $ciudad
 * @property string $email
 * @property string $contrasena
 * @property string $fecha_nacimiento
 * @property string $movil
 * @property string $fecha_registro
 * @property string $estado
 * @property string $sexo
 * @property string $foto_sello
 * @property string|null $foto_perfil   foto del inversor (privada, Mi cuenta)
 */
class Usuarios extends \yii\db\ActiveRecord implements IdentityInterface
{
    /** Países de operación del Grupo (Visión de expansión 2026-2035). */
    const PAISES = ['Bolivia' => 'Bolivia', 'Argentina' => 'Argentina', 'Perú' => 'Perú', 'Brasil' => 'Brasil'];

    /** Ecosistemas a cargo (solo Gerente de área). Se guarda en usuario_ecosistema. */
    public $ecosistemasIds = [];
    /**
     * @inheritdoc
     */
   // var $authKey;

    public static function tableName()
    {
        return 'usuarios';
    }

    /**
     * Sello/firma que cada usuario/doctor puede subir para los
     * resultados de laboratorio. El behavior maneja el recorte (crop)
     * y el guardado del archivo — la vista solo pone el widget Cutter,
     * el controlador no necesita tocar nada manual de subida.
     */
    public function behaviors()
    {
        return [
            'sello' => [
                'class'      => CutterBehavior::className(),
                'attributes' => 'foto_sello',
                'baseDir'    => '/imagen/usuarios',
                'basePath'   => '@webroot',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nombrecompleto',  'idrol', 'telefono', 'email', 'usuario'], 'required'],
            [['email'], 'unique', 'message' => 'Este correo ya se encuentra registrado.'],
            [['usuario'], 'unique', 'message' => 'Este usuario ya se encuentra registrado.'],
            [['fecha_nacimiento', 'fecha_registro', 'cargo'], 'safe'],
            [['intentos_fallidos', 'bloqueado_hasta', 'zona_horaria'], 'safe'],
            [['nombrecompleto', 'contrasena'], 'string', 'max' => 250],
            [['telefono', 'movil'], 'string', 'min' => 8, 'max' => 15],
            [['telefono', 'movil'], 'match', 'pattern' => '/^[0-9]+$/', 'message' => 'Solo se permiten números.'],
            [['direccion', 'email', 'movil'], 'string', 'max' => 100],
            [['telefono', 'pais', 'ciudad'], 'string', 'max' => 50],
            ['contrasena', 'string', 'min' => 8, 'max' => 255], // Asegúrate de que la longitud máxima sea suficiente
            [['contrasena'], 'string', 'min' => 8, 'message' => 'La contraseña debe tener al menos 8 caracteres.'],
            ['contrasena', 'match', 'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&#]).{8,}$/', 'message' => 'La contraseña debe tener al menos 8 caracteres, incluyendo una letra mayúscula, una letra minúscula, un número y un símbolo.'],[['estado'], 'string', 'max' => 1],
            [['sexo'], 'string', 'max' => 10],
            [['pais_operacion'], 'in', 'range' => array_keys(self::PAISES), 'message' => 'Elija un país de la lista.'],
            [['ecosistemasIds'], 'each', 'rule' => ['integer']],
            [['ecosistemasIds'], 'required', 'enableClientValidation' => false,
                'when' => fn($m) => (int) $m->idrol === \app\components\Rol::GERENTE,
                'message' => 'Elija al menos un ecosistema para el Gerente de área.'],
            [['email'], 'email'],
            // NUEVO: sello/firma — opcional (skipOnEmpty), por eso no
            // está en el bloque 'required' de arriba.
            [['foto_sello'], 'file', 'extensions' => 'jpg, jpeg, png', 'mimeTypes' => 'image/jpeg, image/png', 'skipOnEmpty' => true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'idusuario' => 'ID',
            'nombrecompleto' => 'Nombre completo',
            'direccion' => 'Direccion',
            'telefono' => 'Telefono o celular',
            'pais' => 'Pais',
            'ciudad' => 'Ciudad',
            'email' => 'Email',
            'contrasena' => 'Contraseña',
            'fecha_nacimiento' => 'Fecha Nacimiento',
            'movil' => 'Celular / WhatsApp',
            'fecha_registro' => 'Fecha Registro',
            'ultimo_acceso' => 'Último acceso',
            'ultimo_acceso_ip' => 'IP del último acceso',
            'estado' => 'Estado',
            'sexo' => 'Sexo',
            'cargo' => 'Puesto de trabajo',
            'idrol' => 'Tipo de usuario',
            'zona_horaria' => 'Zona horaria',
            'foto_sello' => 'Sello / firma (para resultados)',
            'pais_operacion' => 'País',
            'ecosistemasIds' => 'Ecosistemas a cargo',
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['resetPassword'] = ['contrasena']; // Solo queremos validar este campo
        return $scenarios;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // 🔐 Generar auth_key automáticamente si se está creando el usuario y no lo tiene
            if ($insert && empty($this->auth_key)) {
                $this->generateAuthKey();
            }
            return true;
        }
        return false;
    }

    public function getFechaNacimientoFormateada()
    {
        return $this->fecha_nacimiento ? date("d/m/Y", strtotime($this->fecha_nacimiento)) : null;
    }

    public function getFechaRegistroFormateada()
    {
        return $this->fecha_registro ? date("d/m/Y", strtotime($this->fecha_registro)) : null;
    }

    public function esAdministrador()
    {
        return $this->idrol == \app\models\Roles::getIdPorAlias('administrador');
    }

    public function getRol()
    {
        return $this->hasOne(Roles::class, ['idrol' => 'idrol']);
    }

 




    public static function getTipoUsuario()
    {
        $result =[];
        $prioridad = [
            '1' =>'Administrador',
            '2' =>'Usuario',        
           
        ];
        $result = $prioridad;
        return $result;
    }

    public static function getUsuarioActual()
    {
        if (!Yii::$app->user || Yii::$app->user->isGuest || !Yii::$app->user->identity) {
            return null;
        }

        return self::findOne(Yii::$app->user->identity->idusuario);
    }


   /* public static function getSelectConsultor()
    {
        $result =[];
        $temp = Usuarios::find()->where(['idrol' => '3', 'estado' => '1'])->all();
        foreach ($temp as $item) {
            $result[$item->idusuario]="".$item->nombrecompleto." - ".$item->empresa;
        }
        return $result;
    }*/

    //
    public static function findIdentity($id)
    {
        return static::findOne(['idusuario' => $id, 'estado' => '1']);
    }

    /**
     * Finds user by username
     *
     * @param  string $username
     * @return static|null
     */
    public static function findByUsername($usuario)
    {
        return static::findOne(['email' => $usuario]);
    }

    /**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['accessToken' => $token]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->idusuario;
    }


    /**
     * @return string current user auth key
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Validates password
     * @param  string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->contrasena);
    }


    public static function countries()
    {
        return array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
    }

    public function exist()
    {
        $tmp = $this->find()->where(['email' => $this->email])->count();
        return ($tmp > 0);
    }
    public static function getEstadosUsuario()
    {
        return [
            '1' => 'Activo',
            '2' => 'Inactivo',
            '0'  => 'Bloqueado', // clave vacía como usa en su BD
        ];
    }

    public function esConsultor()
    {
        return $this->idrol == \app\models\Roles::getIdPorAlias('consultor');
    }
    public function esGestor()
    {
        return $this->idrol == \app\models\Roles::getIdPorAlias('gestor');
    }
    public function esConsultorDe($asignacion)
    {
        return $this->idusuario == $asignacion->idconsultor;
    }

    public function esCliente()
    {
        return in_array($this->idrol, ['4']);
    }
    public function esAdmin()
    {
        return in_array($this->idrol, ['1']);
    }

    public static function obtenerTextoEstado($estado)
    {
        $estados = [
            '1' => 'Activo',
            '2' => 'Inactivo',
            '0'  => 'Bloqueado', // Importante: clave vacía para bloqueados
            ''  => 'Bloqueado', // Importante: clave vacía para bloqueados
        ];

        return $estados[$estado] ?? 'Desconocido';
    }

    public function tieneCuentaGoogleVinculada(): bool
    {
        return !empty($this->google_token);
    }


    public function esAdminoGestor()
    {
        return in_array($this->idrol, ['1', '2']);
    }

    /** Ecosistemas asignados (Gerente de área). */
    public function getEcosistemas()
    {
        return $this->hasMany(Ecosistema::class, ['idecosistema' => 'idecosistema'])
            ->viaTable('usuario_ecosistema', ['idusuario' => 'idusuario'])
            ->orderBy(['numero' => SORT_ASC]);
    }
}
