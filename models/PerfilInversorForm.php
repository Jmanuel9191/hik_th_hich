<?php

namespace app\models;

use yii\base\Model;

/**
 * Lo que el inversor puede cambiar de su perfil (Mi cuenta → Mis datos).
 * Solo estos campos: nombre, documento, correo, tipo, áreas y preferencias de inversión quedan fijos
 * (los cambia el equipo desde el panel). Los datos de empresa solo valen si invierte como empresa.
 */
class PerfilInversorForm extends Model
{
    const SCENARIO_PERSONA = 'persona';
    const SCENARIO_EMPRESA = 'empresa';

    public $celular;
    public $pais;
    public $ciudad;
    public $razon_social;
    public $nit;
    public $cargo;
    public $sitio_web;

    public function scenarios()
    {
        $persona = ['celular', 'pais', 'ciudad'];
        return [
            self::SCENARIO_PERSONA => $persona,
            self::SCENARIO_EMPRESA => array_merge($persona, ['razon_social', 'nit', 'cargo', 'sitio_web']),
        ];
    }

    public function rules()
    {
        return [
            [['celular', 'pais', 'ciudad', 'razon_social', 'nit', 'cargo', 'sitio_web'], 'trim'],
            [['celular', 'pais', 'ciudad'], 'required'],
            [['razon_social', 'nit', 'cargo'], 'required', 'on' => self::SCENARIO_EMPRESA],
            [['celular'], 'string', 'max' => 30],
            [['celular'], 'match', 'pattern' => '/^\+?[0-9 ()\-]{7,25}$/', 'message' => 'Escríbalo con el código de país. Ej.: +591 70000000'],
            [['pais', 'ciudad'], 'string', 'max' => 50],
            [['razon_social'], 'string', 'max' => 200],
            [['nit'], 'string', 'max' => 40],
            [['nit'], 'match', 'pattern' => '/^[A-Za-z0-9.\-\/ ]+$/', 'message' => 'Solo letras, números, puntos y guiones.'],
            [['cargo'], 'string', 'max' => 100],
            [['sitio_web'], 'default', 'value' => null],
            [['sitio_web'], 'url', 'defaultScheme' => 'https', 'validSchemes' => ['http', 'https']],
            [['sitio_web'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'celular' => 'Celular (WhatsApp)',
            'pais' => 'País',
            'ciudad' => 'Ciudad',
            'razon_social' => 'Razón social',
            'nit' => 'NIT / registro',
            'cargo' => 'Cargo en la empresa',
            'sitio_web' => 'Sitio web',
        ];
    }

    /** Formulario cargado con los datos actuales. */
    public static function desde(Inversor $inv): self
    {
        $f = new self(['scenario' => $inv->tipo === 'empresa' ? self::SCENARIO_EMPRESA : self::SCENARIO_PERSONA]);
        $u = $inv->usuario;
        $f->setAttributes([
            'celular' => $u->movil ?: $u->telefono, 'pais' => $u->pais, 'ciudad' => $u->ciudad,
            'razon_social' => $inv->razon_social, 'nit' => $inv->nit, 'cargo' => $u->cargo, 'sitio_web' => $inv->sitio_web,
        ]);
        return $f;
    }
}
