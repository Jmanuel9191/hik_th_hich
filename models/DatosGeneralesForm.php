<?php

namespace app\models;

use yii\base\Model;

/**
 * Formulario "Datos generales" (se guarda en la tabla configuracion).
 * Es un formulario aparte para no tocar las reglas del modelo Configuracion.
 */
class DatosGeneralesForm extends Model
{
    /** Atributo del formulario => columna de configuracion */
    const COLUMNAS = [
        'titulo_pagina' => 'titulo_pagina',
        'nombre_empresa' => 'nombre_empresa',
        'email' => 'email',
        'email_web' => 'email_web',
         'meta_descripcion' => 'meta_descripcion',
        
        'direccion_empresa' => 'direccion_empresa',
        'horarios' => 'horarios',
        'telefono_empresa' => 'telefono_empresa',
        'whatsapp' => 'fax_empresa',
        'telefonos_otros' => 'telefonos_otros',
        'facebook' => 'facebook',
        'instagram' => 'instagram',
        'tiktok' => 'tiktok',
        'youtube' => 'youtube',
        'linkedin' => 'LinkedIn',
        'twitter' => 'twitter',
        'mapa' => 'mapa_embed',
    ];

    const REDES = ['facebook', 'instagram', 'tiktok', 'youtube', 'linkedin', 'twitter'];

    /** Único formato de mapa que se acepta: el enlace de "Insertar un mapa" de Google Maps. */
    const PATRON_MAPA = '#^https://www\.google\.com/maps/embed\?pb=[A-Za-z0-9!._%\-:+,*~]+$#';

    public $titulo_pagina;
    public $nombre_empresa;
    public $email;
    public $email_web;
    public $meta_descripcion;
    public $direccion_empresa;
    public $horarios;
    public $telefono_empresa;
    public $whatsapp;
    public $telefonos_otros;
    public $facebook;
    public $instagram;
    public $tiktok;
    public $youtube;
    public $linkedin;
    public $twitter;
    public $mapa;

    public function rules()
    {
        $telefono = '/^[0-9+()\s\-]+$/';
        return [
            [array_keys(self::COLUMNAS), 'filter', 'filter' => fn($v) => trim((string) $v)],
            [['titulo_pagina', 'nombre_empresa', 'email'], 'required'],
            [['titulo_pagina', 'nombre_empresa'], 'string', 'max' => 200],
            [['email'], 'string', 'max' => 100],
            [['email_web'], 'string', 'max' => 500],
            [['email', 'email_web'], 'email'],
            [['direccion_empresa', 'telefono_empresa'], 'string', 'max' => 250],
            [['horarios', 'meta_descripcion'], 'string', 'max' => 500],
            [['telefono_empresa'], 'match', 'pattern' => $telefono, 'message' => 'Solo números, espacios, +, ( ) y guiones.'],
            [['whatsapp'], 'match', 'pattern' => '/^[0-9]{8,15}$/', 'message' => 'Solo números, con código de país. Ej.: 59175095874'],
            [['telefonos_otros'], 'string', 'max' => 500],
            [['telefonos_otros'], function ($attr) use ($telefono) {
                foreach (preg_split('/\R/', $this->$attr) as $linea) {
                    if (trim($linea) !== '' && !preg_match($telefono, trim($linea))) {
                        $this->addError($attr, 'Revisá "' . trim($linea) . '": solo números, espacios, +, ( ) y guiones (uno por renglón).');
                        return;
                    }
                }
            }],
            [self::REDES, 'string', 'max' => 255],
            [self::REDES, 'url', 'validSchemes' => ['https', 'http'], 'defaultScheme' => 'https',
                'message' => 'Pegá el enlace completo del perfil. Ej.: https://www.facebook.com/hukglobal'],
            [['mapa'], 'validarMapa'],
        ];
    }

    /** Acepta el HTML de "Insertar un mapa" o solo su enlace; se queda únicamente con el enlace. */
    public function validarMapa($attr)
    {
        if ($this->$attr === '') {
            return;
        }
        $enlace = self::extraerMapa($this->$attr);
        if ($enlace === null) {
            $this->addError($attr, preg_match('#goo\.gl|maps\.app|/maps/place|/maps/@#i', $this->$attr)
                ? 'Ese es el enlace de "Compartir": Google no deja mostrarlo dentro de la página. Usá Compartir → Insertar un mapa → Copiar HTML.'
                : 'No es un mapa de Google válido. En Google Maps: Compartir → Insertar un mapa → Copiar HTML, y pegalo acá.');
            return;
        }
        if (strlen($enlace) > 1000) {
            $this->addError($attr, 'El enlace del mapa es demasiado largo.');
            return;
        }
        $this->$attr = $enlace;
    }

    /** Del HTML del iframe (o de un enlace suelto) devuelve el enlace "embed" de Google, o null. */
    public static function extraerMapa($texto)
    {
        $texto = trim((string) $texto);
        if (preg_match('/\bsrc\s*=\s*["\']([^"\']+)["\']/i', $texto, $m)) {
            $texto = $m[1];
        }
        $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return preg_match(self::PATRON_MAPA, $texto) ? $texto : null;
    }

    public function attributeLabels()
    {
        return [
            'titulo_pagina' => 'Título de la página',
            'nombre_empresa' => 'Nombre de la empresa',
            'email' => 'Email del sistema (remitente)',
            'email_web' => 'Email de contacto',
            'direccion_empresa' => 'Dirección',
            'horarios' => 'Horarios de atención',
            'telefono_empresa' => 'Teléfono principal',
            'whatsapp' => 'WhatsApp',
            'telefonos_otros' => 'Otros teléfonos',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'tiktok' => 'TikTok',
            'youtube' => 'YouTube',
            'linkedin' => 'LinkedIn',
            'twitter' => 'X (Twitter)',
            'mapa' => 'Mapa de Google',
            'meta_descripcion' => 'Descripción para buscadores (meta description)',
            
        ];
    }
}