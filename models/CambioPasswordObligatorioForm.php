<?php
namespace app\models;

use yii\base\Model;

class CambioPasswordObligatorioForm extends Model
{
    public $nueva_contrasena;
    public $confirmar_contrasena;

    public function rules()
    {
        return [
            [['nueva_contrasena', 'confirmar_contrasena'], 'required'],
            ['nueva_contrasena', 'string', 'min' => 8],
            ['nueva_contrasena', 'match', 'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                'message' => 'Debe contener al menos una mayúscula, una minúscula, un número y un símbolo.'],
            ['confirmar_contrasena', 'compare', 'compareAttribute' => 'nueva_contrasena', 'message' => 'Las contraseñas no coinciden.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'nueva_contrasena' => 'Nueva contraseña',
            'confirmar_contrasena' => 'Confirmar contraseña',
        ];
    }
}
