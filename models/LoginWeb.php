<?php

namespace app\models;

use app\components\InterfaceNoti;
use Yii;
use yii\base\Model;
use app\components\InterfaceCorreos;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginWeb extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    /*public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Usuario o contraseña incorrecta.');
                Yii::$app->session->remove('user');
            }
        }
    }*/


    public function validatePassword($attribute, $params)
    {/*
        if (!$this->hasErrors()) {
            $user = Usuarios::findOne(['usuario' => $this->username]);

            if (!$user) {
                $this->addError($attribute, 'Usuario o contraseña incorrecta.');
                return;
            }

            // Verificar si el usuario está bloqueado
            if ($user->bloqueado_hasta !== null && strtotime($user->bloqueado_hasta) > time()) {
                $minRestante = ceil((strtotime($user->bloqueado_hasta) - time()) / 60);
                $this->addError($attribute, "Cuenta bloqueada por múltiples intentos fallidos. Tiempo restante: $minRestante minutos.");
                return;
            }

            // Verificar contraseña incorrecta
            if (!Yii::$app->security->validatePassword($this->password, $user->contrasena)) {
                $user->intentos_fallidos += 1;

                if ($user->intentos_fallidos >= 4) {
                    $user->bloqueado_hasta = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                    $user->save(false);

                    // Enviar correo
                    Yii::$app->correoService->enviarCorreoDeBloqueoporintentos($user->idusuario);

                    $this->addError($attribute, 'Tu cuenta ha sido bloqueada por múltiples intentos fallidos. Tiempo restante: 30 minutos.');
                    return;
                }

                // 👉 AQUÍ VA EL MENSAJE DE INTENTOS RESTANTES
                $intentosRestantes = 4 - $user->intentos_fallidos;
                $user->save(false);
                $this->addError($attribute, "Usuario o contraseña incorrecta. Te quedan $intentosRestantes intento(s).");
                return;
            }

            // Si la contraseña es correcta, reiniciar intentos
            $user->intentos_fallidos = 0;
            $user->bloqueado_hasta = null;
            $user->save(false);
            Yii::$app->session->set('user', $user);
        }*/
    }



    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $usuario = $this->getUser();

            // Establecer zona horaria si el usuario tiene una
            if (!empty($usuario->zona_horaria)) {
                date_default_timezone_set($usuario->zona_horaria);
            }

            return Yii::$app->user->login($usuario, $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }


    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = Usuarios::findOne(['usuario' => $this->username, 'estado' => 1]);
        }

        return $this->_user;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Usuario',
            'password' => 'Contraseña',
        ];
    }
}
