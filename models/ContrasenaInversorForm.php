<?php

namespace app\models;

use yii\base\Model;

/**
 * Cambio de contraseña desde Mi cuenta → Seguridad: pide la actual.
 * La comprobación de la actual la hace PerfilInversorService.
 */
class ContrasenaInversorForm extends Model
{
    public $actual;
    public $nueva;
    public $nueva2;

    public function rules()
    {
        return [
            [['actual', 'nueva', 'nueva2'], 'required'],
            [['actual', 'nueva'], 'string', 'max' => 72],
            [['nueva'], 'match', 'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/',
                'message' => 'Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.'],
            [['nueva'], 'compare', 'compareAttribute' => 'actual', 'operator' => '!==', 'message' => 'La contraseña nueva tiene que ser distinta de la actual.'],
            [['nueva2'], 'compare', 'compareAttribute' => 'nueva', 'message' => 'Las contraseñas no coinciden.'],
        ];
    }

    public function attributeLabels()
    {
        return ['actual' => 'Contraseña actual', 'nueva' => 'Contraseña nueva', 'nueva2' => 'Repita la contraseña nueva'];
    }
}
