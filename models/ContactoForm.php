<?php

namespace app\models;

use yii\base\Model;

/**
 * Formulario de contacto de la web (/contacto). No se guarda en la base: se manda por correo (ContactoService).
 * "sitio" es una trampa para robots: va oculto en la página y una persona nunca lo completa.
 */
class ContactoForm extends Model
{
    public $nombre;
    public $empresa;
    public $telefono;
    public $email;
    public $mensaje;
    public $sitio;

    public function rules()
    {
        return [
            [['nombre', 'empresa', 'telefono', 'email', 'mensaje', 'sitio'], 'filter', 'filter' => fn($v) => trim((string) $v)],
            [['nombre', 'email', 'mensaje'], 'required'],
            [['nombre', 'empresa'], 'string', 'max' => 150],
            [['email'], 'string', 'max' => 150],
            [['email'], 'email', 'message' => 'Escriba un correo válido. Ej.: nombre@empresa.com'],
            [['telefono'], 'string', 'max' => 40],
            [['telefono'], 'match', 'pattern' => '/^[0-9+()\s\-]*$/', 'message' => 'Solo números, espacios, +, ( ) y guiones.'],
            [['mensaje'], 'string', 'min' => 10, 'max' => 3000,
                'tooShort' => 'Cuéntenos un poco más (mínimo 10 caracteres).', 'tooLong' => 'El mensaje es muy largo (máximo 3000 caracteres).'],
            [['sitio'], 'compare', 'compareValue' => '', 'message' => 'No se pudo enviar.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'nombre' => 'Nombre completo',
            'empresa' => 'Empresa',
            'telefono' => 'Teléfono',
            'email' => 'Correo electrónico',
            'mensaje' => 'Su consulta',
        ];
    }
}
