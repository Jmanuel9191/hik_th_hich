<?php

namespace app\models;

use yii\base\Model;

/**
 * Formulario "Quiero invertir" de la página del proyecto. El monto no es obligatorio: puede elegir "A conversar".
 */
class QuieroInvertirForm extends Model
{
    const MONTO_MAXIMO = 999999999999;

    public $tipo_monto = 'monto';   // monto | conversar
    public $monto;
    public $participacion;
    public $plazo;
    public $contacto_medio = 'whatsapp';
    public $contacto_horario;
    public $comentario;
    public $acepto;

    public function rules()
    {
        return [
            [['monto', 'contacto_horario', 'comentario'], 'trim'],
            [['tipo_monto', 'contacto_medio'], 'required'],
            [['tipo_monto'], 'in', 'range' => ['monto', 'conversar']],
            [['monto'], 'required', 'when' => fn($m) => $m->tipo_monto === 'monto',
                'whenClient' => "function () { return $('[name=\"QuieroInvertirForm[tipo_monto]\"]:checked').val() === 'monto'; }",
                'message' => 'Escriba un monto estimado o elija «A conversar».'],
            [['monto'], 'number', 'min' => 1, 'max' => self::MONTO_MAXIMO, 'message' => 'Escriba solo el número, sin puntos de miles. Ej.: 50000'],
            [['participacion'], 'in', 'range' => array_keys(Inversor::PARTICIPACIONES), 'message' => 'Elija una opción de la lista.'],
            [['plazo'], 'in', 'range' => array_keys(Inversor::PLAZOS), 'message' => 'Elija una opción de la lista.'],
            [['contacto_medio'], 'in', 'range' => array_keys(InteresInversion::MEDIOS), 'message' => 'Elija cómo prefiere que lo contactemos.'],
            [['contacto_horario'], 'string', 'max' => 100],
            [['comentario'], 'string', 'max' => 1000],
            [['acepto'], 'required', 'requiredValue' => '1', 'message' => 'Marque la casilla para enviar su interés.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'tipo_monto' => '¿Cuánto piensa invertir?',
            'monto' => 'Monto estimado',
            'participacion' => 'Tipo de participación',
            'plazo' => 'Plazo',
            'contacto_medio' => '¿Cómo prefiere que lo contactemos?',
            'contacto_horario' => 'Horario preferido (opcional)',
            'comentario' => 'Comentario (opcional)',
            'acepto' => 'Entiendo que esto es una manifestación de interés y no un compromiso de inversión.',
        ];
    }

    /** Vacío con las preferencias de su perfil, o con lo que ya envió (para actualizarlo). */
    public static function para(Inversor $inv, ?InteresInversion $previo): self
    {
        $f = new self();
        if ($previo !== null && $previo->activo()) {
            $f->setAttributes([
                'tipo_monto' => $previo->monto === null ? 'conversar' : 'monto',
                'monto' => $previo->monto === null ? null : (string) (0 + $previo->monto),
                'participacion' => $previo->participacion, 'plazo' => $previo->plazo, 'contacto_medio' => $previo->contacto_medio,
                'contacto_horario' => $previo->contacto_horario, 'comentario' => $previo->comentario,
            ], false);
            return $f;
        }
        $f->participacion = $inv->participacion_preferida ?: null;
        $f->plazo = $inv->plazo_preferido ?: null;
        return $f;
    }
}
