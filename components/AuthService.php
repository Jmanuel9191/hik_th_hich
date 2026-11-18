<?php
namespace app\components;
use app\models\LoginForm;
use app\models\Usuarios;;
use Yii;
use app\components\InterfaceCorreos;
class AuthService implements InterfaceAuth
{
    /** Intentos fallidos permitidos antes de bloquear. */
    const MAX_INTENTOS = 4;
    /** Minutos que dura el bloqueo. */
    const MINUTOS_BLOQUEO = 30;

    private $correoService;
    /**
     * @param array $rolesPermitidos Roles que pueden entrar por ESTE login.
     *        Panel admin: Rol::PANEL (por defecto). Web de inversores: [Rol::INVERSOR].
     */
    public function login($model, InterfaceCorreos $correoService, array $rolesPermitidos = Rol::PANEL)
    {
        if (!$model->validate()) {
            return false;
        }

        // Se puede entrar con el usuario o con el email.
        $identificador = trim((string) $model->username);
        $user = Usuarios::find()
            ->where(['or', ['usuario' => $identificador], ['email' => $identificador]])
            ->one();

        // Si no existe o no puede entrar por este login: mismo mensaje (no revela que existe).
        if (!$user || !in_array((int) $user->idrol, $rolesPermitidos, true)) {
            $model->addError('password', 'Usuario o contraseña incorrecta.');
            return false;
        }


        if ($user->estado === '2') {
            $model->addError('password', 'Tu cuenta está inactiva. Contacta con el soporte.');
            return false;
        }


        if (empty($user->estado)) {
            $model->addError('password', 'Tu cuenta ha sido bloqueada. Contacta con el administrador.');
            return false;
        }
        if ($user->bloqueado_hasta !== null) {
            if (strtotime($user->bloqueado_hasta) <= time()) {
                $historial = new \app\models\HistorialUsuarios();
                $historial->idusuario_afectado = $user->idusuario;
                $historial->idusuario_actor = $user->idusuario;
                $historial->accion = 'Desbloqueo automático';
                $historial->descripcion = 'El sistema desbloqueó la cuenta tras finalizar el tiempo de bloqueo.';
                $historial->fecha = date('Y-m-d H:i:s');
                $historial->save(false);

                // Ya pasó el tiempo, limpiar bloqueo
                $user->bloqueado_hasta = null;
                $user->intentos_fallidos = 0;
                $user->save(false);
            } else {
                // Aún está bloqueado
                $minRestante = (int) ceil((strtotime($user->bloqueado_hasta) - time()) / 60);
                $model->addError('password', "Usuario bloqueado por intentos fallidos. Tiempo restante: $minRestante " . ($minRestante === 1 ? 'minuto.' : 'minutos.'));
                return false;
            }
        }

        if (!Yii::$app->security->validatePassword($model->password, $user->contrasena)) {
            $user->intentos_fallidos++;

            if ($user->intentos_fallidos >= self::MAX_INTENTOS) {

                $historial = new \app\models\HistorialUsuarios();
                $historial->idusuario_afectado = $user->idusuario;
                $historial->idusuario_actor = $user->idusuario; // Se usa el mismo usuario ya que fue automático
                $historial->accion = 'Bloqueo automático por intentos fallidos';
                $historial->descripcion = 'El usuario fue bloqueado ' . self::MINUTOS_BLOQUEO . ' min tras ' . self::MAX_INTENTOS . ' intentos fallidos de inicio de sesión. IP: ' . (Yii::$app->request->userIP ?? '-');
                $historial->fecha = date('Y-m-d H:i:s');
                $historial->save(false);


                $user->bloqueado_hasta = date('Y-m-d H:i:s', time() + self::MINUTOS_BLOQUEO * 60);
                $user->save(false);
                // Si el correo de aviso falla, el bloqueo igual queda hecho.
                try {
                    $correoService->enviarCorreoDeBloqueoporintentos($user->idusuario);
                } catch (\Throwable $e) {
                    Yii::error('No se pudo enviar el aviso de bloqueo: ' . $e->getMessage(), __METHOD__);
                }
                $model->addError('password', 'Tu usuario ha sido bloqueado por ' . self::MINUTOS_BLOQUEO . ' minutos por superar los ' . self::MAX_INTENTOS . ' intentos fallidos.');
            } else {
                $intentosRestantes = self::MAX_INTENTOS - $user->intentos_fallidos;
                $user->save(false);
                $model->addError('password', "Usuario o contraseña incorrecta. Te quedan $intentosRestantes intento(s).");
            }

            return false;
        }

        $user->intentos_fallidos = 0;
        $user->bloqueado_hasta = null;
        $user->save(false);


        Yii::$app->user->switchIdentity($user, $model->rememberMe ? 3600 * 24 * 30 : 0);
        //  Redirección obligatoria si requiere cambio de contraseña
        if ($user->requiere_cambio_contrasena == 1) {
            Yii::$app->response->redirect(['admin/cuenta/cambiar-clave-obligatoria'])->send();
            Yii::$app->end();
        }
        return true;

    }


    public function getUser()
    {
        return Yii::$app->user->identity;
    }

    public function logout()
    {

        Yii::$app->user->logout();
    }

    public function getRedirectRoute($user)
    {
        $routes = [
            '1' => ['default/index'],
            '2' => ['default/index'],
            '3' => ['default/index'],
        ];

        // Fallback por defecto si no encuentra un rol
        return $routes[$user->idrol] ?? ['site/index'];
    }

}