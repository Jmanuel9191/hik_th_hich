<?php
namespace app\models;

use yii\base\Model;

class CambioPasswordObligatorioForm extends Model
{
    public $nueva_contrasena;
    public $repetir_contrasena;

    public function rules()
    {
        return [
            [['nueva_contrasena', 'repetir_contrasena'], 'required'],
            ['repetir_contrasena', 'compare', 'compareAttribute' => 'nueva_contrasena', 'message' => 'Las contraseñas no coinciden.'],
            ['nueva_contrasena', 'string', 'min' => 8],
            ['nueva_contrasena', 'match', 'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])/', 'message' => 'Debe incluir una mayúscula, una minúscula, un número y un símbolo.'],
        ];
    }
}
